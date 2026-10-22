<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Politica de Privacidade — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto px-4 py-10">
        <div class="bg-white shadow-sm rounded-lg p-6 sm:p-10">

            <h1 class="text-2xl font-bold text-gray-900">Politica de Privacidade</h1>
            <p class="mt-1 text-sm text-gray-500">Ultima atualizacao: {{ now()->format('d/m/Y') }}</p>

            <p class="mt-6 text-sm text-gray-700 leading-relaxed">
                Esta politica explica como a <strong>Dus Guri</strong>, de Almirante Tamandare do Sul (RS),
                trata os dados pessoais de quem usa o {{ config('app.name') }}, o sistema de
                agendamento da estetica. Ela segue a Lei Geral de Protecao de Dados (Lei 13.709/2018).
            </p>

            <h2 class="mt-8 text-lg font-semibold text-gray-900">1. Quais dados coletamos</h2>
            <ul class="mt-2 text-sm text-gray-700 leading-relaxed list-disc pl-5 space-y-1">
                <li><strong>Cadastro:</strong> nome, e-mail e telefone.</li>
                <li><strong>Veiculos:</strong> placa, modelo e cor dos carros que voce cadastrar.</li>
                <li><strong>Agendamentos:</strong> datas, periodos, servicos escolhidos, valores e observacoes.</li>
                <li><strong>Preferencias:</strong> data de aniversario, forma de contato preferida e observacoes, quando voce informar.</li>
                <li><strong>Acesso:</strong> sua senha e guardada de forma criptografada e nao pode ser lida por ninguem, nem por nos.</li>
            </ul>

            <h2 class="mt-8 text-lg font-semibold text-gray-900">2. Para que usamos</h2>
            <ul class="mt-2 text-sm text-gray-700 leading-relaxed list-disc pl-5 space-y-1">
                <li>Realizar e organizar os agendamentos.</li>
                <li>Manter o historico dos servicos feitos no seu veiculo.</li>
                <li>Enviar lembretes do seu atendimento e avisos sobre o seu agendamento.</li>
                <li>Entrar em contato com voce sobre o atendimento.</li>
            </ul>
            <p class="mt-2 text-sm text-gray-700 leading-relaxed">
                Nao usamos seus dados para publicidade e nao enviamos mensagens de divulgacao ou promocao.
            </p>

            <h2 class="mt-8 text-lg font-semibold text-gray-900">3. Com quem compartilhamos</h2>
            <p class="mt-2 text-sm text-gray-700 leading-relaxed">
                Nao vendemos nem cedemos seus dados. Eles sao acessados apenas por dois servicos
                necessarios para o sistema funcionar:
            </p>
            <ul class="mt-2 text-sm text-gray-700 leading-relaxed list-disc pl-5 space-y-1">
                <li><strong>Hostinger:</strong> hospeda o sistema e o banco de dados.</li>
                <li><strong>Z-API:</strong> intermedia o envio das mensagens de WhatsApp, apenas para quem autorizou.</li>
            </ul>

            <h2 class="mt-8 text-lg font-semibold text-gray-900">4. WhatsApp e e-mail</h2>
            <p class="mt-2 text-sm text-gray-700 leading-relaxed">
                Os avisos sobre o seu agendamento sao enviados por e-mail. O envio pelo WhatsApp e
                opcional: so acontece se voce autorizar, e voce pode desligar a qualquer momento na
                tela do seu perfil. Enviamos apenas mensagens sobre os seus agendamentos.
            </p>

            <h2 class="mt-8 text-lg font-semibold text-gray-900">5. Por quanto tempo guardamos</h2>
            <p class="mt-2 text-sm text-gray-700 leading-relaxed">
                Seus dados pessoais ficam guardados enquanto a sua conta existir. Se voce excluir a
                conta, os dados que identificam voce sao <strong>anonimizados</strong>: nome, e-mail,
                telefone, data de nascimento, preferencias e a placa dos veiculos sao apagados ou
                substituidos, de forma permanente e sem possibilidade de recuperacao.
            </p>
            <p class="mt-2 text-sm text-gray-700 leading-relaxed">
                O registro dos servicos realizados permanece sem ligacao com voce, apenas para o
                controle financeiro da estetica. Depois da anonimizacao, esse registro deixa de ser
                um dado pessoal.
            </p>

            <h2 class="mt-8 text-lg font-semibold text-gray-900">6. Seus direitos</h2>
            <p class="mt-2 text-sm text-gray-700 leading-relaxed">
                A qualquer momento voce pode:
            </p>
            <ul class="mt-2 text-sm text-gray-700 leading-relaxed list-disc pl-5 space-y-1">
                <li><strong>Acessar</strong> seus dados, pela tela do seu perfil.</li>
                <li><strong>Corrigir</strong> seus dados, pela mesma tela.</li>
                <li><strong>Excluir</strong> sua conta, pela opcao de exclusao no perfil.</li>
                <li><strong>Retirar a autorizacao</strong> do WhatsApp, sem perder o aviso por e-mail.</li>
            </ul>
            <p class="mt-2 text-sm text-gray-700 leading-relaxed">
                Se preferir, pode pedir qualquer uma dessas coisas pelo contato abaixo.
            </p>

            <h2 class="mt-8 text-lg font-semibold text-gray-900">7. Contato</h2>
            <p class="mt-2 text-sm text-gray-700 leading-relaxed">
                Duvidas sobre esta politica ou sobre os seus dados:
                <strong>WhatsApp (54) 99200-5378</strong>.
            </p>

            <div class="mt-10 pt-6 border-t border-gray-200">
                <a href="{{ url('/') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                    Voltar ao inicio
                </a>
            </div>
        </div>
    </div>
</body>
</html>
