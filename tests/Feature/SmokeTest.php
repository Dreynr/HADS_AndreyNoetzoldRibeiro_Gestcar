<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\ClientNote;
use App\Models\FollowUp;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Wash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Abre todas as telas do sistema com dados reais dentro.
 *
 * Os outros testes provam as regras de negocio; este prova que nenhuma tela
 * quebra. Erro de Blade, variavel que a view espera e o controller nao manda,
 * rota renomeada e metodo que deixou de existir aparecem aqui — e so aqui,
 * porque um teste de regra quase nunca renderiza a pagina inteira.
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    private array $parametros = [];

    protected function setUp(): void
    {
        parent::setUp();

        $cliente = User::factory()->create([
            'role' => 'cliente',
            'phone' => '5554999991111',
            'whatsapp_opt_in' => true,
        ]);

        $veiculo = Vehicle::factory()->create(['user_id' => $cliente->id]);
        $servico = Service::factory()->create();

        $agendamento = Appointment::factory()->create([
            'user_id' => $cliente->id,
            'vehicle_id' => $veiculo->id,
            'service_id' => $servico->id,
        ]);

        $lavagem = Wash::factory()->create([
            'appointment_id' => $agendamento->id,
            'user_id' => $cliente->id,
            'vehicle_id' => $veiculo->id,
            'service_id' => $servico->id,
        ]);

        $produto = Product::factory()->withBalance(3)->create(['min_quantity' => 5]);
        $produto->registerMovement(Product::TYPE_IN, 2, 'Compra inicial');

        $autor = User::factory()->create(['role' => 'admin']);

        $nota = ClientNote::create([
            'client_id' => $cliente->id,
            'admin_id' => $autor->id,
            'content' => 'Prefere atendimento de manha.',
        ]);

        $followUp = FollowUp::create([
            'client_id' => $cliente->id,
            'type' => 'retorno',
            'title' => 'Chamar de volta',
            'due_date' => today(),
        ]);

        $this->parametros = [
            'appointment' => $agendamento->id,
            'client' => $cliente->id,
            'user' => $cliente->id,
            'vehicle' => $veiculo->id,
            'service' => $servico->id,
            'wash' => $lavagem->id,
            'product' => $produto->id,
            'note' => $nota->id,
            'followUp' => $followUp->id,
        ];
    }

    /**
     * @return array<int, string> as URLs GET cujo nome comeca com o prefixo
     */
    private function urls(string $prefixo): array
    {
        $urls = [];

        foreach (Route::getRoutes() as $rota) {
            $nome = $rota->getName();

            if (!$nome || !str_starts_with($nome, $prefixo) || !in_array('GET', $rota->methods())) {
                continue;
            }

            $faltou = false;
            $parametros = [];

            foreach ($rota->parameterNames() as $parametro) {
                if (!isset($this->parametros[$parametro])) {
                    $faltou = true;
                    break;
                }

                $parametros[$parametro] = $this->parametros[$parametro];
            }

            if ($faltou) {
                continue;
            }

            $urls[$nome] = route($nome, $parametros);
        }

        return $urls;
    }

    public function test_nenhuma_tela_do_admin_quebra(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $urls = $this->urls('admin.');

        $this->assertNotEmpty($urls, 'Nenhuma rota de admin encontrada.');

        foreach ($urls as $nome => $url) {
            $resposta = $this->actingAs($admin)->get($url);

            $this->assertTrue(
                $resposta->isSuccessful(),
                "A tela {$nome} ({$url}) respondeu {$resposta->status()}."
            );
        }
    }

    public function test_nenhuma_tela_do_cliente_quebra(): void
    {
        $cliente = User::find($this->parametros['client']);

        $telas = [
            route('dashboard'),
            route('appointments.index'),
            route('appointments.create'),
            route('history.index'),
            route('vehicles.index'),
            route('profile.edit'),
        ];

        foreach ($telas as $url) {
            $resposta = $this->actingAs($cliente)->get($url);

            $this->assertTrue(
                $resposta->isSuccessful(),
                "A tela {$url} respondeu {$resposta->status()}."
            );
        }
    }

    public function test_as_telas_publicas_abrem(): void
    {
        $this->get(route('privacy'))->assertOk();
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
        $this->get(route('password.request'))->assertOk();
        $this->get('/')->assertOk();
    }

    public function test_o_dashboard_do_admin_abre(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
    }
}
