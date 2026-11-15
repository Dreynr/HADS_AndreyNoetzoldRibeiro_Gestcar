<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Produto do estoque (B-12 / RF07 / UC07).
 *
 * O saldo nao e alterado na mao em lugar nenhum: ele so muda por uma
 * movimentacao, pelo `registerMovement()`. Isso garante que o numero sempre
 * tem historico que o explique.
 *
 * A baixa automatica por servico realizado esta fora do escopo: o estoque e
 * de controle manual.
 */
class Product extends Model
{
    use HasFactory;

    public const TYPE_IN = 'entrada';
    public const TYPE_OUT = 'saida';

    protected $fillable = [
        'name',
        'unit',
        'quantity',
        'min_quantity',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'min_quantity' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Produtos que precisam de reposicao (UC07-HU04).
     *
     * `whereColumn` compara duas colunas da mesma linha — e o jeito de
     * perguntar "quantidade menor ou igual ao minimo" sem trazer tudo para o
     * PHP.
     */
    public function scopeBelowMinimum(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '<=', 'min_quantity');
    }

    public function isBelowMinimum(): bool
    {
        return $this->quantity <= $this->min_quantity;
    }

    /**
     * Registra uma entrada ou saida e atualiza o saldo (UC07-HU03).
     *
     * Tudo dentro de uma transacao: ou a movimentacao e gravada e o saldo
     * muda junto, ou nada acontece. Sem isso, uma falha no meio deixaria o
     * saldo discordando do historico.
     *
     * O `lockForUpdate()` segura a linha do produto ate o fim da transacao,
     * para duas saidas ao mesmo tempo nao lerem o mesmo saldo e derrubarem o
     * estoque para negativo.
     *
     * @throws RuntimeException quando o produto esta inativo ou a saida e
     *                          maior que o saldo.
     */
    public function registerMovement(string $type, float $quantity, ?string $notes = null): StockMovement
    {
        return DB::transaction(function () use ($type, $quantity, $notes) {
            $atual = static::query()->lockForUpdate()->find($this->id);

            // Produto inativo saiu de uso: aceitar entrada ou saida nele
            // faria o saldo de algo que a estetica nao usa mais continuar
            // mudando, e esse saldo nao aparece no alerta de reposicao.
            if (!$atual->active) {
                throw new RuntimeException(
                    "O produto {$atual->name} esta inativo e nao aceita movimentacao. Ative o produto antes."
                );
            }

            $novoSaldo = $type === self::TYPE_OUT
                ? (float) $atual->quantity - $quantity
                : (float) $atual->quantity + $quantity;

            // Regra do UC07-HU03: saida nao pode deixar o saldo negativo.
            if ($novoSaldo < 0) {
                throw new RuntimeException(
                    "Saida de {$quantity} maior que o saldo atual de {$atual->quantity} {$atual->unit}."
                );
            }

            $movimento = $this->movements()->create([
                'type' => $type,
                'quantity' => $quantity,
                'notes' => $notes,
            ]);

            $atual->update(['quantity' => $novoSaldo]);
            $this->setAttribute('quantity', $novoSaldo);

            return $movimento;
        });
    }
}
