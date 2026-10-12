<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:40px 20px;">
<tr><td align="center">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background-color:#ffffff;border-radius:8px;overflow:hidden;">

@php
    $colors = [
        'confirmado' => ['bg' => '#065f46', 'light' => '#d1fae5', 'text' => '#065f46', 'icon' => '&#10003;'],
        'cancelado' => ['bg' => '#991b1b', 'light' => '#fee2e2', 'text' => '#991b1b', 'icon' => '&#10007;'],
        'concluido' => ['bg' => '#1e3a5f', 'light' => '#dbeafe', 'text' => '#1e3a5f', 'icon' => '&#9733;'],
    ];
    $c = $colors[$newStatus] ?? $colors['confirmado'];
    $messages = [
        'confirmado' => 'Seu agendamento foi confirmado! Estamos te esperando.',
        'cancelado' => 'Infelizmente seu agendamento foi cancelado. Entre em contato conosco para reagendar.',
        'concluido' => 'Seu servico foi concluido! Obrigado pela preferencia.',
    ];
@endphp

<tr><td style="background-color:{{ $c['bg'] }};padding:32px;text-align:center;">
    <div style="font-size:48px;margin-bottom:8px;color:#ffffff;">{!! $c['icon'] !!}</div>
    <h1 style="margin:0;color:#ffffff;font-size:22px;">GestCar</h1>
    <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:14px;">
        @if($newStatus === 'confirmado') Agendamento Confirmado
        @elseif($newStatus === 'cancelado') Agendamento Cancelado
        @else Servico Concluido
        @endif
    </p>
</td></tr>

<tr><td style="padding:32px;">
    <p style="margin:0 0 24px;color:#374151;font-size:15px;line-height:1.7;">
        Ola, <strong>{{ $appointment->user->name }}</strong>!<br>
        {{ $messages[$newStatus] ?? '' }}
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:{{ $c['light'] }};border-radius:8px;margin-bottom:24px;">
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
                <tr>
                    <td style="padding:6px 0;color:#6b7280;font-size:13px;">Valor</td>
                    <td style="padding:6px 0;color:{{ $c['text'] }};font-size:16px;font-weight:bold;">R$ {{ number_format($appointment->totalPrice(), 2, ',', '.') }}</td>
                </tr>
            </table>
        </td></tr>
    </table>

    @if(!empty($observation))
    <div style="background:#f3f4f6;border-left:4px solid {{ $c['bg'] }};padding:14px 16px;border-radius:0 6px 6px 0;margin-bottom:16px;">
        <p style="margin:0 0 4px;color:#374151;font-size:13px;font-weight:bold;">
            @if($newStatus === 'cancelado') Motivo: @else Observacao: @endif
        </p>
        <p style="margin:0;color:#4b5563;font-size:14px;line-height:1.6;">{{ $observation }}</p>
    </div>
    @endif

    @if($newStatus === 'confirmado')
    <div style="background:#fffbeb;border-left:4px solid #f59e0b;padding:14px 16px;border-radius:0 6px 6px 0;margin-bottom:16px;">
        <p style="margin:0;color:#92400e;font-size:13px;line-height:1.6;">
            <strong>Dica:</strong> Chegue no horario combinado para garantir o melhor atendimento. Caso precise desmarcar, faca pelo sistema com antecedencia.
        </p>
    </div>
    @endif

    @if($newStatus === 'concluido')
    <p style="margin:0;color:#6b7280;font-size:14px;text-align:center;line-height:1.6;">
        Esperamos que tenha gostado do resultado!<br>
        Seu carro merece sempre o melhor cuidado. Ate a proxima!
    </p>
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
