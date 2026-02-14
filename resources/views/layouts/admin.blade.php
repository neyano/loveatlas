<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>管理画面 - @yield('title', config('app.name', 'LoveAtlas'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+JP:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="admin">
    <aside class="admin-sidebar">
        <div class="admin-sidebar__logo">
            <a href="/admin">LoveAtlas Admin</a>
        </div>
        <nav class="admin-sidebar__nav">
            <a href="/admin" class="admin-sidebar__link">ダッシュボード</a>
            <a href="/admin/quotes" class="admin-sidebar__link">セリフ承認</a>
            <a href="/admin/reports" class="admin-sidebar__link">通報管理</a>
            <a href="/admin/users" class="admin-sidebar__link">ユーザー管理</a>
            <a href="/admin/works" class="admin-sidebar__link">作品管理</a>
            <a href="/admin/stats" class="admin-sidebar__link">統計</a>
        </nav>
        <div class="admin-sidebar__footer">
            <a href="/">サイトに戻る</a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-header">
            <h1 class="admin-header__title">@yield('title')</h1>
        </header>

        <main class="admin-content" id="app">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
