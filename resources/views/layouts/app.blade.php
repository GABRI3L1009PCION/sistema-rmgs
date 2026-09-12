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
    <script>
        const rmgsTheme = localStorage.getItem('rmgs-theme') || 'light';
        document.documentElement.dataset.theme = rmgsTheme;
    </script>
    <style>
        :root {
            color-scheme: light;
            --bg: #e9f2ed;
            --sidebar: #f4faf6;
            --surface: #fbfdf9;
            --surface-2: #f0f7f3;
            --surface-3: #e7f1ec;
            --ink: #10231d;
            --muted: #60756e;
            --line: #cddfd7;
            --green: #10946a;
            --green-dark: #086547;
            --blue: #1677b9;
            --blue-soft: #eaf5ff;
            --mint: #dff4e9;
            --amber: #d89a00;
            --red: #d8483f;
            --danger-soft: #fff0ee;
            --warning-soft: #fff7df;
            --topbar: rgba(251, 253, 249, .94);
            --input-bg: #ffffff;
            --table-head: #eef6f2;
            --shadow: 0 18px 42px rgba(30, 75, 56, .12);
        }
        html[data-theme="dark"] {
            color-scheme: dark;
            --bg: #071411;
            --sidebar: #0b1d18;
            --surface: #10251f;
            --surface-2: #142e27;
            --surface-3: #1a3830;
            --ink: #ecfff7;
            --muted: #9ab8ad;
            --line: #284a40;
            --green: #20c58d;
            --green-dark: #7ee2bb;
            --blue: #55b7f7;
            --blue-soft: #112d43;
            --mint: #12382b;
            --amber: #f5bd36;
            --red: #ff746b;
            --danger-soft: #3a1718;
            --warning-soft: #352812;
            --topbar: rgba(12, 29, 25, .94);
            --input-bg: #0c201b;
            --table-head: #132b25;
            --shadow: 0 18px 42px rgba(0, 0, 0, .28);
        }
        * { box-sizing: border-box; }
        html, body { min-height: 100%; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, Segoe UI, system-ui, sans-serif;
            background:
                radial-gradient(circle at 82% 0%, color-mix(in srgb, var(--blue) 18%, transparent), transparent 32%),
                radial-gradient(circle at 18% 20%, color-mix(in srgb, var(--green) 12%, transparent), transparent 26%),
                linear-gradient(180deg, color-mix(in srgb, var(--surface) 68%, var(--bg)) 0%, var(--bg) 100%);
            color: var(--ink);
        }
        a { color: inherit; text-decoration: none; }
        h1, h2, h3, p { margin: 0; }
        button, input, select { font: inherit; }
        body,
        .sidebar,
        .topbar,
        .card,
        .btn,
        .side-link,
        .sidebar-toggle,
        .theme-toggle,
        .user-menu-button,
        .user-dropdown,
        input,
        select,
        table,
        th,
        td {
            transition:
                background-color .34s ease,
                color .34s ease,
                border-color .34s ease,
                box-shadow .34s ease;
        }
        .app-shell { display: grid; grid-template-columns: 244px minmax(0, 1fr); min-height: 100vh; transition: grid-template-columns .22s ease; }
        .app-shell.sidebar-collapsed { grid-template-columns: 76px minmax(0, 1fr); }
        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 14px 12px;
            background:
                linear-gradient(180deg, color-mix(in srgb, var(--surface) 90%, var(--green) 10%) 0%, var(--sidebar) 100%);
            border-right: 1px solid var(--line);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }
        .brand { display: block; margin: 4px 8px 18px; height: 82px; }
        .brand img { display: block; width: 100%; height: 82px; object-fit: contain; object-position: center; }
        .sidebar-toggle {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--surface);
            color: var(--muted);
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(23, 58, 111, .08);
        }
        .sidebar-toggle:hover { color: var(--green-dark); border-color: color-mix(in srgb, var(--green) 50%, var(--line)); background: var(--surface-2); }
        .sidebar-toggle svg { width: 23px; height: 23px; stroke-width: 2.8; }
        .side-nav { display: grid; gap: 4px; }
        .side-link {
            display: grid;
            grid-template-columns: 28px 1fr;
            align-items: center;
            gap: 12px;
            min-height: 48px;
            padding: 0 18px;
            border-radius: 8px;
            color: var(--muted);
            font-weight: 700;
        }
        .side-link:hover { background: var(--surface-2); color: var(--ink); }
        .side-link.active { background: linear-gradient(90deg, var(--mint), color-mix(in srgb, var(--blue-soft) 58%, transparent)); color: var(--green-dark); }
        .side-link svg { width: 22px; height: 22px; stroke-width: 2.4; }
        .app-shell.sidebar-collapsed .sidebar { padding-inline: 10px; }
        .app-shell.sidebar-collapsed .brand { width: 48px; height: 54px; margin: 8px auto 18px; overflow: hidden; }
        .app-shell.sidebar-collapsed .brand img { width: 130px; max-width: none; height: 54px; object-fit: contain; object-position: left center; }
        .app-shell.sidebar-collapsed .sidebar-toggle i,
        .app-shell.sidebar-collapsed .sidebar-toggle svg { transform: rotate(180deg); }
        .app-shell.sidebar-collapsed .side-link {
            grid-template-columns: 1fr;
            justify-items: center;
            padding: 0;
            gap: 0;
        }
        .app-shell.sidebar-collapsed .side-link span { display: none; }
        .footer-card {
            padding: 12px 14px;
            border-radius: 8px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.78), rgba(255,255,255,.48)),
                linear-gradient(135deg, rgba(14, 144, 90, .14), rgba(22, 137, 244, .10));
            border: 1px solid rgba(220, 232, 246, .72);
        }
        .footer-card strong { display: block; margin-top: 8px; color: var(--ink); line-height: 1.2; }
        .main { min-width: 0; padding: 0 18px 16px; overflow-x: hidden; }
        .topbar {
            position: sticky;
            top: 0;
            z-index: 12;
            min-height: 66px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin: 0 -18px;
            padding: 10px 20px;
            background: var(--topbar);
            border-bottom: 1px solid var(--line);
            box-shadow: 0 10px 24px color-mix(in srgb, var(--ink) 10%, transparent);
            backdrop-filter: blur(12px);
        }
        .topbar::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--green), var(--blue));
        }
        .search-box, .month-picker, .user-menu-button {
            min-height: 42px;
            border: 1px solid var(--line);
            background: var(--surface);
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
        .topbar-spacer { flex: 1; }
        .user-menu { position: relative; }
        .theme-toggle {
            width: 46px;
            height: 46px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: var(--surface-2);
            color: var(--green-dark);
            display: grid;
            place-items: center;
            cursor: pointer;
            box-shadow: 0 10px 26px color-mix(in srgb, var(--ink) 6%, transparent);
        }
        .theme-toggle:hover { border-color: color-mix(in srgb, var(--green) 44%, var(--line)); background: var(--surface-3); }
        .theme-toggle svg { transition: transform .36s ease, opacity .22s ease; }
        .theme-toggle.is-changing svg { transform: rotate(180deg) scale(.78); opacity: .35; }
        .user-menu-button {
            min-height: 46px;
            border: 1px solid var(--line);
            background: var(--surface-2);
            box-shadow: 0 10px 26px color-mix(in srgb, var(--ink) 6%, transparent);
            color: var(--ink);
            font-weight: 900;
            justify-content: end;
            cursor: pointer;
            padding: 0 12px 0 8px;
            border-radius: 999px;
        }
        .user-menu-button:hover { color: var(--green-dark); border-color: color-mix(in srgb, var(--green) 44%, var(--line)); }
        .avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(180deg, color-mix(in srgb, var(--blue) 18%, var(--surface)), color-mix(in srgb, var(--green) 24%, var(--surface-3))); display: grid; place-items: center; color: var(--green-dark); flex: 0 0 auto; }
        .user-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            width: 260px;
            padding: 8px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--surface);
            box-shadow: 0 22px 48px color-mix(in srgb, var(--ink) 18%, transparent);
            display: none;
            z-index: 30;
        }
        .user-menu.open .user-dropdown { display: block; }
        .user-summary {
            display: grid;
            grid-template-columns: 38px 1fr;
            gap: 10px;
            align-items: center;
            padding: 10px 10px 12px;
            border-bottom: 1px solid var(--line);
        }
        .user-summary strong { display: block; color: var(--ink); line-height: 1.2; }
        .user-summary div span { display: block; color: var(--muted); font-size: .8rem; margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .menu-action {
            width: 100%;
            min-height: 42px;
            margin-top: 8px;
            border: 0;
            border-radius: 8px;
            background: var(--surface-2);
            color: var(--ink);
            cursor: pointer;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 0 12px;
        }
        .menu-action span { display: inline-flex; align-items: center; gap: 10px; }
        .menu-action:hover { background: var(--surface-3); }
        .dropdown-logout { background: var(--danger-soft); color: var(--red); }
        .dropdown-logout:hover { background: color-mix(in srgb, var(--red) 16%, var(--danger-soft)); }
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
        .card { background: color-mix(in srgb, var(--surface) 96%, transparent); border: 1px solid var(--line); border-radius: 8px; padding: 16px; box-shadow: var(--shadow); }
        .section { margin-top: 12px; }
        .flash { margin-bottom: 16px; border-color: color-mix(in srgb, var(--green) 40%, var(--line)); background: var(--mint); color: var(--green-dark); }
        .card-title { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
        .card-title h2 { font-size: 1.15rem; line-height: 1.2; }
        .muted { color: var(--muted); font-size: .86rem; }
        .pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 9px; border-radius: 999px; font-size: .78rem; font-weight: 900; }
        .pill.alert { background: var(--danger-soft); color: var(--red); }
        .pill.ok { background: var(--mint); color: var(--green-dark); }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        label { display: grid; gap: 6px; color: var(--muted); font-size: .86rem; font-weight: 800; }
        input, select { width: 100%; min-height: 42px; border: 1px solid var(--line); border-radius: 8px; padding: 8px 10px; color: var(--ink); background: var(--input-bg); }
        .error { color: var(--red); font-size: .8rem; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: .86rem; }
        th, td { padding: 9px 10px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: middle; }
        th { color: var(--muted); font-size: .72rem; font-weight: 800; }
        tbody tr:hover { background: color-mix(in srgb, var(--surface-2) 70%, transparent); }
        .nav { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        #map { height: 380px; border-radius: 8px; border: 1px solid var(--line); overflow: hidden; }
        .health-item,
        .info-item,
        .installation-row,
        .visible-table th,
        .projection-table th,
        .alerts-table th,
        .farms-table th,
        .panel-table th { background: var(--table-head) !important; }
        .alert-tile.danger,
        .farm-detail-status.inactive { background: var(--danger-soft) !important; }
        .alert-tile,
        .farm-detail-status.maintenance { background: var(--warning-soft) !important; }
        .panel-row:hover,
        .panel-row.selected,
        .map-tip,
        .page-pill { background: var(--mint) !important; }
        .map-legend { background: color-mix(in srgb, var(--surface) 94%, transparent) !important; color: var(--muted) !important; }
        html[data-theme="dark"] .report-type,
        html[data-theme="dark"] .scenario,
        html[data-theme="dark"] .department-kpi,
        html[data-theme="dark"] .preview-panel,
        html[data-theme="dark"] .visible-table-wrap,
        html[data-theme="dark"] .alerts-table-wrap {
            background: var(--surface-2) !important;
            color: var(--ink) !important;
            border-color: var(--line) !important;
        }
        html[data-theme="dark"] .report-type.active {
            background: linear-gradient(90deg, color-mix(in srgb, var(--green) 18%, var(--surface-2)), var(--surface-2)) !important;
            border-color: var(--green) !important;
        }
        html[data-theme="dark"] .report-type p,
        html[data-theme="dark"] .scenario p,
        html[data-theme="dark"] .department-head p,
        html[data-theme="dark"] .department-kpi span,
        html[data-theme="dark"] .recent-alert p,
        html[data-theme="dark"] .recent-alert time,
        html[data-theme="dark"] .recommendation,
        html[data-theme="dark"] .distribution-legend,
        html[data-theme="dark"] .preview-header p {
            color: var(--muted) !important;
        }
        html[data-theme="dark"] .map-help {
            background: color-mix(in srgb, var(--blue) 13%, var(--surface-2)) !important;
            color: var(--muted) !important;
            border: 1px solid var(--line);
        }
        html[data-theme="dark"] .map-help strong { color: var(--ink) !important; }
        html[data-theme="dark"] .summary-line,
        html[data-theme="dark"] .recent-alert,
        html[data-theme="dark"] .recommendation {
            border-bottom-color: var(--line) !important;
        }
        html[data-theme="dark"] .alert-kpi-icon.high,
        html[data-theme="dark"] .priority.high,
        html[data-theme="dark"] .alert-state.active,
        html[data-theme="dark"] .scenario.conservative .scenario-icon {
            background: var(--danger-soft) !important;
        }
        html[data-theme="dark"] .alert-kpi-icon.medium,
        html[data-theme="dark"] .priority.medium {
            background: var(--warning-soft) !important;
        }
        html[data-theme="dark"] .priority.low,
        html[data-theme="dark"] .alert-kpi-icon.low {
            background: var(--blue-soft) !important;
        }
        html[data-theme="dark"] .alert-action { color: var(--blue) !important; }
        html[data-theme="dark"] .leaflet-container { background: var(--surface-2); }
        html[data-theme="dark"] .leaflet-tile { filter: brightness(.72) saturate(.9) contrast(1.08); }
        html[data-theme="dark"] .leaflet-control,
        html[data-theme="dark"] .leaflet-popup-content-wrapper,
        html[data-theme="dark"] .leaflet-popup-tip { background: var(--surface); color: var(--ink); border-color: var(--line); }
        .theme-wipe {
            position: fixed;
            inset: 0;
            z-index: 9999;
            pointer-events: none;
            opacity: 0;
            transform: scale(.12);
            transform-origin: calc(100% - 124px) 34px;
            background:
                radial-gradient(circle at calc(100% - 124px) 34px,
                    color-mix(in srgb, var(--green) 22%, transparent) 0%,
                    color-mix(in srgb, var(--blue) 18%, transparent) 34%,
                    transparent 68%);
        }
        .theme-wipe.is-active { animation: theme-wipe .62s ease-out; }
        @keyframes theme-wipe {
            0% { opacity: 0; transform: scale(.12); }
            18% { opacity: .86; }
            100% { opacity: 0; transform: scale(3.2); }
        }
        @media (prefers-reduced-motion: reduce) {
            body,
            .sidebar,
            .topbar,
            .card,
            .btn,
            .side-link,
            .sidebar-toggle,
            .theme-toggle,
            .user-menu-button,
            .user-dropdown,
            input,
            select,
            table,
            th,
            td { transition: none; }
            .theme-wipe.is-active { animation: none; }
            .theme-toggle svg { transition: none; }
        }
        @media (max-width: 1450px) {
            .app-shell { grid-template-columns: 228px minmax(0, 1fr); }
            .app-shell.sidebar-collapsed { grid-template-columns: 76px minmax(0, 1fr); }
            .main { padding: 0 14px 14px; }
            .user-menu-button span:not(.avatar) { display: none; }
            .side-link { min-height: 44px; }
        }
        @media (max-width: 900px) {
            .app-shell, .app-shell.sidebar-collapsed { grid-template-columns: 1fr; }
            .sidebar { position: relative; height: auto; }
            .sidebar-toggle { display: none; }
            .user-dropdown { width: min(280px, calc(100vw - 34px)); }
            .kpis, .two, .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="theme-wipe" aria-hidden="true" data-theme-wipe></div>
    <div class="app-shell">
        <aside class="sidebar">
            <div>
                <a class="brand" href="/" aria-label="RMGS - Inicio">
                    <img src="{{ asset('images/rmgs-logo.png') }}" alt="RMGS - Registro y Monitoreo de Generacion Solar Guatemala">
                </a>
                <nav class="side-nav">
                    <a class="side-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="/" title="Dashboard"><i data-lucide="home"></i><span>Dashboard</span></a>
                    <a class="side-link {{ request()->routeIs('farms.*') ? 'active' : '' }}" href="{{ route('farms.index') }}" title="Granjas solares"><i data-lucide="landmark"></i><span>Granjas solares</span></a>
                    <a class="side-link {{ request()->routeIs('panels.*') ? 'active' : '' }}" href="{{ route('panels.index') }}" title="Paneles"><i data-lucide="grid-2x2"></i><span>Paneles</span></a>
                    <a class="side-link {{ request()->routeIs('records.*') ? 'active' : '' }}" href="{{ route('records.index') }}" title="Generacion"><i data-lucide="bar-chart-3"></i><span>Generacion</span></a>
                    <a class="side-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}" title="Reportes"><i data-lucide="file-text"></i><span>Reportes</span></a>
                    <a class="side-link {{ request()->routeIs('alerts.*') ? 'active' : '' }}" href="{{ route('alerts.index') }}" title="Alertas"><i data-lucide="bell"></i><span>Alertas</span></a>
                    <a class="side-link {{ request()->routeIs('projections.*') ? 'active' : '' }}" href="{{ route('projections.index') }}" title="Proyecciones"><i data-lucide="line-chart"></i><span>Proyecciones</span></a>
                    <a class="side-link {{ request()->routeIs('map.*') ? 'active' : '' }}" href="{{ route('map.index') }}" title="Ver mapa"><i data-lucide="map-pin"></i><span>Ver mapa</span></a>
                </nav>
            </div>
        </aside>

        <main class="main">
            <header class="topbar">
                <button class="sidebar-toggle" type="button" aria-label="Ocultar menu" aria-expanded="true" data-sidebar-toggle>
                    <i data-lucide="panel-left-close"></i>
                </button>
                <div class="topbar-spacer"></div>
                <button class="theme-toggle" type="button" aria-label="Activar modo oscuro" title="Cambiar tema" data-theme-toggle>
                    <i data-lucide="moon"></i>
                </button>
                <div class="user-menu" data-user-menu>
                    <button class="user-menu-button" type="button" aria-haspopup="true" aria-expanded="false" data-user-menu-toggle>
                        <span class="avatar"><i data-lucide="user"></i></span>
                        <span>{{ auth()->user()->name ?? 'Usuario RMGS' }}</span>
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="user-dropdown" role="menu">
                        <div class="user-summary">
                            <span class="avatar"><i data-lucide="user"></i></span>
                            <div>
                                <strong>{{ auth()->user()->name ?? 'Usuario RMGS' }}</strong>
                                <span>{{ auth()->user()->email ?? 'Cuenta del sistema' }}</span>
                            </div>
                        </div>
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button class="menu-action dropdown-logout" type="submit" role="menuitem">
                                <span><i data-lucide="log-out"></i>Cerrar sesion</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            @if (session('status'))
                <section class="card flash">{{ session('status') }}</section>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        const appShell = document.querySelector('.app-shell');
        const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
        const sidebarPreference = localStorage.getItem('rmgs-sidebar-collapsed');

        function setSidebarCollapsed(collapsed) {
            appShell.classList.toggle('sidebar-collapsed', collapsed);
            sidebarToggle?.setAttribute('aria-expanded', String(!collapsed));
            sidebarToggle?.setAttribute('aria-label', collapsed ? 'Mostrar menu' : 'Ocultar menu');
            localStorage.setItem('rmgs-sidebar-collapsed', collapsed ? '1' : '0');
        }

        if (sidebarPreference === '1') {
            setSidebarCollapsed(true);
        }

        sidebarToggle?.addEventListener('click', () => {
            setSidebarCollapsed(!appShell.classList.contains('sidebar-collapsed'));
        });

        const userMenu = document.querySelector('[data-user-menu]');
        const userMenuToggle = document.querySelector('[data-user-menu-toggle]');
        const themeToggle = document.querySelector('[data-theme-toggle]');
        const themeWipe = document.querySelector('[data-theme-wipe]');

        function syncThemeLabel() {
            const isDark = document.documentElement.dataset.theme === 'dark';
            themeToggle?.querySelector('i')?.setAttribute('data-lucide', isDark ? 'sun' : 'moon');
            themeToggle?.setAttribute('aria-label', isDark ? 'Activar modo claro' : 'Activar modo oscuro');
            if (window.lucide) {
                window.lucide.createIcons();
            }
        }

        userMenuToggle?.addEventListener('click', (event) => {
            event.stopPropagation();
            const isOpen = userMenu?.classList.toggle('open') ?? false;
            userMenuToggle.setAttribute('aria-expanded', String(isOpen));
        });

        themeToggle?.addEventListener('click', (event) => {
            event.stopPropagation();
            const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            themeToggle.classList.add('is-changing');
            themeWipe?.classList.remove('is-active');
            void themeWipe?.offsetWidth;
            themeWipe?.classList.add('is-active');
            document.documentElement.dataset.theme = nextTheme;
            localStorage.setItem('rmgs-theme', nextTheme);
            syncThemeLabel();
            window.setTimeout(() => {
                themeToggle.classList.remove('is-changing');
                themeWipe?.classList.remove('is-active');
            }, 640);
        });

        document.addEventListener('click', (event) => {
            if (!userMenu?.contains(event.target)) {
                userMenu?.classList.remove('open');
                userMenuToggle?.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                userMenu?.classList.remove('open');
                userMenuToggle?.setAttribute('aria-expanded', 'false');
            }
        });

        if (window.lucide) {
            window.lucide.createIcons();
        }

        syncThemeLabel();
    </script>
</body>
</html>
