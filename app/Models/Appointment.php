<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Appointment extends Model
{
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

    public function extras(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'appointment_extras')
            ->withPivot('price')
            ->withTimestamps();
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
