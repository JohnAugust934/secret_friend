<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifique seu E-mail</title>
</head>

<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Helvetica', 'Arial', sans-serif; color: #374151;">

    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f3f4f6; padding: 40px 0;">
        <tr>
            <td align="center">

                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">

                    <tr>
                        <td style="background: linear-gradient(135deg, #4f46e5 0%, #9333ea 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 800;">AMIGO SECRETO</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px 30px; text-align: center;">

                            <h2 style="color: #111827; font-size: 20px; font-weight: bold; margin-bottom: 20px;">
                                Olá, {{ $name }}! 👋
                            </h2>

                            <p style="font-size: 16px; line-height: 1.6; color: #6b7280; margin-bottom: 30px;">
                                Obrigado por se cadastrar! Antes de começar a criar grupos e participar dos sorteios, precisamos confirmar que este e-mail é realmente seu.
                            </p>

                            <div style="margin-bottom: 30px;">
                                <a href="{{ $url }}" style="display: inline-block; background-color: #4f46e5; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 50px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.4);">
                                    Confirmar meu E-mail
                                </a>
                            </div>

                            <p style="font-size: 14px; color: #9ca3af;">
                                Se o botão não funcionar, copie e cole o link abaixo no seu navegador:<br>
                                <span style="font-size: 12px; color: #4f46e5; word-break: break-all;">{{ $url }}</span>
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                Se você não criou uma conta, pode ignorar este e-mail.
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>

</html>