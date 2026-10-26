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

    private function book(Service $service, string $period = 'noite')
    {
        return $this->actingAs($this->client)->post(route('appointments.store'), [
            'vehicle_id' => $this->vehicle->id,
            'service_id' => $service->id,
            'date' => $this->weekday,
            'period' => $period,
        ]);
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
}
