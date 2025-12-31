<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }

        .content {
            text-align: center;
        }

        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            margin-top: 20px;
        }

        .highlight {
            color: #4f46e5;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="logo">🎅 Amigo Secreto</div>
        </div>

        <div class="content">
            <h2>Olá, {{ $santa->name }}!</h2>
            <p>O sorteio do grupo <span class="highlight">{{ $group->name }}</span> foi realizado.</p>

            <p>O seu amigo secreto é:</p>

            <div style="background-color: #eef2ff; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <h1 style="margin: 0; color: #312e81;">{{ $giftee->name }}</h1>
            </div>

            @if($giftee->groups->find($group->id)?->pivot->wishlist)
            <p>🎁 <strong>Dica de presente:</strong></p>
            <p style="font-style: italic;">"{{ $giftee->groups->find($group->id)->pivot->wishlist }}"</p>
            @endif

            <p>O evento acontecerá em: <strong>{{ \Carbon\Carbon::parse($group->event_date)->format('d/m/Y') }}</strong></p>

            <a href="{{ route('groups.show', $group->id) }}" class="button">Ver no Site</a>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Amigo Secreto da Galera.
        </div>
    </div>
</body>

</html>