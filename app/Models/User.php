<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        'privacy_accepted_at',
        'whatsapp_opt_in',
        'whatsapp_opt_in_at',
    ];

    protected function phone(): Attribute
    {
        return Attribute::make(
            set: function (?string $value) {
                if ($value === null || trim($value) === '') {
                    return null;
                }

                $digits = preg_replace('/\D/', '', $value);

                if (!str_starts_with($digits, '55')) {
                    $digits = '55' . $digits;
                }

                return $digits;
            },
        );
    }

    /**
     * O banco guarda o telefone no formato que a Z-API exige (55 + DDD +
     * numero, so digitos). Para a tela, devolve no formato que o brasileiro
     * le e que a validacao aceita de volta: (54) 99999-1111.
     *
     * E o caminho inverso do mutator acima: um grava, o outro exibe.
     */
    public function formattedPhone(): ?string
    {
        if (!$this->phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->phone);

        // Tira o codigo do pais, que nao se escreve no dia a dia.
        if (strlen($digits) > 11 && str_starts_with($digits, '55')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 11) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7));
        }

        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 4), substr($digits, 6));
        }

        // Formato inesperado: mostra como esta, em vez de esconder o dado.
        return $this->phone;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCliente(): bool
    {
        return $this->role === 'cliente';
    }

    /**
     * Quem pediu a exclusao da conta sai das listagens e do CRM, mas o
     * registro continua existindo para os agendamentos nao perderem o dono.
     */
    public function scopeNotAnonymized($query)
    {
        return $query->whereNull('anonymized_at');
    }

    public function isAnonymized(): bool
    {
        return $this->anonymized_at !== null;
    }

    /**
     * LGPD (D-07): a conta nao e apagada, e anonimizada. Os dados pessoais
     * sao substituidos e os agendamentos ficam, ligados a um registro anonimo,
     * para o faturamento de meses passados nao mudar sozinho.
     *
     * Tudo numa transacao: ou todas as tabelas mudam, ou nenhuma muda.
     */
    public function anonymize(): void
    {
        DB::transaction(function () {
            $originalEmail = $this->email;

            // A placa identifica o dono; modelo e cor ficam para o historico.
            $this->vehicles()->update(['plate' => 'REMOVIDA']);

            $this->clientNotes()->delete();
            $this->followUps()->delete();

            DB::table('password_reset_codes')->where('email', $originalEmail)->delete();

            // forceFill ignora o $fillable: aqui a origem dos dados e o
            // proprio sistema, nao um formulario.
            $this->forceFill([
                'name' => 'Cliente removido',
                // .invalid e um dominio reservado que nunca existe de verdade,
                // e o id mantem o e-mail unico. O e-mail original fica livre
                // para um novo cadastro.
                'email' => 'removido-' . $this->id . '@gestcar.invalid',
                'phone' => null,
                'birthday' => null,
                'preferred_contact' => null,
                'preferences' => null,
                // O cast 'hashed' transforma isso num hash. Ninguem conhece
                // a senha, entao ninguem entra mais nessa conta.
                'password' => Str::random(40),
                'remember_token' => null,
                'active' => false,
                'whatsapp_opt_in' => false,
                'anonymized_at' => now(),
            ])->save();
        });
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
            'privacy_accepted_at' => 'datetime',
            'whatsapp_opt_in' => 'boolean',
            'whatsapp_opt_in_at' => 'datetime',
            'anonymized_at' => 'datetime',
        ];
    }
}
