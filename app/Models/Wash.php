<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Lavagem: o atendimento realizado (D-14). E dela que sai o fechamento
 * mensal, e nao dos agendamentos, porque agendamento cancelado nao fatura
 * e lavagem avulsa fatura sem nunca ter sido agendada.
 */
class Wash extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'user_id',
        'vehicle_id',
        'service_id',
        'service_name',
        'amount',
        'notes',
        'performed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'performed_at' => 'datetime',
        ];
    }

    /**
     * Grava a copia do nome do servico junto com o valor (D-17), para o
     * relatorio de um mes fechado nao mudar se o servico for renomeado.
     * Usado por quem cria a lavagem, em vez de preencher na mao.
     */
    public static function fromService(Service $service, array $attributes): static
    {
        return new static(array_merge([
            'service_id' => $service->id,
            'service_name' => $service->name,
            'amount' => $service->price,
        ], $attributes));
    }

    /**
     * Intervalo do fechamento mensal (B-10). `whereBetween` com o dia 1 as
     * 00:00 e o ultimo dia as 23:59:59 aproveita o indice de performed_at.
     */
    public function scopeInMonth(Builder $query, int $year, int $month): Builder
    {
        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();

        return $query->whereBetween('performed_at', [$start, $start->copy()->endOfMonth()]);
    }

    /**
     * Fechamento mensal (B-10 / RF08 / UC08-HU01).
     *
     * Soma o que foi cobrado nas lavagens do mes, e nao o preco atual dos
     * servicos: o valor fica gravado no momento do atendimento (D-13), entao
     * um mes ja fechado nunca muda por causa de um reajuste.
     *
     * O agrupamento usa `service_name`, a copia do nome guardada na lavagem
     * (D-17), pelo mesmo motivo: renomear um servico nao pode reescrever o
     * historico.
     *
     * @return array{total: float, quantidade: int, ticket: float, por_servico: \Illuminate\Support\Collection}
     */
    public static function monthlyClosing(int $year, int $month): array
    {
        $base = static::query()->inMonth($year, $month);

        $quantidade = (clone $base)->count();
        $total = (float) (clone $base)->sum('amount');

        $porServico = (clone $base)
            ->selectRaw('service_name, COUNT(*) as quantidade, SUM(amount) as total')
            ->groupBy('service_name')
            ->orderByDesc('total')
            ->get();

        return [
            'total' => $total,
            'quantidade' => $quantidade,
            // Ticket medio e faturamento dividido por numero de lavagens.
            // Sem lavagem nenhuma seria divisao por zero, entao devolve 0.
            'ticket' => $quantidade > 0 ? $total / $quantidade : 0.0,
            'por_servico' => $porServico,
        ];
    }

    /**
     * Os ultimos meses lado a lado, do mais antigo para o mais novo
     * (B-10 / UC08-HU02).
     *
     * Um mes por consulta, de proposito: agrupar por `YEAR()`/`MONTH()` numa
     * consulta so dependeria de funcoes de data do MySQL, e os testes rodam
     * em SQLite. Sao poucas consultas e o indice de `performed_at` atende.
     */
    public static function lastMonths(int $months, int $year, int $month): array
    {
        $referencia = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $meses = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $mes = $referencia->copy()->subMonths($i);
            $base = static::query()->inMonth($mes->year, $mes->month);

            $meses[] = [
                'ano' => $mes->year,
                'mes' => $mes->month,
                'rotulo' => sprintf('%02d/%d', $mes->month, $mes->year),
                'total' => (float) (clone $base)->sum('amount'),
                'quantidade' => (clone $base)->count(),
            ];
        }

        return $meses;
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
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
}
