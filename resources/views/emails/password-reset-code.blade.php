<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:480px; background-color:#ffffff; border-radius:8px; overflow:hidden;">
                    <tr>
                        <td style="background-color:#1e3a5f; padding:24px 32px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:22px; font-weight:bold;">GestCar</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px; color:#374151; font-size:15px; line-height:1.6;">
                                Voce solicitou a recuperacao de senha da sua conta. Use o codigo abaixo para redefinir sua senha:
                            </p>
                            <div style="background-color:#f0f4ff; border:2px dashed #2b6cb0; border-radius:8px; padding:20px; text-align:center; margin:24px 0;">
                                <span style="font-size:36px; font-weight:bold; letter-spacing:8px; color:#1e3a5f; font-family:'Courier New',monospace;">{{ $code }}</span>
                            </div>
                            <p style="margin:0 0 8px; color:#374151; font-size:15px; line-height:1.6;">
                                Este codigo expira em <strong>15 minutos</strong>.
                            </p>
                            <p style="margin:0; color:#6b7280; font-size:13px; line-height:1.5;">
                                Se voce nao solicitou essa recuperacao, ignore este e-mail. Sua senha permanecera inalterada.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f9fafb; padding:16px 32px; text-align:center; border-top:1px solid #e5e7eb;">
                            <p style="margin:0; color:#9ca3af; font-size:12px;">GestCar - Estetica Automotiva</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
