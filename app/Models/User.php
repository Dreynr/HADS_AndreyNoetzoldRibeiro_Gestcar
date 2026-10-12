<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'active',
        'birthday',
        'preferred_contact',
        'preferences',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCliente(): bool
    {
        return $this->role === 'cliente';
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function clientNotes(): HasMany
    {
        return $this->hasMany(ClientNote::class, 'client_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'client_id');
    }

    public function totalSpent(): float
    {
        return $this->appointments()
            ->where('status', 'concluido')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->sum('services.price');
    }

    public function completedAppointmentsCount(): int
    {
        return $this->appointments()->where('status', 'concluido')->count();
    }

    public function lastAppointmentDate(): ?\Carbon\Carbon
    {
        return $this->appointments()
            ->whereIn('status', ['concluido', 'confirmado', 'pendente'])
            ->latest('scheduled_at')
            ->value('scheduled_at');
    }

    public function daysSinceLastVisit(): ?int
    {
        $last = $this->lastAppointmentDate();
        return $last ? (int) $last->diffInDays(now()) : null;
    }

    public function segment(): string
    {
        $completed = $this->completedAppointmentsCount();
        $days = $this->daysSinceLastVisit();

        if ($days !== null && $days > 90) return 'inativo';
        if ($completed >= 10) return 'vip';
        if ($completed >= 3) return 'recorrente';
        return 'novo';
    }

    public static function segmentLabel(string $segment): string
    {
        return match ($segment) {
            'vip' => 'VIP',
            'recorrente' => 'Recorrente',
            'inativo' => 'Inativo',
            default => 'Novo',
        };
    }

    public static function segmentColor(string $segment): string
    {
        return match ($segment) {
            'vip' => 'bg-purple-100 text-purple-800',
            'recorrente' => 'bg-blue-100 text-blue-800',
            'inativo' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthday' => 'date',
        ];
    }
}
