<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="robots" content="noindex, nofollow">

  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/Brand_Favicon.svg') }}">
  <meta name="theme-color" content="#181818">

  <title>@yield('admin_title', 'Dashboard') — Madhavi Admin</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    /* Mobile admin shell — independent of the storefront layout. */
    #admin-drawer { transition: transform 0.32s cubic-bezier(0.4,0,0.2,1); }
    #admin-drawer.closed { transform: translateX(100%); }
    #admin-drawer-backdrop { transition: opacity 0.3s ease; }
    body { overflow-x: hidden; }
    body.drawer-open { overflow: hidden; }
    /* Animate hamburger into X when drawer is open */
    #admin-hamburger.open span:nth-child(1) { transform: translateY(6.5px) rotate(45deg); }
    #admin-hamburger.open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
    #admin-hamburger.open span:nth-child(3) { transform: translateY(-6.5px) rotate(-45deg); }
  </style>
</head>
<body class="bg-silk text-primary min-h-screen">

  {{-- ── Sticky Top Bar ─────────────────────────────────── --}}
  <header class="sticky top-0 z-40 bg-white border-b border-gray-200 flex items-center justify-between px-4"
          style="height:56px;padding-top:env(safe-area-inset-top);">
    <button id="admin-hamburger" type="button" onclick="openAdminDrawer()" aria-label="Open menu"
            class="mob-toggle -ml-2" style="padding:8px;">
      <span></span><span></span><span></span>
    </button>
    <div class="flex-1 min-w-0 overflow-hidden flex flex-col items-center leading-none">
      <span class="text-[9px] tracking-[0.25em] uppercase text-muted">Madhavi Admin</span>
      <span class="text-sm font-bold text-primary mt-0.5 truncate max-w-full">@yield('admin_title', 'Dashboard')</span>
    </div>
    <a href="{{ route('home') }}" aria-label="View store"
       class="w-10 h-10 -mr-2 flex items-center justify-center text-muted hover:text-primary">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.592 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
    </a>
  </header>

  @php
    $newOrdersCount = \App\Models\Order::when(
        session('admin_orders_viewed_at'),
        fn($q) => $q->where('created_at', '>', session('admin_orders_viewed_at')),
        fn($q) => $q->where('order_status', 'Pending')
    )->count();
    $adminNavItems = [
      ['route' => 'admin.dashboard',        'pattern' => 'admin.dashboard',   'label' => 'Overview'],
      ['route' => 'admin.products.index',   'pattern' => 'admin.products.*',  'label' => 'Products'],
      ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*','label' => 'Collections'],
      ['route' => 'admin.orders.index',     'pattern' => 'admin.orders.*',    'label' => 'Orders'],
      ['route' => 'admin.users.index',      'pattern' => 'admin.users.*',     'label' => 'Carts & Wishlists'],
      ['route' => 'admin.coupons.index',    'pattern' => 'admin.coupons.*',   'label' => 'Coupons'],
    ];
  @endphp

  {{-- ── Backdrop (blurred, same as storefront) ────────── --}}
  <div id="admin-drawer-backdrop" onclick="closeAdminDrawer()"
       style="position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,0.35);-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);opacity:0;pointer-events:none;transition:opacity 0.3s ease;"></div>

  {{-- ── Drawer Panel (slides from right, storefront style) --}}
  <aside id="admin-drawer" class="closed"
         style="position:fixed;top:0;right:0;bottom:0;z-index:9999;width:85vw;max-width:360px;background:#fff;display:flex;flex-direction:column;box-shadow:-8px 0 40px rgba(0,0,0,0.18);">

    {{-- Header: logo + close --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;">
      <img src="{{ asset('images/brand/Brand_logo.svg') }}" alt="Madhavi Stores" style="height:26px;width:auto;object-fit:contain;">
      <button type="button" onclick="closeAdminDrawer()" aria-label="Close"
              style="color:var(--primary);background:none;border:none;cursor:pointer;padding:4px;display:flex;align-items:center;">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    {{-- Gold decorative rule --}}
    <div style="height:1px;background:var(--secondary);margin:0 24px 4px;opacity:0.6;"></div>

    {{-- Nav links in Cormorant Garamond italic --}}
    <nav style="flex:1;overflow-y:auto;padding:20px 28px;">
      @foreach($adminNavItems as $item)
        <a href="{{ route($item['route']) }}"
           style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;display:flex;align-items:center;gap:10px;padding:10px 0;color:{{ request()->routeIs($item['pattern']) ? 'var(--secondary)' : 'var(--primary)' }};text-decoration:none;">
          {{ $item['label'] }}
          @if($item['route'] === 'admin.orders.index' && $newOrdersCount > 0 && !request()->routeIs('admin.orders.*'))
            <span style="font-family:system-ui,sans-serif;font-size:10px;font-style:normal;font-weight:700;background:#ef4444;color:#fff;min-width:18px;height:18px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;padding:0 3px;flex-shrink:0;">{{ $newOrdersCount > 99 ? '99+' : $newOrdersCount }}</span>
          @endif
        </a>
      @endforeach
      <div style="height:1px;background:#f0f0f0;margin:8px 0 4px;"></div>
      <a href="{{ route('home') }}"
         style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-style:italic;font-weight:300;display:flex;align-items:center;gap:8px;padding:10px 0;color:var(--secondary);text-decoration:none;opacity:0.8;">
        ← Visit Store
      </a>
    </nav>

    {{-- Footer: admin name + sign out --}}
    <div style="padding:24px 28px;border-top:1px solid #f0f0f0;">
      <p style="font-size:11px;color:#aaa;margin-bottom:12px;letter-spacing:0.04em;">
        Admin: <strong style="color:var(--primary);">{{ Auth::user()->name }}</strong>
      </p>
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit"
                style="width:100%;font-size:10px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;padding:12px;border:1px solid var(--primary);color:var(--primary);background:transparent;cursor:pointer;">
          Sign Out
        </button>
      </form>
    </div>
  </aside>

  {{-- ── Page Body ──────────────────────────────────────── --}}
  <main id="admin-main" class="px-4 py-5" style="padding-bottom:calc(env(safe-area-inset-bottom) + 32px);">

    {{-- Flash / validation messages --}}
    @if(session('success'))
      <div class="mb-5 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="mb-5 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">✕ {{ session('error') }}</div>
    @endif
    @if($errors->any())
      <div class="mb-5 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs">
        <ul class="list-disc pl-4 space-y-1">
          @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
      </div>
    @endif

    @yield('admin_content')
  </main>

  <div id="toast-container" class="fixed bottom-6 right-4 left-4 z-[60] flex flex-col gap-2"></div>

  <script>
    function openAdminDrawer() {
      document.getElementById('admin-drawer').classList.remove('closed');
      var b = document.getElementById('admin-drawer-backdrop');
      b.style.opacity = '1';
      b.style.pointerEvents = 'auto';
      document.body.classList.add('drawer-open');
      document.getElementById('admin-hamburger').classList.add('open');
    }
    function closeAdminDrawer() {
      document.getElementById('admin-drawer').classList.add('closed');
      var b = document.getElementById('admin-drawer-backdrop');
      b.style.opacity = '0';
      b.style.pointerEvents = 'none';
      document.body.classList.remove('drawer-open');
      document.getElementById('admin-hamburger').classList.remove('open');
    }

    // Lightweight toast (admin pages reuse this for AJAX feedback).
    function showToast(message, type = 'success') {
      const c = document.getElementById('toast-container');
      if (!c) return;
      const t = document.createElement('div');
      const base = 'flex items-center gap-2 px-4 py-3 border shadow-lg text-xs font-semibold transition-all duration-300 translate-y-3 opacity-0';
      const skin = type === 'error'
        ? 'bg-red-950 text-red-200 border-red-500/30'
        : 'bg-primary text-white border-secondary/30';
      t.className = base + ' ' + skin;
      t.textContent = message;
      c.appendChild(t);
      requestAnimationFrame(() => t.classList.remove('translate-y-3', 'opacity-0'));
      setTimeout(() => { t.classList.add('opacity-0'); setTimeout(() => t.remove(), 300); }, 3000);
    }
  </script>

  @yield('scripts')
  @stack('scripts')
</body>
</html>
