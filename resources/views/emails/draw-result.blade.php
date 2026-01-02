<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado do Sorteio - Amigo Secreto</title>
</head>

<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Helvetica', 'Arial', sans-serif; color: #374151;">

    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f3f4f6; padding: 40px 0;">
        <tr>
            <td align="center">

                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">

                    <tr>
                        <td style="background: linear-gradient(135deg, #4f46e5 0%, #9333ea 100%); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 1px;">AMIGO SECRETO</h1>
                            <p style="color: #e0e7ff; margin: 5px 0 0 0; font-size: 14px;">O sorteio foi realizado! 🎲</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px 30px;">

                            <p style="font-size: 16px; line-height: 1.5; margin-bottom: 20px;">
                                Olá, <strong>{{ $santaName }}</strong>! 👋
                            </p>

                            <p style="font-size: 16px; line-height: 1.5; margin-bottom: 30px; color: #6b7280;">
                                O sorteio do grupo <strong style="color: #4f46e5;">{{ $groupName }}</strong> acabou de acontecer e seu par foi definido. Prepare o presente!
                            </p>

                            <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 30px; text-align: center; margin-bottom: 30px;">
                                <p style="margin: 0; font-size: 12px; text-transform: uppercase; letter-spacing: 2px; color: #9ca3af; font-weight: bold;">VOCÊ TIROU</p>
                                <h2 style="margin: 10px 0; font-size: 32px; color: #111827; font-weight: 900;">{{ $gifteeName }}</h2>

                                @if(!empty($wishlist))
                                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed #d1d5db;">
                                    <p style="margin: 0; font-size: 14px; color: #6b7280; font-style: italic;">
                                        🎁 Dica de presente: <br>
                                        <strong style="color: #4b5563; font-style: normal;">"{{ $wishlist }}"</strong>
                                    </p>
                                </div>
                                @else
                                <p style="margin-top: 15px; font-size: 13px; color: #9ca3af; font-style: italic;">(Essa pessoa não cadastrou lista de desejos)</p>
                                @endif
                            </div>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                                <tr>
                                    <td width="50%" style="padding-right: 10px;">
                                        <div style="background-color: #eff6ff; padding: 15px; border-radius: 8px;">
                                            <p style="margin: 0; font-size: 11px; color: #3b82f6; font-weight: bold; text-transform: uppercase;">Valor Estipulado</p>
                                            <p style="margin: 5px 0 0 0; font-size: 16px; font-weight: bold; color: #1e40af;">R$ {{ number_format($budget, 2, ',', '.') }}</p>
                                        </div>
                                    </td>
                                    <td width="50%" style="padding-left: 10px;">
                                        <div style="background-color: #fdf2f8; padding: 15px; border-radius: 8px;">
                                            <p style="margin: 0; font-size: 11px; color: #db2777; font-weight: bold; text-transform: uppercase;">Data da Festa</p>
                                            <p style="margin: 5px 0 0 0; font-size: 16px; font-weight: bold; color: #9d174d;">{{ \Carbon\Carbon::parse($eventDate)->format('d/m/Y') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <div style="text-align: center;">
                                <a href="{{ route('login') }}" style="display: inline-block; background-color: #4f46e5; color: #ffffff; text-decoration: none; padding: 14px 30px; border-radius: 8px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3);">
                                    Acessar Painel
                                </a>
                            </div>

                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. Feito com ❤️.
                            </p>
                        </td>
                    </tr>
                </table>

                <p style="text-align: center; margin-top: 20px; font-size: 12px; color: #9ca3af;">
                    Você recebeu este e-mail porque participa de um grupo de Amigo Secreto.
                </p>

            </td>
        </tr>
    </table>

</body>

</html>