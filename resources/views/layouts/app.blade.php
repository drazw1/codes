<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PharmaHub') – Laravel</title>
    <style>
        :root {
            --bg:#080b14;--surface:#0f1420;--card:#141928;--border:#1e2740;
            --accent:#38bdf8;--green:#4ade80;--amber:#fbbf24;--red:#f87171;
            --text:#e2e8f0;--muted:#64748b;--radius:10px;
            --font:'JetBrains Mono',monospace;
        }
        *{box-sizing:border-box;margin:0;padding:0;}
        body{background:var(--bg);color:var(--text);font-family:var(--font);font-size:14px;min-height:100vh;}
        a{color:var(--accent);text-decoration:none;}
        a:hover{opacity:.8;}

        /* ── Topbar ── */
        .topbar{background:#060912;border-bottom:1px solid var(--border);padding:.9rem 2rem;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
        .logo{font-size:1.3rem;font-weight:900;color:var(--accent);letter-spacing:-1px;}
        .logo span{color:var(--green);}
        .nav{display:flex;gap:.25rem;align-items:center;}
        .nav a{color:var(--muted);padding:.4rem .85rem;border-radius:6px;font-size:.8rem;transition:all .2s;}
        .nav a:hover,.nav a.active{background:rgba(56,189,248,.1);color:var(--accent);}
        .nav-divider{width:1px;height:20px;background:var(--border);margin:0 .5rem;}
        .user-badge{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.25);color:var(--green);padding:.3rem .8rem;border-radius:20px;font-size:.75rem;}

        /* ── Layout ── */
        .wrapper{max-width:1300px;margin:0 auto;padding:2rem;}
        .page-title{font-size:1.5rem;font-weight:900;color:var(--accent);margin-bottom:.25rem;}
        .page-sub{color:var(--muted);font-size:.82rem;margin-bottom:1.75rem;}

        /* ── Cards ── */
        .card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;margin-bottom:1.5rem;}
        .card-title{font-size:.95rem;font-weight:700;color:var(--amber);margin-bottom:1rem;}

        /* ── Buttons ── */
        .btn{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1.1rem;border-radius:6px;border:none;font-family:var(--font);font-size:.8rem;font-weight:600;cursor:pointer;transition:all .2s;text-decoration:none;}
        .btn-primary{background:var(--accent);color:#000;}
        .btn-success{background:var(--green);color:#000;}
        .btn-danger{background:var(--red);color:#fff;}
        .btn-ghost{background:transparent;border:1px solid var(--border);color:var(--text);}
        .btn-sm{padding:.3rem .7rem;font-size:.75rem;}
        .btn:hover{filter:brightness(1.12);transform:translateY(-1px);}

        /* ── Forms ── */
        .form-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem;}
        .form-group{margin-bottom:1rem;}
        .form-group label{display:block;font-size:.75rem;color:var(--muted);margin-bottom:.35rem;}
        .form-group input,.form-group select,.form-group textarea{width:100%;background:var(--surface);border:1px solid var(--border);color:var(--text);padding:.5rem .75rem;border-radius:6px;font-family:var(--font);font-size:.82rem;}
        .form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--accent);}
        .form-error{color:var(--red);font-size:.75rem;margin-top:.3rem;}

        /* ── Table ── */
        .data-table{width:100%;border-collapse:collapse;font-size:.82rem;}
        .data-table th{background:var(--surface);color:var(--amber);padding:.55rem .75rem;text-align:left;border-bottom:1px solid var(--border);}
        .data-table td{padding:.5rem .75rem;border-bottom:1px solid #1a1e30;}
        .data-table tr:hover td{background:rgba(255,255,255,.02);}

        /* ── Badges ── */
        .badge{display:inline-block;padding:.15rem .55rem;border-radius:4px;font-size:.7rem;font-weight:700;}
        .badge-yes{background:rgba(248,113,113,.15);color:var(--red);}
        .badge-no{background:rgba(74,222,128,.12);color:var(--green);}
        .badge-low{background:rgba(251,191,36,.15);color:var(--amber);}
        .badge-ok{background:rgba(74,222,128,.15);color:var(--green);}
        .badge-out{background:rgba(248,113,113,.2);color:var(--red);}
        .badge-cat{background:rgba(56,189,248,.12);color:var(--accent);}

        /* ── Alerts ── */
        .alert{padding:.75rem 1rem;border-radius:6px;font-size:.82rem;margin-bottom:1rem;}
        .alert-success{background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.3);color:var(--green);}
        .alert-error{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:var(--red);}

        /* ── Pagination ── */
        .pagination{display:flex;gap:.35rem;margin-top:1.25rem;flex-wrap:wrap;align-items:center;}
        .pagination .page-item .page-link{background:var(--surface);border:1px solid var(--border);color:var(--muted);padding:.4rem .75rem;border-radius:5px;font-size:.8rem;display:block;transition:all .2s;}
        .pagination .page-item.active .page-link{background:var(--accent);color:#000;border-color:var(--accent);}
        .pagination .page-item .page-link:hover{border-color:var(--accent);color:var(--accent);}
        .pagination .page-item.disabled .page-link{opacity:.4;pointer-events:none;}

        /* ── Stat cards ── */
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:1rem;margin-bottom:2rem;}
        .stat-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem;}
        .stat-card .stat-val{font-size:2rem;font-weight:900;line-height:1;}
        .stat-card .stat-label{font-size:.75rem;color:var(--muted);margin-top:.35rem;}
    </style>
    @stack('styles')
</head>
<body>

<div class="topbar">
    <div class="logo">⚕ Pharma<span>Hub</span> <span style="font-size:.7rem;color:var(--muted);font-weight:400">Laravel</span></div>
    <nav class="nav">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('medicines.index') }}"  class="{{ request()->routeIs('medicines*')  ? 'active' : '' }}">Medicines</a>
        <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories*') ? 'active' : '' }}">Categories</a>
        <a href="{{ route('suppliers.index') }}"  class="{{ request()->routeIs('suppliers*')  ? 'active' : '' }}">Suppliers</a>
        <div class="nav-divider"></div>
        <span class="user-badge">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="btn btn-ghost btn-sm" style="margin-left:.5rem">Logout</button>
        </form>
    </nav>
</div>

<div class="wrapper">

    @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">✗ {{ session('error') }}</div>
    @endif

    @yield('content')
</div>

@stack('scripts')
</body>
</html>
