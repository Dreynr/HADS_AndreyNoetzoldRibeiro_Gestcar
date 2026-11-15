<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entrada ou saida de um produto (B-12 / RF07 / UC07-HU03).
 *
 * A movimentacao e o historico do saldo: ela nunca e editada nem apagada,
 * porque mudar o passado faria o saldo deixar de ser explicavel. Erro de
 * lancamento se corrige com a movimentacao contraria.
 */
class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isIn(): bool
    {
        return $this->type === Product::TYPE_IN;
    }

    public function label(): string
    {
        return $this->isIn() ? 'Entrada' : 'Saida';
    }
}
