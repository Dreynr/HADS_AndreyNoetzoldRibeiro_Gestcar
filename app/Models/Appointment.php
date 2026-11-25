<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    const ALLOWED_TRANSITIONS = [
        'pendente'   => ['confirmado', 'cancelado'],
        'confirmado' => ['concluido', 'cancelado'],
        'concluido'  => [],
        'cancelado'  => [],
    ];

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::ALLOWED_TRANSITIONS[$this->status] ?? []);
    }

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'service_id',
        'scheduled_at',
        'period',
        'status',
        'notes',
        'admin_notes',
        'reminder_1day_sent',
        'reminder_1hour_sent',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Um agendamento concluido vira uma lavagem (D-14). `hasOne` porque o
     * atendimento acontece uma vez; lavagem avulsa nao tem agendamento.
     */
    public function wash(): HasOne
    {
        return $this->hasOne(Wash::class);
    }

    public function extras(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'appointment_extras')
            ->withPivot('price')
            ->withTimestamps();
    }

    /**
     * Registra o atendimento realizado a partir deste agendamento (D-14).
     * O valor vem da tela, porque o admin pode dar desconto; o nome do
     * servico e copiado pelo `fromService`, para o relatorio de um mes
     * fechado nao mudar se o servico for renomeado depois (D-13, D-17).
     */
    public function registerWash(array $data): Wash
    {
        $wash = Wash::fromService($this->service, [
            'appointment_id' => $this->id,
            'user_id' => $this->user_id,
            'vehicle_id' => $this->vehicle_id,
            'amount' => $data['amount'],
            'notes' => $data['notes'] ?? null,
            'performed_at' => $data['performed_at'],
        ]);

        $wash->save();

        return $wash;
    }

    /**
     * O dia do atendimento ainda nao chegou.
     *
     * Concluir antes da data e permitido — as vezes o atendimento acontece
     * adiantado —, mas a tela pede confirmacao, porque concluido e final.
     */
    public function dateHasNotArrived(): bool
    {
        return $this->scheduled_at->startOfDay()->isFuture();
    }

    /**
     * O cliente so cancela agendamento que ainda vai acontecer. Agendamento
     * de uma data que ja passou nao se cancela: ou foi atendido, e o admin
     * conclui, ou o cliente faltou, e o admin cancela.
     */
    public function canBeCancelledByClient(): bool
    {
        return in_array($this->status, ['pendente', 'confirmado'])
            && !$this->scheduled_at->startOfDay()->isPast();
    }

    public function totalPrice(): float
    {
        $base = $this->service ? (float) $this->service->price : 0;
        $extrasTotal = $this->extras->sum('pivot.price');

        return $base + $extrasTotal;
    }

    /**
     * A regra do periodo: basta um servico exigir o periodo todo para o
     * periodo inteiro ficar reservado. Vale para o servico principal e
     * para os extras, porque os dois ocupam a estetica da mesma forma.
     */
    public static function anyBlocksPeriod(iterable $services): bool
    {
        foreach ($services as $service) {
            if ($service && $service->blocks_period) {
                return true;
            }
        }

        return false;
    }

    /**
     * A mesma regra aplicada a um agendamento ja gravado.
     * `concat` devolve uma colecao nova, sem alterar a relacao carregada.
     */
    public function blocksPeriod(): bool
    {
        return static::anyBlocksPeriod($this->extras->concat([$this->service]));
    }

    public static function periodLabel(string $period): string
    {
        return match ($period) {
            'manha' => 'Manha (08:00 - 12:00)',
            'tarde' => 'Tarde (13:00 - 17:00)',
            'noite' => 'Noite (17:00 - 22:00)',
            default => $period,
        };
    }

    /**
     * O nome curto do periodo, sem a faixa de horas.
     */
    public static function periodName(string $period): string
    {
        return match ($period) {
            'manha' => 'Manha',
            'tarde' => 'Tarde',
            'noite' => 'Noite',
            default => $period,
        };
    }

    /**
     * Como o agendamento aparece nas telas e nas mensagens (D-22).
     *
     * O horario escolhido vem primeiro, porque e o que o cliente precisa
     * saber. O periodo vem entre parenteses, porque e ele que organiza a
     * agenda e define a capacidade.
     *
     * Existe num lugar so para as telas nunca discordarem entre si: antes
     * desta correcao, o sistema gravava 19:30 e todas as telas mostravam
     * "Noite (17:00 - 22:00)".
     */
    public function scheduleLabel(): string
    {
        return $this->scheduled_at->format('H:i') . ' (' . static::periodName($this->period) . ')';
    }

    /**
     * Os dois horarios de cada periodo (D-22).
     *
     * O periodo continua organizando o dia — ele e que tem capacidade e e
     * ele que um servico completo reserva inteiro. O horario e a preferencia
     * do cliente dentro do periodo, e e dele que os lembretes partem.
     *
     * Cada periodo e dividido ao meio: a noite vai das 17h as 22h, entao o
     * segundo horario comeca as 19:30.
     *
     * @return array<int, string>
     */
    public static function periodSlots(string $period): array
    {
        return match ($period) {
            'manha' => ['08:00', '10:00'],
            'tarde' => ['13:00', '15:00'],
            'noite' => ['17:00', '19:30'],
            default => ['17:00'],
        };
    }

    /**
     * Sem escolha do cliente, vale o primeiro horario do periodo.
     */
    public static function defaultSlot(string $period): string
    {
        return static::periodSlots($period)[0];
    }

    public static function isValidSlot(string $period, string $slot): bool
    {
        return in_array($slot, static::periodSlots($period), true);
    }

    public static function periodStart(string $period): int
    {
        return match ($period) {
            'manha' => 8,
            'tarde' => 13,
            'noite' => 17,
            default => 17,
        };
    }

    public static function periodAvailability(string $date, string $period): array
    {
        $appointments = static::whereDate('scheduled_at', $date)
            ->where('period', $period)
            ->whereIn('status', ['pendente', 'confirmado'])
            ->with(['service', 'extras'])
            ->get();

        $hasBlocker = $appointments->contains(fn ($a) => $a->blocksPeriod());

        if ($hasBlocker) {
            return ['available' => false, 'reason' => 'Periodo ocupado (servico completo)'];
        }

        $maxSimple = (int) Setting::getValue('max_simple_per_period', '2');

        if ($appointments->count() >= $maxSimple) {
            return ['available' => false, 'reason' => 'Periodo lotado'];
        }

        return [
            'available' => true,
            'remaining' => $maxSimple - $appointments->count(),
        ];
    }

    public static function canBook(string $date, string $period, bool $blocksAllPeriod): array
    {
        $appointments = static::whereDate('scheduled_at', $date)
            ->where('period', $period)
            ->whereIn('status', ['pendente', 'confirmado'])
            ->with(['service', 'extras'])
            ->get();

        if ($appointments->contains(fn ($a) => $a->blocksPeriod())) {
            return ['ok' => false, 'error' => 'Este periodo ja esta reservado para um servico completo.'];
        }

        if ($blocksAllPeriod && $appointments->count() > 0) {
            return ['ok' => false, 'error' => 'Este servico precisa do periodo inteiro, mas ja existem agendamentos.'];
        }

        $maxSimple = (int) Setting::getValue('max_simple_per_period', '2');
        if (!$blocksAllPeriod && $appointments->count() >= $maxSimple) {
            return ['ok' => false, 'error' => 'Este periodo ja atingiu o limite de agendamentos.'];
        }

        return ['ok' => true];
    }

    public static function availablePeriodsForDate(string $date): array
    {
        $carbon = Carbon::parse($date);
        $isWeekend = $carbon->isWeekend();

        $periods = [];

        if ($isWeekend) {
            $periods[] = 'manha';
            $periods[] = 'tarde';
        }

        $periods[] = 'noite';

        $result = [];
        foreach ($periods as $p) {
            $avail = static::periodAvailability($date, $p);
            $result[$p] = [
                'label' => static::periodLabel($p),
                'available' => $avail['available'],
                'remaining' => $avail['remaining'] ?? 0,
                'reason' => $avail['reason'] ?? null,
            ];
        }

        return $result;
    }
}
