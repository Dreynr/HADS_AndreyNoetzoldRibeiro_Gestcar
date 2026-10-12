<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:40px 20px;">
<tr><td align="center">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background-color:#ffffff;border-radius:8px;overflow:hidden;">

<tr><td style="background:linear-gradient(135deg,#1e3a5f,#2563eb);padding:32px;text-align:center;">
    <div style="font-size:48px;margin-bottom:8px;">
        @if($type === '1day') &#128197; @else &#9200; @endif
    </div>
    <h1 style="margin:0;color:#ffffff;font-size:22px;">GestCar</h1>
    <p style="margin:8px 0 0;color:rgba(255,255,255,0.9);font-size:14px;">
        @if($type === '1day') Lembrete: Amanha tem lavagem! @else Falta 1 hora para sua lavagem! @endif
    </p>
</td></tr>

<tr><td style="padding:32px;">
    @if($type === '1day')
    <p style="margin:0 0 20px;color:#374151;font-size:15px;line-height:1.7;">
        Ola, <strong>{{ $appointment->user->name }}</strong>!<br><br>
        Passando para lembrar que <strong>amanha</strong> voce tem um horario marcado conosco.
        Estamos preparando tudo para deixar seu carro impecavel!
    </p>
    @else
    <p style="margin:0 0 20px;color:#374151;font-size:15px;line-height:1.7;">
        Ola, <strong>{{ $appointment->user->name }}</strong>!<br><br>
        Falta apenas <strong>1 hora</strong> para o seu horario!
        Ja esta tudo pronto por aqui. Pode trazer o carro ou, se preferir,
        podemos combinar de ir buscar. E so nos avisar!
    </p>
    @endif

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f7ff;border-radius:8px;margin-bottom:24px;">
        <tr><td style="padding:20px;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding:6px 0;color:#6b7280;font-size:13px;width:110px;">Servico</td>
                    <td style="padding:6px 0;color:#1f2937;font-size:14px;font-weight:bold;">{{ $appointment->service->name }}</td>
                </tr>
                @if($appointment->extras->count())
                <tr>
                    <td style="padding:6px 0;color:#6b7280;font-size:13px;">Extras</td>
                    <td style="padding:6px 0;color:#1f2937;font-size:14px;">{{ $appointment->extras->pluck('name')->join(', ') }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:6px 0;color:#6b7280;font-size:13px;">Veiculo</td>
                    <td style="padding:6px 0;color:#1f2937;font-size:14px;">{{ $appointment->vehicle->model }} - {{ $appointment->vehicle->plate }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#6b7280;font-size:13px;">Data</td>
                    <td style="padding:6px 0;color:#1f2937;font-size:14px;font-weight:bold;">{{ $appointment->scheduled_at->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#6b7280;font-size:13px;">Horario</td>
                    <td style="padding:6px 0;color:#1f2937;font-size:14px;">{{ \App\Models\Appointment::periodLabel($appointment->period) }}</td>
                </tr>
            </table>
        </td></tr>
    </table>

    @if($type === '1day')
    <div style="background:#f0fdf4;border-left:4px solid #22c55e;padding:14px 16px;border-radius:0 6px 6px 0;">
        <p style="margin:0;color:#166534;font-size:13px;line-height:1.6;">
            <strong>Dicas para amanha:</strong><br>
            &bull; Retire objetos pessoais do veiculo antes de trazer<br>
            &bull; Chegue no horario para garantir o melhor atendimento<br>
            &bull; Em caso de imprevisto, cancele pelo sistema com antecedencia
        </p>
    </div>
    @else
    <div style="background:#eff6ff;border-left:4px solid #3b82f6;padding:14px 16px;border-radius:0 6px 6px 0;">
        <p style="margin:0;color:#1e40af;font-size:13px;line-height:1.6;">
            <strong>Estamos te esperando!</strong><br>
            Pode trazer o carro agora ou, se preferir, entre em contato que podemos combinar de buscar.
            O importante e que seu carro saia daqui brilhando!
        </p>
    </div>
    @endif
</td></tr>

<tr><td style="background-color:#f9fafb;padding:16px 32px;text-align:center;border-top:1px solid #e5e7eb;">
    <p style="margin:0;color:#9ca3af;font-size:12px;">GestCar - Estetica Automotiva</p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
