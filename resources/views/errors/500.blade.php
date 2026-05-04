<!DOCTYPE html>
<html lang="es" data-theme="serralleria">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error del servidor</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
            background-color: #f3f4f6;
            color: #111827;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
        }
        .card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            max-width: 480px;
            width: 100%;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .emoji { font-size: 4rem; margin-bottom: 0.5rem; }
        h1 { font-size: 1.5rem; font-weight: 700; margin: 0 0 0.75rem; }
        p { color: #6b7280; margin: 0 0 1.5rem; line-height: 1.6; }
        .btn {
            display: inline-block;
            background-color: #F75211;
            color: #fff;
            text-decoration: none;
            padding: 0.625rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: background 0.15s;
        }
        .btn:hover { background-color: #d94710; }
        .divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.5rem 0 1rem;
            color: #9ca3af;
            font-size: 0.85rem;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }
        .games-hint { font-size: 0.875rem; color: #6b7280; margin-bottom: 1rem; }
        .game-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #F75211;
            text-decoration: underline;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="emoji">⚠️</div>
        <h1>Error del servidor</h1>
        <p>Ha ocurrido un error inesperado. Nuestro equipo ha sido notificado.<br>Por favor, inténtalo de nuevo más tarde.</p>
        <a href="/" class="btn">Volver al inicio</a>

        <div class="divider">Mientras esperas</div>
        <p class="games-hint">¿Por qué no juegas un momento?</p>
        <a href="/games" class="game-link">🎮 Ir a los minijuegos</a>
    </div>
</body>
</html>
