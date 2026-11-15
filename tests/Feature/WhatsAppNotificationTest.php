<?php

namespace Tests\Feature;

use App\Contracts\WhatsAppSender;
use App\Models\Appointment;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\AppointmentNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

/**
 * B-08 / RF10 / UC10-HU02/HU03/HU04 — o aviso sai pelo WhatsApp para quem
 * autorizou, o e-mail nunca depende do WhatsApp (RNF10, testes S16 e N8) e
 * cada tentativa fica registrada em notification_logs.
 */
class WhatsAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Um WhatsAppSender de mentira, que so anota o que receberia. E a prova
     * de que vale a pena ter o provedor atras de uma interface (D-05): no
     * teste, a Z-API simplesmente nao existe.
     */
    private function fakeSender(): object
    {
        return new class implements WhatsAppSender
        {
            public array $sent = [];

            public function send(string $phone, string $message): void
            {
                $this->sent[] = ['phone' => $phone, 'message' => $message];
            }
        };
    }

    private function brokenSender(): object
    {
        return new class implements WhatsAppSender
        {
            public function send(string $phone, string $message): void
            {
                throw new RuntimeException('Z-API respondeu 500: fora do ar');
            }
        };
    }

    private function client(bool $optIn, ?string $phone = '5554999991111'): User
    {
        return User::factory()->create([
            'role' => 'cliente',
            'phone' => $phone,
            'whatsapp_opt_in' => $optIn,
            'whatsapp_opt_in_at' => $optIn ? now() : null,
        ]);
    }

    private function updateStatus(Appointment $appointment, string $status)
    {
        $admin = User::factory()->create(['role' => 'admin']);

        return $this->actingAs($admin)->patch(
            route('admin.appointments.update-status', $appointment),
            ['status' => $status]
        );
    }

    public function test_cliente_que_autorizou_recebe_email_e_whatsapp(): void
    {
        Mail::fake();
        $sender = $this->fakeSender();
        $this->app->instance(WhatsAppSender::class, $sender);

        $appointment = Appointment::factory()->status('pendente')->create([
            'user_id' => $this->client(true)->id,
        ]);

        $this->updateStatus($appointment, 'confirmado');

        $this->assertCount(1, $sender->sent);
        $this->assertSame('5554999991111', $sender->sent[0]['phone']);
        $this->assertStringContainsString('confirmado', $sender->sent[0]['message']);

        $this->assertDatabaseHas('notification_logs', [
            'appointment_id' => $appointment->id,
            'channel' => 'whatsapp',
            'type' => 'status_confirmado',
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('notification_logs', [
            'appointment_id' => $appointment->id,
            'channel' => 'email',
            'status' => 'sent',
        ]);
    }

    public function test_cliente_sem_autorizacao_recebe_so_email(): void
    {
        Mail::fake();
        $sender = $this->fakeSender();
        $this->app->instance(WhatsAppSender::class, $sender);

        $appointment = Appointment::factory()->status('pendente')->create([
            'user_id' => $this->client(false)->id,
        ]);

        $this->updateStatus($appointment, 'confirmado');

        $this->assertSame([], $sender->sent);
        $this->assertDatabaseMissing('notification_logs', [
            'appointment_id' => $appointment->id,
            'channel' => 'whatsapp',
        ]);
        $this->assertDatabaseHas('notification_logs', [
            'appointment_id' => $appointment->id,
            'channel' => 'email',
            'status' => 'sent',
        ]);
    }

    public function test_cliente_sem_telefone_nao_recebe_whatsapp(): void
    {
        Mail::fake();
        $sender = $this->fakeSender();
        $this->app->instance(WhatsAppSender::class, $sender);

        $appointment = Appointment::factory()->status('pendente')->create([
            'user_id' => $this->client(true, null)->id,
        ]);

        $this->updateStatus($appointment, 'confirmado');

        $this->assertSame([], $sender->sent);
    }

    /**
     * Testes S16 e N8 do roteiro: o WhatsApp fora do ar nao pode derrubar a
     * tela do admin nem impedir o e-mail (RNF10).
     */
    public function test_whatsapp_fora_do_ar_nao_impede_o_email(): void
    {
        Mail::fake();
        $this->app->instance(WhatsAppSender::class, $this->brokenSender());

        $appointment = Appointment::factory()->status('pendente')->create([
            'user_id' => $this->client(true)->id,
        ]);

        $this->updateStatus($appointment, 'confirmado')->assertSessionHas('success');

        $this->assertSame('confirmado', $appointment->fresh()->status);
        Mail::assertSentCount(1);

        $this->assertDatabaseHas('notification_logs', [
            'appointment_id' => $appointment->id,
            'channel' => 'email',
            'status' => 'sent',
        ]);

        $log = NotificationLog::where('channel', 'whatsapp')->first();
        $this->assertSame('failed', $log->status);
        $this->assertStringContainsString('fora do ar', $log->error);
    }

    public function test_o_mesmo_aviso_nao_sai_duas_vezes_pelo_mesmo_canal(): void
    {
        Mail::fake();
        $sender = $this->fakeSender();
        $this->app->instance(WhatsAppSender::class, $sender);

        $appointment = Appointment::factory()->status('pendente')->create([
            'user_id' => $this->client(true)->id,
        ]);
        $appointment->load(['user', 'service']);

        $notifier = $this->app->make(AppointmentNotifier::class);
        $notifier->statusChanged($appointment, 'confirmado');
        $notifier->statusChanged($appointment, 'confirmado');

        $this->assertCount(1, $sender->sent);
        $this->assertSame(1, NotificationLog::where('channel', 'whatsapp')->count());
    }

    /**
     * Se nenhum canal entregou, a marca de "lembrete enviado" nao pode ser
     * gravada: senao o cliente nunca mais receberia aquele lembrete.
     */
    public function test_lembrete_que_falhou_nos_dois_canais_e_tentado_de_novo(): void
    {
        Mail::shouldReceive('to')->andThrow(new RuntimeException('servidor de e-mail fora do ar'));
        $this->app->instance(WhatsAppSender::class, $this->brokenSender());

        $appointment = Appointment::factory()->status('confirmado')->create([
            'user_id' => $this->client(true)->id,
            'scheduled_at' => now()->addDay()->setTime(17, 0),
            'reminder_1day_sent' => false,
        ]);

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        $this->assertEquals(0, $appointment->fresh()->reminder_1day_sent);
        $this->assertSame(2, NotificationLog::where('status', 'failed')->count());
    }

    public function test_sem_credenciais_no_env_o_sistema_cai_no_modo_seguro(): void
    {
        config(['services.zapi' => [
            'enabled' => true,
            'base_url' => null,
            'channel_id' => null,
            'secret_key' => null,
        ]]);

        $this->assertInstanceOf(
            \App\Services\LogWhatsAppSender::class,
            $this->app->make(WhatsAppSender::class)
        );
    }

    public function test_lembrete_de_um_dia_tambem_vai_pelo_whatsapp(): void
    {
        Mail::fake();
        $sender = $this->fakeSender();
        $this->app->instance(WhatsAppSender::class, $sender);

        $appointment = Appointment::factory()->status('confirmado')->create([
            'user_id' => $this->client(true)->id,
            'scheduled_at' => now()->addDay()->setTime(17, 0),
            'reminder_1day_sent' => false,
        ]);

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        $this->assertCount(1, $sender->sent);
        $this->assertStringContainsString('Lembrete', $sender->sent[0]['message']);
        // A coluna nao tem cast de boolean no model, entao vem 1 do banco.
        $this->assertEquals(1, $appointment->fresh()->reminder_1day_sent);

        $this->assertDatabaseHas('notification_logs', [
            'appointment_id' => $appointment->id,
            'channel' => 'whatsapp',
            'type' => 'reminder_1day',
            'status' => 'sent',
        ]);
    }
}
