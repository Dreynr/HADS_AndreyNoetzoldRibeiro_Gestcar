<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:40px 20px;">
<tr><td align="center">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background-color:#ffffff;border-radius:8px;overflow:hidden;">

<tr><td style="background-color:#1e3a5f;padding:24px 32px;text-align:center;">
    <h1 style="margin:0;color:#ffffff;font-size:22px;">GestCar</h1>
    <p style="margin:6px 0 0;color:#93c5fd;font-size:13px;">Novo Agendamento Recebido</p>
</td></tr>

<tr><td style="padding:32px;">
    <p style="margin:0 0 20px;color:#374151;font-size:15px;line-height:1.6;">
        Um novo agendamento foi realizado no sistema. Confira os detalhes:
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f7ff;border-radius:8px;margin-bottom:24px;">
        <tr><td style="padding:20px;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding:6px 0;color:#6b7280;font-size:13px;width:120px;">Cliente</td>
                    <td style="padding:6px 0;color:#1f2937;font-size:14px;font-weight:bold;">{{ $appointment->user->name }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#6b7280;font-size:13px;">Telefone</td>
                    <td style="padding:6px 0;color:#1f2937;font-size:14px;">{{ $appointment->user->phone }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#6b7280;font-size:13px;">Veiculo</td>
                    <td style="padding:6px 0;color:#1f2937;font-size:14px;">{{ $appointment->vehicle->model }} - {{ $appointment->vehicle->plate }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#6b7280;font-size:13px;">Servico</td>
                    <td style="padding:6px 0;color:#1f2937;font-size:14px;font-weight:bold;">{{ $appointment->service->name }}</td>
                </tr>
                @if($appointment->extras->count())
                <tr>
                    <td style="padding:6px 0;color:#6b7280;font-size:13px;">Extras</td>
                    <td style="padding:6px 0;color:#1f2937;font-size:14px;">{{ $appointment->extras->pluck('name')->join(', ') }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:6px 0;color:#6b7280;font-size:13px;">Data</td>
                    <td style="padding:6px 0;color:#1f2937;font-size:14px;font-weight:bold;">{{ $appointment->scheduled_at->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#6b7280;font-size:13px;">Periodo</td>
                    <td style="padding:6px 0;color:#1f2937;font-size:14px;">{{ \App\Models\Appointment::periodLabel($appointment->period) }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#6b7280;font-size:13px;">Valor Total</td>
                    <td style="padding:6px 0;color:#1e3a5f;font-size:16px;font-weight:bold;">R$ {{ number_format($appointment->totalPrice(), 2, ',', '.') }}</td>
                </tr>
            </table>
        </td></tr>
    </table>

    @if($appointment->notes)
    <p style="margin:0 0 8px;color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Observacoes do cliente:</p>
    <p style="margin:0 0 20px;color:#374151;font-size:14px;background:#fef3c7;padding:12px;border-radius:6px;">{{ $appointment->notes }}</p>
    @endif

    <p style="margin:0;color:#6b7280;font-size:13px;text-align:center;">
        Acesse o painel para confirmar ou gerenciar este agendamento.
    </p>
</td></tr>

<tr><td style="background-color:#f9fafb;padding:16px 32px;text-align:center;border-top:1px solid #e5e7eb;">
    <p style="margin:0;color:#9ca3af;font-size:12px;">GestCar - Estetica Automotiva</p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
