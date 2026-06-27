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
    #admin-drawer { transition: transform .25s ease; }
    #admin-drawer.closed { transform: translateX(-100%); }
    #admin-drawer-backdrop { transition: opacity .25s ease; }
    .madmin-nav-link.active { background: var(--primary); color: #fff; }
    body.drawer-open { overflow: hidden; }
  </style>
</head>
<body class="bg-silk text-primary min-h-screen">

  {{-- ── Sticky Top Bar ─────────────────────────────────── --}}
  <header class="sticky top-0 z-40 bg-white border-b border-gray-200 flex items-center justify-between px-4"
          style="height:56px;padding-top:env(safe-area-inset-top);">
    <button type="button" onclick="openAdminDrawer()" aria-label="Open menu"
            class="w-10 h-10 -ml-2 flex items-center justify-center text-primary text-xl">☰</button>
    <div class="flex flex-col items-center leading-none">
      <span class="text-[9px] tracking-[0.25em] uppercase text-muted">Madhavi Admin</span>
      <span class="text-sm font-bold text-primary mt-0.5">@yield('admin_title', 'Dashboard')</span>
    </div>
    <form action="{{ route('logout') }}" method="POST">
      @csrf
      <button type="submit" aria-label="Log out"
              class="w-10 h-10 -mr-2 flex items-center justify-center text-muted hover:text-primary text-lg">⎋</button>
    </form>
  </header>

  {{-- ── Drawer + Backdrop ──────────────────────────────── --}}
  <div id="admin-drawer-backdrop" onclick="closeAdminDrawer()"
       class="fixed inset-0 z-40 bg-black/40 opacity-0 pointer-events-none"></div>

  <aside id="admin-drawer" class="closed fixed top-0 left-0 z-50 h-full w-72 max-w-[80%] bg-white border-r border-gray-200 flex flex-col"
         style="padding-top:env(safe-area-inset-top);">
    <div class="flex items-center justify-between px-5 border-b border-gray-100" style="height:56px;">
      <span class="font-display text-xl text-primary">Atelier</span>
      <button type="button" onclick="closeAdminDrawer()" aria-label="Close menu"
              class="w-9 h-9 -mr-1 flex items-center justify-center text-muted text-lg">✕</button>
    </div>

    @php
      $newOrdersCount = \App\Models\Order::when(
          session('admin_orders_viewed_at'),
          fn($q) => $q->where('created_at', '>', session('admin_orders_viewed_at')),
          fn($q) => $q->where('order_status', 'Pending')
      )->count();
      $navItems = [
        ['route' => 'admin.dashboard',        'pattern' => 'admin.dashboard',   'label' => 'Overview'],
        ['route' => 'admin.products.index',   'pattern' => 'admin.products.*',  'label' => 'Products'],
        ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*','label' => 'Collections'],
        ['route' => 'admin.orders.index',     'pattern' => 'admin.orders.*',    'label' => 'Orders'],
        ['route' => 'admin.users.index',      'pattern' => 'admin.users.*',     'label' => 'Active Carts & Wishlists'],
        ['route' => 'admin.coupons.index',    'pattern' => 'admin.coupons.*',   'label' => 'Coupons'],
      ];
    @endphp

    <nav class="flex-1 overflow-y-auto py-3">
      @foreach($navItems as $item)
        <a href="{{ route($item['route']) }}"
           class="madmin-nav-link flex items-center justify-between px-5 py-3.5 text-xs font-bold tracking-wider uppercase text-primary border-l-2 {{ request()->routeIs($item['pattern']) ? 'active border-secondary' : 'border-transparent' }}">
          <span>{{ $item['label'] }}</span>
          @if($item['route'] === 'admin.orders.index' && $newOrdersCount > 0 && !request()->routeIs('admin.orders.*'))
            <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[9px] font-bold bg-rose-500 text-white rounded-full">{{ $newOrdersCount > 99 ? '99+' : $newOrdersCount }}</span>
          @endif
        </a>
      @endforeach
    </nav>

    <div class="border-t border-gray-100 p-4 space-y-2">
      <a href="{{ route('account') }}"
         class="block w-full text-center text-[10px] font-bold tracking-widest uppercase text-primary border border-gray-200 py-3">
        My Profile
      </a>
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit"
                class="block w-full text-center text-[10px] font-bold tracking-widest uppercase text-white bg-primary py-3">
          Logout
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
      const b = document.getElementById('admin-drawer-backdrop');
      b.classList.remove('opacity-0', 'pointer-events-none');
      document.body.classList.add('drawer-open');
    }
    function closeAdminDrawer() {
      document.getElementById('admin-drawer').classList.add('closed');
      const b = document.getElementById('admin-drawer-backdrop');
      b.classList.add('opacity-0', 'pointer-events-none');
      document.body.classList.remove('drawer-open');
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
