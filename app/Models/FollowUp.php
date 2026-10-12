<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUp extends Model
{
    protected $fillable = [
        'client_id',
        'type',
        'title',
        'description',
        'due_date',
        'completed',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function isPending(): bool
    {
        return !$this->completed;
    }

    public function isDueToday(): bool
    {
        return !$this->completed && $this->due_date->isToday();
    }

    public function isOverdue(): bool
    {
        return !$this->completed && $this->due_date->isBefore(today());
    }
}
