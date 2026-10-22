<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
}
