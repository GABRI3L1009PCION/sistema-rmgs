<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar | Sistema RMGS</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --ink: #061844;
            --muted: #5b7197;
            --line: #d8e6f5;
            --green: #0da36f;
            --blue: #1b8ef3;
            --surface: #ffffff;
            --soft: #eef7fc;
            --danger: #c0342b;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Instrument Sans", system-ui, sans-serif;
            color: var(--ink);
            background: #f7fbff;
        }
        .login-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            background: var(--surface);
        }
        .brand-panel {
            position: relative;
            overflow: hidden;
            padding: 58px 64px;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background:
                linear-gradient(135deg, rgba(4, 22, 52, .82), rgba(7, 76, 94, .58)),
                url("{{ asset('images/dashboard-hero-guatemala.png') }}") center/cover no-repeat;
        }
        .brand-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, rgba(255, 255, 255, .07) 1px, transparent 1px),
                linear-gradient(180deg, rgba(255, 255, 255, .06) 1px, transparent 1px);
            background-size: 68px 68px;
            opacity: .22;
        }
        .brand-panel::after {
            content: "";
            position: absolute;
            left: 12%;
            right: 12%;
            bottom: 70px;
            height: 4px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--green), var(--blue));
            opacity: .9;
        }
        .brand-panel img {
            position: relative;
            z-index: 1;
            width: min(500px, 92%);
            display: block;
            padding: 28px 34px;
            border: 1px solid rgba(255, 255, 255, .72);
            border-radius: 30px;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 24px 60px rgba(3, 18, 45, .36);
        }
        .form-panel {
            background: #fbfdff;
            padding: clamp(36px, 7vw, 96px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .form-card {
            width: min(520px, 100%);
        }
        .form-card h2 { margin: 0; font-size: 34px; letter-spacing: 0; }
        .form-card > p { margin: 10px 0 28px; color: var(--muted); line-height: 1.5; }
        label {
            display: block;
            margin-bottom: 8px;
            color: #405a84;
            font-size: 13px;
            font-weight: 800;
        }
        .field {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 54px;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 0 16px;
            background: #fff;
            color: #52709f;
        }
        .password-field { padding-right: 8px; }
        .field input {
            width: 100%;
            border: 0;
            outline: 0;
            font: inherit;
            color: var(--ink);
            background: transparent;
        }
        .password-toggle {
            width: 40px;
            height: 40px;
            border: 0;
            border-radius: 12px;
            background: transparent;
            color: #52709f;
            display: grid;
            place-items: center;
            cursor: pointer;
        }
        .password-toggle:hover {
            background: var(--soft);
            color: var(--blue);
        }
        .field-row { margin-bottom: 18px; }
        .error {
            margin: -6px 0 16px;
            color: var(--danger);
            font-size: 13px;
            font-weight: 700;
        }
        .options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin: 4px 0 24px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 700;
        }
        .options label {
            margin: 0;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
        }
        .submit {
            width: 100%;
            border: 0;
            min-height: 54px;
            border-radius: 14px;
            background: linear-gradient(90deg, var(--green), var(--blue));
            color: white;
            font: inherit;
            font-weight: 900;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 16px 32px rgba(18, 128, 190, .22);
        }
        @media (max-width: 860px) {
            .login-shell { grid-template-columns: 1fr; min-height: 100vh; }
            .brand-panel { min-height: 260px; padding: 30px; }
            .brand-panel::after { left: 30px; right: 30px; bottom: 26px; }
            .brand-panel img { width: min(340px, 92%); padding: 18px 20px; }
            .form-panel { padding: 32px 24px; align-items: stretch; }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="brand-panel">
            <img src="{{ asset('images/rmgs-logo.png') }}" alt="RMGS Guatemala">
        </section>

        <section class="form-panel">
            <form class="form-card" method="post" action="{{ route('login.store') }}">
                @csrf
                <h2>Iniciar sesion</h2>
                <p>Ingresa con una cuenta autorizada para operar el sistema RMGS.</p>

                <div class="field-row">
                    <label for="email">Correo electronico</label>
                    <div class="field">
                        <i data-lucide="mail"></i>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                    </div>
                </div>

                <div class="field-row">
                    <label for="password">Contraseña</label>
                    <div class="field password-field">
                        <i data-lucide="lock-keyhole"></i>
                        <input id="password" name="password" type="password" autocomplete="current-password" required>
                        <button class="password-toggle" type="button" aria-label="Mostrar contraseña" data-password-toggle>
                            <i data-lucide="eye"></i>
                        </button>
                    </div>
                </div>

                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror

                <div class="options">
                    <label><input type="checkbox" name="remember" value="1"> Recordar acceso</label>
                    <span>RMGS Guatemala</span>
                </div>

                <button class="submit" type="submit">
                    <i data-lucide="log-in"></i>
                    Entrar al sistema
                </button>

            </form>
        </section>
    </main>
    <script>
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.querySelector('[data-password-toggle]');

        passwordToggle?.addEventListener('click', () => {
            const shouldShow = passwordInput.type === 'password';
            passwordInput.type = shouldShow ? 'text' : 'password';
            passwordToggle.setAttribute('aria-label', shouldShow ? 'Ocultar contraseña' : 'Mostrar contraseña');
            passwordToggle.querySelector('i')?.setAttribute('data-lucide', shouldShow ? 'eye-off' : 'eye');
            lucide.createIcons();
        });

        lucide.createIcons();
    </script>
</body>
</html>
