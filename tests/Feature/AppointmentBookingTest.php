<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * B-07 itens 1 a 5 — as regras de periodo e capacidade do agendamento,
 * que vivem em App\Models\Appointment.
 */
class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private Vehicle $vehicle;
    private Service $simple;
    private Service $full;
    private string $weekday;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        Setting::setValue('max_simple_per_period', '2');

        $this->client = User::factory()->create(['role' => 'cliente']);
        $this->vehicle = Vehicle::factory()->create(['user_id' => $this->client->id]);

        $this->simple = Service::factory()->create(['name' => 'Lavagem Simples']);
        $this->full = Service::factory()->blocksPeriod()->create(['name' => 'Lavagem Completa']);

        // Segunda-feira futura: em dia de semana so existe o periodo da noite.
        $this->weekday = now()->addWeek()->next('Monday')->format('Y-m-d');
    }

    private function book(Service $service, string $period = 'noite', ?string $slot = null)
    {
        $dados = [
            'vehicle_id' => $this->vehicle->id,
            'service_id' => $service->id,
            'date' => $this->weekday,
            'period' => $period,
        ];

        if ($slot !== null) {
            $dados['slot'] = $slot;
        }

        return $this->actingAs($this->client)->post(route('appointments.store'), $dados);
    }

    private function existingAppointment(Service $service, string $status = 'pendente'): Appointment
    {
        return Appointment::factory()
            ->on($this->weekday, 'noite')
            ->status($status)
            ->create(['service_id' => $service->id]);
    }

    public function test_em_dia_de_semana_so_a_noite_e_aceita(): void
    {
        $this->book($this->simple, 'manha')->assertSessionHasErrors('period');
        $this->book($this->simple, 'tarde')->assertSessionHasErrors('period');

        $this->assertSame(0, Appointment::count());

        $this->book($this->simple, 'noite')->assertSessionHasNoErrors();

        $this->assertSame(1, Appointment::count());
    }

    public function test_o_terceiro_agendamento_simples_no_mesmo_periodo_e_recusado(): void
    {
        $this->existingAppointment($this->simple);
        $this->existingAppointment($this->simple);

        $this->book($this->simple)->assertSessionHasErrors('period');

        $this->assertSame(2, Appointment::count());
    }

    public function test_servico_completo_bloqueia_os_demais_do_periodo(): void
    {
        $this->existingAppointment($this->full);

        $this->book($this->simple)->assertSessionHasErrors('period');

        $this->assertSame(1, Appointment::count());
    }

    public function test_servico_completo_e_recusado_se_o_periodo_ja_tem_agendamento(): void
    {
        $this->existingAppointment($this->simple);

        $this->book($this->full)->assertSessionHasErrors('period');

        $this->assertSame(1, Appointment::count());
    }

    /**
     * Agendamento de data que ja passou nao se cancela: ou foi atendido, e o
     * admin conclui, ou houve falta, e o admin cancela. O botao some da tela
     * do cliente, e a rota recusa mesmo se for chamada direto.
     */
    public function test_cliente_nao_cancela_agendamento_de_data_passada(): void
    {
        $passado = Appointment::factory()->create([
            'user_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'service_id' => $this->simple->id,
            'scheduled_at' => now()->subWeek()->setTime(17, 0),
            'status' => 'confirmado',
        ]);

        $this->actingAs($this->client)
            ->patch(route('appointments.cancel', $passado))
            ->assertSessionHas('error');

        $this->assertSame('confirmado', $passado->fresh()->status);

        $this->actingAs($this->client)
            ->get(route('appointments.index'))
            ->assertOk()
            ->assertDontSee('Cancelar Agendamento');
    }

    public function test_cliente_cancela_agendamento_futuro(): void
    {
        $futuro = Appointment::factory()->create([
            'user_id' => $this->client->id,
            'vehicle_id' => $this->vehicle->id,
            'service_id' => $this->simple->id,
            'scheduled_at' => now()->addWeek()->setTime(17, 0),
            'status' => 'confirmado',
        ]);

        $this->actingAs($this->client)
            ->get(route('appointments.index'))
            ->assertSee('Cancelar Agendamento');

        $this->actingAs($this->client)
            ->patch(route('appointments.cancel', $futuro))
            ->assertSessionHas('success');

        $this->assertSame('cancelado', $futuro->fresh()->status);
    }

    public function test_cancelar_libera_a_vaga(): void
    {
        $this->existingAppointment($this->simple);
        $cancelavel = $this->existingAppointment($this->simple);

        // Periodo cheio: 2 de 2.
        $this->book($this->simple)->assertSessionHasErrors('period');

        $cancelavel->update(['status' => 'cancelado']);

        $this->book($this->simple)->assertSessionHasNoErrors();

        $this->assertSame(2, Appointment::whereIn('status', ['pendente', 'confirmado'])->count());
    }

    /**
     * D-22 — o periodo ganhou dois horarios. Sem escolha do cliente, vale o
     * primeiro: a noite comeca as 17:00.
     */
    public function test_sem_escolha_o_agendamento_fica_no_primeiro_horario(): void
    {
        $this->book($this->simple)->assertSessionHasNoErrors();

        $this->assertSame('17:00', Appointment::first()->scheduled_at->format('H:i'));
    }

    public function test_cliente_pode_escolher_o_segundo_horario_do_periodo(): void
    {
        $this->book($this->simple, 'noite', '19:30')->assertSessionHasNoErrors();

        $this->assertSame('19:30', Appointment::first()->scheduled_at->format('H:i'));
    }

    /**
     * Horario que nao pertence ao periodo escolhido e recusado, em vez de
     * corrigido em silencio: o cliente nao pode achar que marcou um horario
     * e o sistema ter marcado outro.
     */
    public function test_horario_de_outro_periodo_e_recusado(): void
    {
        $this->book($this->simple, 'noite', '08:00')->assertSessionHasErrors('slot');

        $this->assertSame(0, Appointment::count());
    }

    /**
     * O horario e preferencia dentro do periodo: a capacidade continua sendo
     * do periodo inteiro, entao dois clientes podem escolher as 17:00.
     */
    public function test_o_horario_nao_muda_a_capacidade_do_periodo(): void
    {
        $this->book($this->simple, 'noite', '17:00')->assertSessionHasNoErrors();
        $this->book($this->simple, 'noite', '17:00')->assertSessionHasNoErrors();

        $this->book($this->simple, 'noite', '19:30')->assertSessionHasErrors('period');

        $this->assertSame(2, Appointment::count());
    }

    /**
     * O horario escolhido tem que APARECER. O dado estava certo no banco e
     * nas mensagens, mas todas as telas mostravam so a faixa do periodo
     * ("Noite (17:00 - 22:00)"), entao quem marcava 19:30 nao tinha como
     * conferir. Agora todas usam o Appointment::scheduleLabel().
     */
    public function test_as_telas_mostram_o_horario_escolhido(): void
    {
        $this->book($this->simple, 'noite', '19:30')->assertSessionHasNoErrors();

        $this->actingAs($this->client)
            ->get(route('appointments.index'))
            ->assertOk()
            ->assertSee('19:30');

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.appointments.index', ['date' => $this->weekday]))
            ->assertOk()
            ->assertSee('19:30');
    }
}
