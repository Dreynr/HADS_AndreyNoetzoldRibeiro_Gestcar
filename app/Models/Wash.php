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
