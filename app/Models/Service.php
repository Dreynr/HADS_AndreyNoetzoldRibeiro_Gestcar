<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'price',
        'duration_minutes',
        'blocks_period',
        'active',
    ];

    public function scopePrincipal($query)
    {
        return $query->where('type', 'principal');
    }

    public function scopeExtra($query)
    {
        return $query->where('type', 'extra');
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'blocks_period' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Agendamentos em que este servico foi escolhido como EXTRA.
     *
     * E a segunda forma de um servico ser usado: como extra ele nao aparece
     * em `appointments.service_id`, e sim na tabela `appointment_extras`.
     * Sem esta relacao o sistema achava que o servico nunca tinha sido
     * usado, tentava exclui-lo e esbarrava na chave estrangeira.
     */
    public function appointmentsAsExtra(): BelongsToMany
    {
        return $this->belongsToMany(Appointment::class, 'appointment_extras')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function washes(): HasMany
    {
        return $this->hasMany(Wash::class);
    }
}
