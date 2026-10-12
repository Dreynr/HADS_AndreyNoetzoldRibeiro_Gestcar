<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:40px 20px;">
<tr><td align="center">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background-color:#ffffff;border-radius:8px;overflow:hidden;">

<!-- BOAS-VINDAS -->
<tr><td style="padding:36px 32px 0;text-align:center;">
    <h1 style="margin:0 0 8px;color:#1f2937;font-size:24px;font-weight:bold;">
        Bem-vindo(a) a GestCar!
    </h1>
    <div style="width:50px;height:3px;background:#2563eb;margin:0 auto 24px;border-radius:2px;"></div>

    <p style="margin:0 0 20px;color:#374151;font-size:15px;line-height:1.7;">
        Ola, <strong>{{ $user->name }}</strong>!
    </p>
    <p style="margin:0 0 24px;color:#374151;font-size:15px;line-height:1.7;">
        Que bom ter voce com a gente! A partir de agora, cuidar do seu carro ficou muito mais facil.
        Agende lavagens, acompanhe o historico e escolha os servicos ideais &mdash; tudo na palma da mao.
    </p>
</td></tr>

<!-- O QUE VOCE PODE FAZER -->
<tr><td style="padding:0 32px;">
    <p style="margin:0 0 16px;color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:1.5px;font-weight:bold;">O que voce pode fazer</p>

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:12px 16px;background:#eff6ff;border-radius:8px;margin-bottom:8px;">
                <table cellpadding="0" cellspacing="0"><tr>
                    <td style="font-size:24px;padding-right:14px;vertical-align:middle;">&#128197;</td>
                    <td>
                        <p style="margin:0;color:#1f2937;font-size:14px;font-weight:bold;">Agendar lavagens online 24h</p>
                        <p style="margin:2px 0 0;color:#6b7280;font-size:12px;">Escolha a data e horario que melhor funcionam pra voce</p>
                    </td>
                </tr></table>
            </td>
        </tr>
        <tr><td style="height:8px;"></td></tr>
        <tr>
            <td style="padding:12px 16px;background:#f0fdf4;border-radius:8px;">
                <table cellpadding="0" cellspacing="0"><tr>
                    <td style="font-size:24px;padding-right:14px;vertical-align:middle;">&#128663;</td>
                    <td>
                        <p style="margin:0;color:#1f2937;font-size:14px;font-weight:bold;">Cadastrar seus veiculos</p>
                        <p style="margin:2px 0 0;color:#6b7280;font-size:12px;">Adicione todos os seus carros para agilizar o agendamento</p>
                    </td>
                </tr></table>
            </td>
        </tr>
        <tr><td style="height:8px;"></td></tr>
        <tr>
            <td style="padding:12px 16px;background:#fef3c7;border-radius:8px;">
                <table cellpadding="0" cellspacing="0"><tr>
                    <td style="font-size:24px;padding-right:14px;vertical-align:middle;">&#128202;</td>
                    <td>
                        <p style="margin:0;color:#1f2937;font-size:14px;font-weight:bold;">Acompanhar o historico</p>
                        <p style="margin:2px 0 0;color:#6b7280;font-size:12px;">Veja todos os servicos realizados e valores</p>
                    </td>
                </tr></table>
            </td>
        </tr>
    </table>
</td></tr>

<!-- BOTAO -->
<tr><td style="padding:28px 32px;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr><td align="center">
            <a href="{{ config('app.url') }}/dashboard" style="display:inline-block;background:#2563eb;color:#ffffff;font-size:15px;font-weight:bold;text-decoration:none;padding:14px 36px;border-radius:8px;">
                Agendar minha primeira lavagem
            </a>
        </td></tr>
    </table>
</td></tr>

<!-- MENSAGEM FINAL -->
<tr><td style="padding:0 32px 32px;">
    <p style="margin:0;color:#9ca3af;font-size:13px;text-align:center;line-height:1.6;">
        Precisa de ajuda? Responda este e-mail ou entre em contato conosco.
        Estamos aqui para deixar seu carro sempre impecavel!
    </p>
</td></tr>

<!-- RODAPE -->
<tr><td style="background-color:#f9fafb;padding:24px 32px;text-align:center;border-top:1px solid #e5e7eb;">
    <img src="{{ config('app.url') }}/images/logo-sem-fundo.png" alt="GestCar" style="max-width:140px;height:auto;margin-bottom:8px;">
    <p style="margin:0;color:#9ca3af;font-size:11px;">Estetica Automotiva &bull; by ProWebProfile</p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
