<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', '学联界高校教学资源共享平台')</title>
    <style>
        :root {
            --bg: #f5f7fb;
            --panel: #ffffff;
            --ink: #18202f;
            --muted: #667085;
            --line: #dfe5ef;
            --primary: #2364d2;
            --primary-dark: #18499c;
            --accent: #0f9f8f;
            --warn: #b7791f;
            --danger: #c53030;
            --soft: #edf4ff;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Microsoft YaHei", Arial, sans-serif;
            color: var(--ink);
            background: var(--bg);
            line-height: 1.6;
        }
        a { color: inherit; text-decoration: none; }
        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(255, 255, 255, .96);
            border-bottom: 1px solid var(--line);
            backdrop-filter: blur(10px);
        }
        .nav, .wrap {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
        }
        .backend-layout .nav,
        .backend-layout .wrap {
            width: 100%;
            max-width: none;
            margin: 0;
        }
        .backend-layout .nav {
            padding: 0 18px;
        }
        .nav {
            min-height: 64px;
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .brand {
            font-weight: 800;
            font-size: 20px;
            color: var(--primary-dark);
            white-space: nowrap;
        }
        .links {
            display: flex;
            gap: 6px;
            flex: 1;
            align-items: center;
            flex-wrap: wrap;
        }
        .links a, .link-button {
            border: 0;
            background: transparent;
            color: var(--muted);
            padding: 8px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        .links a:hover, .link-button:hover { background: var(--soft); color: var(--primary-dark); }
        .links a.active { background: var(--soft); color: var(--primary-dark); font-weight: 700; }
        .text-link { color: var(--primary); font-weight: 700; font-size: 14px; }
        .text-link:hover { color: var(--primary-dark); }
        .user-chip {
            font-size: 13px;
            color: var(--muted);
            white-space: nowrap;
        }
        .hero {
            background: linear-gradient(135deg, #f8fbff 0%, #eaf6f4 100%);
            border-bottom: 1px solid var(--line);
        }
        .hero-inner {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 42px 0 26px;
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(300px, .8fr);
            gap: 22px;
            align-items: stretch;
        }
        h1, h2, h3 { margin: 0 0 12px; line-height: 1.25; }
        h1 { font-size: clamp(30px, 5vw, 54px); max-width: 840px; }
        h2 { font-size: 24px; }
        h3 { font-size: 18px; }
        .lead { color: var(--muted); font-size: 16px; max-width: 760px; }
        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px;
        }
        .grid { display: grid; gap: 16px; }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .main { padding: 24px 0 44px; }
        .section { margin-top: 24px; }
        .toolbar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: end;
        }
        label { display: block; font-size: 13px; color: var(--muted); margin-bottom: 5px; }
        input, select, textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 10px 11px;
            background: #fff;
            font: inherit;
            color: var(--ink);
        }
        textarea { min-height: 94px; resize: vertical; }
        .field { min-width: 160px; flex: 1; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid var(--primary);
            border-radius: 6px;
            padding: 10px 14px;
            background: var(--primary);
            color: #fff;
            cursor: pointer;
            font-weight: 700;
            min-height: 40px;
        }
        .btn:hover { background: var(--primary-dark); }
        .btn.secondary { background: #fff; color: var(--primary); }
        .btn.danger { background: var(--danger); border-color: var(--danger); }
        .badges { display: flex; flex-wrap: wrap; gap: 6px; }
        .badge {
            display: inline-flex;
            border-radius: 999px;
            padding: 3px 8px;
            background: #edf4ff;
            color: var(--primary-dark);
            font-size: 12px;
            white-space: nowrap;
        }
        .badge.green { background: #e7f8f3; color: #087264; }
        .badge.gold { background: #fff4df; color: var(--warn); }
        .muted { color: var(--muted); }
        .small { font-size: 13px; }
        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
        }
        .card-body { padding: 15px; }
        .project-card { display: block; min-height: 150px; transition: transform .15s ease, box-shadow .15s ease; }
        .project-card:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(24, 32, 47, .08); }
        .mini-card {
            display: grid;
            gap: 6px;
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            min-height: 124px;
        }
        .mini-card span, .mini-card em { color: var(--muted); font-size: 13px; font-style: normal; }
        .usage-advice {
            display: grid;
            gap: 4px;
            padding: 10px 12px;
            border-left: 3px solid var(--accent);
            background: #f3fbf9;
            border-radius: 6px;
        }
        .usage-advice span { color: var(--muted); font-size: 13px; }
        .cover {
            height: 126px;
            background: linear-gradient(135deg, #d9e7ff, #dff7ef);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-weight: 800;
            padding: 12px;
            text-align: center;
        }
        .list { display: grid; gap: 10px; }
        .list-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            padding: 12px 0;
            border-bottom: 1px solid var(--line);
        }
        a.list-row:hover { color: var(--primary-dark); }
        .list-row:last-child { border-bottom: 0; }
        .section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }
        .section-title h2 { margin-bottom: 0; }
        .notice {
            padding: 12px 14px;
            border-radius: 8px;
            margin-top: 14px;
            background: #e7f8f3;
            color: #075e55;
        }
        .notice.error { background: #fff0f0; color: var(--danger); }
        .table-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: 8px; background: #fff; }
        table { width: 100%; border-collapse: collapse; min-width: 760px; }
        th, td { padding: 10px 12px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: top; }
        th { background: #f8fafc; color: var(--muted); font-size: 13px; }
        .split {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 18px;
        }
        .tabs {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }
        .stat { padding: 14px; border: 1px solid var(--line); background: #fff; border-radius: 8px; }
        .stat strong { font-size: 24px; display: block; }
        .avatar-preview {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid var(--line);
            margin-bottom: 14px;
        }
        .question-body, .post-body { white-space: pre-wrap; color: var(--ink); }
        .pagination { margin-top: 18px; }
        .backend-shell {
            display: grid;
            grid-template-columns: 228px minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }
        .backend-sidebar {
            position: sticky;
            top: 86px;
            min-height: calc(100vh - 128px);
            background: #152033;
            color: #edf4ff;
            border-radius: 8px;
            padding: 16px;
            border: 1px solid #22314c;
        }
        .backend-sidebar h2 {
            font-size: 18px;
            margin-bottom: 6px;
        }
        .backend-sidebar p { color: #aebbd0; font-size: 13px; margin: 0 0 16px; }
        .backend-menu {
            display: grid;
            gap: 6px;
        }
        .backend-menu a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 9px 10px;
            border-radius: 6px;
            color: #d7e1f4;
            font-size: 14px;
        }
        .backend-menu a:hover, .backend-menu a.active {
            background: #263a5f;
            color: #fff;
        }
        .backend-content {
            display: grid;
            gap: 18px;
        }
        .metric-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }
        .metric-card {
            display: grid;
            gap: 4px;
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
        }
        .metric-card strong { font-size: 26px; line-height: 1.1; }
        .bar-list { display: grid; gap: 12px; }
        .bar-row { display: grid; grid-template-columns: 110px 1fr 48px; gap: 10px; align-items: center; font-size: 13px; }
        .bar-track { height: 10px; background: #edf1f7; border-radius: 999px; overflow: hidden; }
        .bar-fill { display: block; height: 100%; background: var(--primary); border-radius: 999px; }
        .timeline { display: grid; gap: 12px; }
        .timeline-item { border-left: 3px solid var(--primary); padding-left: 12px; }
        .role-select-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }
        .role-option {
            display: grid;
            gap: 6px;
            min-height: 132px;
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            cursor: pointer;
            color: var(--ink);
        }
        .role-option input { width: auto; }
        .role-option span { color: var(--muted); font-size: 13px; }
        .role-option:has(input:checked) {
            border-color: var(--primary);
            background: var(--soft);
        }
        .backend-layout .main {
            padding: 0;
        }
        .backend-layout .notice {
            margin: 10px 16px;
        }
        .backend-layout .backend-shell {
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 0;
            min-height: calc(100vh - 64px);
        }
        .backend-layout .backend-sidebar {
            top: 64px;
            min-height: calc(100vh - 64px);
            border-radius: 0;
            border-left: 0;
            border-top: 0;
            border-bottom: 0;
        }
        .backend-layout .backend-content {
            min-width: 0;
            padding: 16px;
        }
        .backend-layout .backend-login-page {
            width: 100%;
            min-height: calc(100vh - 64px);
            padding: 16px;
        }
        .backend-layout footer {
            display: none;
        }
        footer {
            padding: 26px 0;
            border-top: 1px solid var(--line);
            color: var(--muted);
            text-align: center;
            background: #fff;
        }
        @media (max-width: 900px) {
            .hero-inner, .split, .backend-shell { grid-template-columns: 1fr; }
            .backend-sidebar { position: static; min-height: auto; }
            .backend-layout .backend-shell { grid-template-columns: 1fr; }
            .backend-layout .backend-sidebar { min-height: auto; }
            .grid-4, .grid-3, .grid-2 { grid-template-columns: 1fr 1fr; }
            .tabs, .metric-grid, .role-select-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 560px) {
            .nav { align-items: flex-start; flex-direction: column; padding: 12px 0; }
            .grid-4, .grid-3, .grid-2, .tabs, .metric-grid, .role-select-grid { grid-template-columns: 1fr; }
            .field { min-width: 100%; }
            h1 { font-size: 30px; }
            .list-row { grid-template-columns: 1fr; }
            .bar-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="{{ request()->routeIs('platform.backend*') ? 'backend-layout' : '' }}">
    <header class="topbar">
        <nav class="nav">
            <a class="brand" href="{{ auth()->check() ? route('platform.dashboard') : route('platform.home') }}">学联界资源共享平台</a>
            <div class="links">
                @auth
                    <a class="{{ request()->routeIs('platform.dashboard') ? 'active' : '' }}" href="{{ route('platform.dashboard') }}">首页</a>
                    <a class="{{ request()->routeIs('platform.resources*') ? 'active' : '' }}" href="{{ route('platform.resources') }}">资源检索</a>
                    <a class="{{ request()->routeIs('platform.questions*') ? 'active' : '' }}" href="{{ route('platform.questions') }}">历年题目解析</a>
                    <a class="{{ request()->routeIs('platform.boards*') || request()->routeIs('platform.posts*') ? 'active' : '' }}" href="{{ route('platform.boards') }}">共享资源池</a>
                    <a class="{{ request()->routeIs('platform.announcements*') ? 'active' : '' }}" href="{{ route('platform.announcements') }}">公告中心</a>
                    <a class="{{ request()->routeIs('platform.backend*') ? 'active' : '' }}" href="{{ route('platform.backend') }}">角色后台</a>
                    <a href="{{ route('platform.dashboard') }}#profile">个人中心</a>
                @endauth
            </div>
            @auth
                <span class="user-chip">{{ auth()->user()->nickname ?: auth()->user()->username }} · {{ auth()->user()->role }}</span>
                <form method="post" action="{{ route('platform.logout') }}">
                    @csrf
                    <button class="link-button" type="submit">退出</button>
                </form>
            @endauth
        </nav>
    </header>

    @if(session('success'))
        <div class="wrap notice">{{ session('success') }}</div>
    @endif
    @if(session('error') || $errors->any())
        <div class="wrap notice error">
            {{ session('error') ?: $errors->first() }}
        </div>
    @endif

    @yield('hero')

    <main class="wrap main">
        @yield('content')
    </main>

    <footer>
        学联界高校教学资源共享平台 · 教师资源共建、学生精准获取、管理员规范维护
    </footer>
</body>
</html>
