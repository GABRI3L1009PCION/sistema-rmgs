<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema RMGS</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f7f3;
            --surface: #ffffff;
            --ink: #17211b;
            --muted: #667569;
            --line: #dce5de;
            --green: #1f7a4d;
            --teal: #0f766e;
            --amber: #c77700;
            --red: #b42318;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, Segoe UI, system-ui, sans-serif;
            background: var(--bg);
            color: var(--ink);
        }
        a { color: inherit; text-decoration: none; }
        .shell { max-width: 1240px; margin: 0 auto; padding: 24px; }
        .topbar {
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
            padding: 16px 0 24px;
        }
        .brand { display: flex; align-items: center; gap: 12px; }
        .mark {
            width: 44px; height: 44px; border-radius: 8px;
            background: linear-gradient(135deg, #f6c445, #1f7a4d);
            display: grid; place-items: center; color: white; font-weight: 800;
        }
        h1, h2, h3, p { margin: 0; }
        h1 { font-size: 1.45rem; line-height: 1.2; }
        h2 { font-size: 1rem; margin-bottom: 12px; }
        .muted { color: var(--muted); font-size: .9rem; }
        .nav { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-height: 40px; padding: 0 14px; border-radius: 8px;
            border: 1px solid var(--line); background: var(--surface); font-weight: 700;
        }
        .btn.primary { background: var(--green); color: white; border-color: var(--green); }
        .grid { display: grid; gap: 16px; }
        .stats { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .two { grid-template-columns: minmax(0, 1.1fr) minmax(320px, .9fr); }
        .card {
            background: var(--surface); border: 1px solid var(--line); border-radius: 8px;
            padding: 18px; box-shadow: 0 8px 18px rgba(23, 33, 27, .05);
        }
        .stat .label { color: var(--muted); font-size: .82rem; }
        .stat .value { font-size: 1.65rem; font-weight: 800; margin-top: 4px; }
        .stat .unit { font-size: .85rem; color: var(--muted); font-weight: 600; }
        #map { height: 430px; border-radius: 8px; border: 1px solid var(--line); }
        table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        th, td { padding: 10px; border-bottom: 1px solid var(--line); text-align: left; }
        th { color: var(--muted); font-size: .78rem; text-transform: uppercase; }
        .pill {
            display: inline-flex; align-items: center; padding: 4px 8px; border-radius: 999px;
            font-size: .78rem; font-weight: 800;
        }
        .pill.alert { background: #fff1f0; color: var(--red); }
        .pill.ok { background: #ecfdf3; color: var(--green); }
        .section { margin-top: 16px; }
        .flash { margin-bottom: 16px; border-color: #9bd7b3; background: #effaf3; color: #17623d; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        label { display: grid; gap: 6px; color: var(--muted); font-size: .86rem; font-weight: 700; }
        input, select {
            width: 100%; min-height: 42px; border: 1px solid var(--line); border-radius: 8px;
            padding: 8px 10px; color: var(--ink); background: white;
        }
        .error { color: var(--red); font-size: .8rem; margin-top: 4px; }
        @media (max-width: 900px) {
            .stats, .two, .form-grid { grid-template-columns: 1fr; }
            .topbar { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body>
    <main class="shell">
        <header class="topbar">
            <a class="brand" href="/">
                <span class="mark">RM</span>
                <span>
                    <h1>Registro y Monitoreo de Generacion Solar</h1>
                    <span class="muted">Guatemala por departamento</span>
                </span>
            </a>
            <nav class="nav">
                <a class="btn" href="/api/docs">API</a>
                <a class="btn" href="/api/stats">JSON</a>
                <a class="btn primary" href="{{ route('farms.create') }}">Nueva granja</a>
            </nav>
        </header>

        @if (session('status'))
            <section class="card flash">{{ session('status') }}</section>
        @endif

        @yield('content')
    </main>
</body>
</html>
