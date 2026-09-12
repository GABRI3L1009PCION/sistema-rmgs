<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema RMGS</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            color-scheme: light;
            --bg: #f3f8fc;
            --sidebar: #f7fbff;
            --surface: #ffffff;
            --ink: #07164a;
            --muted: #58709b;
            --line: #dce8f6;
            --green: #0aa574;
            --green-dark: #08764f;
            --blue: #1689f4;
            --blue-soft: #eaf6ff;
            --mint: #e7f7ee;
            --amber: #f2a900;
            --red: #ef4444;
            --shadow: 0 18px 42px rgba(23, 58, 111, .10);
        }
        * { box-sizing: border-box; }
        html, body { min-height: 100%; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, Segoe UI, system-ui, sans-serif;
            background:
                radial-gradient(circle at 85% 0%, rgba(20, 137, 244, .12), transparent 30%),
                linear-gradient(180deg, #f8fbff 0%, var(--bg) 100%);
            color: var(--ink);
        }
        a { color: inherit; text-decoration: none; }
        h1, h2, h3, p { margin: 0; }
        button, input, select { font: inherit; }
        .app-shell { display: grid; grid-template-columns: 244px minmax(0, 1fr); min-height: 100vh; }
        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 14px 12px;
            background: linear-gradient(180deg, #fbfdff 0%, #edf7ff 100%);
            border-right: 1px solid var(--line);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }
        .brand { display: block; margin: 4px 8px 18px; height: 82px; }
        .brand img { display: block; width: 100%; height: 82px; object-fit: contain; object-position: center; }
        .side-nav { display: grid; gap: 4px; }
        .side-link {
            display: grid;
            grid-template-columns: 28px 1fr;
            align-items: center;
            gap: 12px;
            min-height: 48px;
            padding: 0 18px;
            border-radius: 8px;
            color: #4d6591;
            font-weight: 700;
        }
        .side-link.active { background: linear-gradient(90deg, #e5f8ec, #effbf4); color: #057448; }
        .side-link svg { width: 22px; height: 22px; stroke-width: 2.4; }
        .side-footer { margin: 12px 8px 2px; display: grid; align-content: end; color: #55709c; }
        .side-footer form { margin: 0; }
        .logout-button { width: 100%; border: 0; background: #fff1f0; color: #b42318; cursor: pointer; text-align: left; }
        .logout-button:hover { background: #fee4e2; }
        .footer-card {
            padding: 12px 14px;
            border-radius: 8px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.78), rgba(255,255,255,.48)),
                linear-gradient(135deg, rgba(14, 144, 90, .14), rgba(22, 137, 244, .10));
            border: 1px solid rgba(220, 232, 246, .72);
        }
        .footer-card strong { display: block; margin-top: 8px; color: var(--ink); line-height: 1.2; }
        .main { min-width: 0; padding: 10px 18px 16px; overflow-x: hidden; }
        .topbar { display: grid; grid-template-columns: minmax(260px, 1fr) max-content max-content; align-items: center; gap: 12px; margin: 0 0 10px; }
        .search-box, .month-picker, .user-menu {
            min-height: 42px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, .88);
            box-shadow: 0 10px 26px rgba(23, 58, 111, .06);
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--muted);
        }
        .search-box { padding: 0 18px; width: 100%; }
        .search-box input { border: 0; outline: 0; width: 100%; background: transparent; color: var(--ink); }
        .month-picker { padding: 0 16px; color: var(--ink); font-weight: 800; }
        .user-menu { border: 0; background: transparent; box-shadow: none; color: var(--ink); font-weight: 900; justify-content: end; }
        .avatar { width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(180deg, #cfe5ff, #7397c3); display: grid; place-items: center; color: #34527a; flex: 0 0 auto; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            padding: 0 14px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: var(--surface);
            color: var(--ink);
            font-weight: 800;
            cursor: pointer;
        }
        .btn.primary { background: var(--green); color: white; border-color: var(--green); box-shadow: 0 12px 24px rgba(10, 165, 116, .22); }
        .btn.danger { color: var(--red); }
        .grid { display: grid; gap: 12px; }
        .kpis { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .two { grid-template-columns: minmax(0, 1.15fr) minmax(420px, .85fr); }
        .card { background: rgba(255, 255, 255, .96); border: 1px solid var(--line); border-radius: 8px; padding: 16px; box-shadow: 0 10px 26px rgba(23, 58, 111, .08); }
        .section { margin-top: 12px; }
        .flash { margin-bottom: 16px; border-color: #9bd7b3; background: #effaf3; color: #17623d; }
        .card-title { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
        .card-title h2 { font-size: 1.15rem; line-height: 1.2; }
        .muted { color: var(--muted); font-size: .86rem; }
        .pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 9px; border-radius: 999px; font-size: .78rem; font-weight: 900; }
        .pill.alert { background: #fff1f0; color: var(--red); }
        .pill.ok { background: #e8f8ef; color: var(--green-dark); }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        label { display: grid; gap: 6px; color: var(--muted); font-size: .86rem; font-weight: 800; }
        input, select { width: 100%; min-height: 42px; border: 1px solid var(--line); border-radius: 8px; padding: 8px 10px; color: var(--ink); background: white; }
        .error { color: var(--red); font-size: .8rem; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: .86rem; }
        th, td { padding: 9px 10px; border-bottom: 1px solid #e8eff7; text-align: left; vertical-align: middle; }
        th { color: #6078a5; font-size: .72rem; font-weight: 800; }
        .nav { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .topbar .nav { flex-wrap: nowrap; }
        #map { height: 380px; border-radius: 8px; border: 1px solid var(--line); overflow: hidden; }
        @media (max-width: 1450px) {
            .app-shell { grid-template-columns: 228px minmax(0, 1fr); }
            .main { padding: 10px 14px 14px; }
            .topbar { grid-template-columns: minmax(220px, 1fr) max-content max-content; gap: 12px; }
            .user-menu span:not(.avatar) { display: none; }
            .side-link { min-height: 44px; }
        }
        @media (max-width: 900px) {
            .app-shell { grid-template-columns: 1fr; }
            .sidebar { position: relative; height: auto; }
            .side-footer { display: none; }
            .topbar { grid-template-columns: 1fr; }
            .topbar .nav { flex-wrap: wrap; }
            .kpis, .two, .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div>
                <a class="brand" href="/" aria-label="RMGS - Inicio">
                    <img src="{{ asset('images/rmgs-logo.png') }}" alt="RMGS - Registro y Monitoreo de Generacion Solar Guatemala">
                </a>
                <nav class="side-nav">
                    <a class="side-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="/"><i data-lucide="home"></i>Dashboard</a>
                    <a class="side-link {{ request()->routeIs('farms.*') ? 'active' : '' }}" href="{{ route('farms.index') }}"><i data-lucide="landmark"></i>Granjas solares</a>
                    <a class="side-link {{ request()->routeIs('panels.*') ? 'active' : '' }}" href="{{ route('panels.index') }}"><i data-lucide="grid-2x2"></i>Paneles</a>
                    <a class="side-link {{ request()->routeIs('records.*') ? 'active' : '' }}" href="{{ route('records.index') }}"><i data-lucide="bar-chart-3"></i>Generacion</a>
                    <a class="side-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}"><i data-lucide="file-text"></i>Reportes</a>
                    <a class="side-link {{ request()->routeIs('alerts.*') ? 'active' : '' }}" href="{{ route('alerts.index') }}"><i data-lucide="bell"></i>Alertas</a>
                    <a class="side-link {{ request()->routeIs('projections.*') ? 'active' : '' }}" href="{{ route('projections.index') }}"><i data-lucide="line-chart"></i>Proyecciones</a>
                    <a class="side-link {{ request()->routeIs('map.*') ? 'active' : '' }}" href="{{ route('map.index') }}"><i data-lucide="map-pin"></i>Ver mapa</a>
                    <a class="side-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}"><i data-lucide="settings"></i>Configuracion</a>
                </nav>
            </div>
            <div class="side-footer">
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="side-link logout-button" type="submit"><i data-lucide="log-out"></i>Cerrar sesion</button>
                </form>
            </div>
        </aside>

        <main class="main">
            <header class="topbar">
                <label class="search-box" aria-label="Buscar">
                    <i data-lucide="search"></i>
                    <input type="search" placeholder="Buscar granjas, paneles, departamentos...">
                </label>
                <div class="nav">
                    <span class="month-picker"><i data-lucide="calendar-days"></i>{{ request()->routeIs('projections.*') ? '2026 - 2030' : 'Septiembre 2026' }} <i data-lucide="chevron-down"></i></span>
                    @if (request()->routeIs('panels.*'))
                        <a class="btn primary" href="{{ route('panels.create') }}"><i data-lucide="plus"></i>Nuevo panel</a>
                    @elseif (request()->routeIs('records.create') || request()->routeIs('records.edit'))
                        <a class="btn primary" href="{{ route('records.index') }}"><i data-lucide="bar-chart-3"></i>Ver resumen</a>
                    @elseif (request()->routeIs('records.index'))
                        <a class="btn primary" href="{{ route('records.create') }}"><i data-lucide="plus"></i>Registrar generacion</a>
                    @elseif (request()->routeIs('reports.*'))
                        <a class="btn primary" href="#report-preview"><i data-lucide="plus"></i>Generar reporte</a>
                    @elseif (request()->routeIs('alerts.*'))
                        <a class="btn primary" href="#recommendations"><i data-lucide="bell"></i>Configurar alertas</a>
                    @elseif (request()->routeIs('projections.*'))
                        <a class="btn primary" href="{{ route('projections.csv') }}"><i data-lucide="download"></i>Exportar</a>
                    @elseif (request()->routeIs('settings.*'))
                        <button class="btn primary" type="submit" form="settings-form"><i data-lucide="check"></i>Guardar cambios</button>
                    @else
                        <a class="btn primary" href="{{ route('farms.create') }}"><i data-lucide="plus"></i>Nueva granja</a>
                    @endif
                </div>
                <div class="user-menu">
                    <span class="avatar"><i data-lucide="user"></i></span>
                    <span>Gabriel Admin</span>
                    <i data-lucide="chevron-down"></i>
                </div>
            </header>

            @if (session('status'))
                <section class="card flash">{{ session('status') }}</section>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        if (window.lucide) {
            window.lucide.createIcons();
        }
    </script>
</body>
</html>
