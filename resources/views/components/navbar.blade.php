{{-- ══ ANNOUNCEMENT STRIP ══ --}}
<div id="strip">
  ✦ &nbsp; Free shipping on orders above ₹5,000 &nbsp; · &nbsp; Easy 30-day returns &nbsp; ✦
  <button id="strip-close" onclick="this.parentElement.remove()">
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
  </button>
</div>

{{-- ══ MAIN NAV ══ --}}
<header id="site-nav" class="w-full px-6 lg:px-16">
  <div class="flex items-center justify-between w-full h-full">

      {{-- Logo (Left) --}}
      <div class="flex-shrink-0 flex items-center">
        <a href="{{ route('home') }}">
          <img src="{{ asset('images/brand/logo.png') }}" alt="Madhavi Stores" style="height:44px;width:auto;object-fit:contain;"
               onerror="this.outerHTML='<span style=\'font-family:Cormorant Garamond,serif;font-size:2rem;font-style:italic;font-weight:300;letter-spacing:0.02em;color:var(--primary);\'>Madhavi</span>'">
        </a>
      </div>

      {{-- Center Links --}}
      <nav class="hidden lg:flex flex-1 items-center gap-10 justify-center" id="nav-center-links">
        <a href="{{ route('home') }}"       class="nav-link {{ request()->routeIs('home')       ? 'active' : '' }}">Home</a>
        <a href="{{ route('shop') }}"       class="nav-link {{ request()->routeIs('shop')       ? 'active' : '' }}">Shop</a>
        <a href="{{ route('collections.index') }}" class="nav-link {{ request()->routeIs('collections.index') ? 'active' : '' }}">Collections</a>
        <a href="{{ route('about') }}"      class="nav-link {{ request()->routeIs('about')      ? 'active' : '' }}">Our Story</a>
      </nav>

      {{-- Right actions --}}
      <div class="flex-shrink-0 flex items-center justify-end gap-2 sm:gap-4 lg:gap-6">
        <button id="open-search" class="search-pill flex items-center gap-2 !bg-transparent hover:!bg-gray-50 !px-3 !py-2 lg:!px-4 lg:!py-2 rounded-full transition-colors group">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" class="text-primary group-hover:text-secondary transition-colors" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
          <span class="hidden xl:inline text-[10px] font-bold tracking-[0.2em] uppercase text-primary group-hover:text-secondary transition-colors">Search</span>
        </button>

        <a href="{{ route('wishlist') }}" class="icon-btn hidden-mob hover:text-secondary transition-colors" aria-label="Wishlist">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
        </a>

        <a href="{{ route('account') }}" class="icon-btn hidden-mob hover:text-secondary transition-colors" aria-label="Account">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
        </a>

        <a href="{{ route('cart') }}" class="icon-btn hidden-mob hover:text-secondary transition-colors relative" aria-label="Cart" id="navbar-cart-link">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
          <span id="navbar-cart-count" class="absolute -top-1 -right-1 bg-secondary text-primary text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center {{ ($cartCount ?? 0) > 0 ? '' : 'hidden' }}">{{ $cartCount ?? 0 }}</span>
        </a>

        <button id="mob-open" class="mob-toggle ml-2" aria-label="Menu">
          <span></span><span></span><span></span>
        </button>
      </div>
  </div>
</header>

{{-- ══ SEARCH PANEL ══ --}}
<div class="drawer-backdrop" id="search-back"></div>
<div class="search-box" id="search-box">
  <div class="wrap" style="max-width:680px;margin:0 auto;">
    <form action="{{ route('shop') }}" method="GET" style="position:relative;">
      <svg style="position:absolute;left:16px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:var(--muted);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
      <input id="search-input" type="text" name="search" value="{{ request('search') }}" placeholder="Search sarees, kurtas, lehengas…"
             style="width:100%;padding:14px 48px;background:#f5f5f5;border:1px solid #e5e5e5;border-radius:999px;font-size:14px;outline:none;font-family:inherit;">
      <button id="search-close" type="button" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </form>
    <div style="margin-top:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
      <span class="eyebrow">Trending:</span>
      @foreach(['Organza Saree','Chanderi Kurta','Anarkali Set','Block Print','Lehenga'] as $t)
        <a href="{{ route('shop') }}?search={{ urlencode($t) }}" class="trend-tag">{{ $t }}</a>
      @endforeach
    </div>
  </div>
</div>

{{-- ══ MOBILE MENU ══ --}}
<div class="drawer-backdrop" id="mob-back"></div>
<div class="mob-panel" id="mob-panel">
  <div style="display:flex;align-items:center;justify-content:space-between;padding:20px;border-bottom:1px solid #f0f0f0;">
    <span style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-style:italic;font-weight:300;">Madhavi</span>
    <button id="mob-close" style="background:none;border:none;cursor:pointer;">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  </div>
  <nav style="flex:1;overflow-y:auto;padding:24px;">
    @foreach([['Home',route('home')],['Shop',route('shop')],['Collections',route('collections.index')],['Our Story',route('about')],['Wishlist',route('wishlist')]] as [$l,$h])
    <a href="{{ $h }}" style="display:flex;align-items:center;justify-content:space-between;padding:16px 0;border-bottom:1px solid #f8f8f8;font-size:14px;font-weight:600;letter-spacing:0.05em;color:var(--primary);transition:color 0.2s;"
       onmouseover="this.style.color='var(--secondary)'" onmouseout="this.style.color='var(--primary)'">
      {{ $l }}
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
    </a>
    @endforeach
  </nav>
  <div style="padding:24px;border-top:1px solid #f0f0f0;display:flex;gap:12px;">
    @auth
      <a href="{{ route('account') }}" class="btn-primary" style="flex:1;text-align:center;">Account</a>
    @else
      <a href="{{ route('login') }}" class="btn-primary" style="flex:1;text-align:center;">Sign In</a>
    @endauth
    <a href="{{ route('cart') }}"    class="btn-secondary" style="flex:1;text-align:center;">Cart (<span id="mob-cart-count">{{ $cartCount ?? 0 }}</span>)</a>
  </div>
</div>

{{-- ══ BOTTOM ACTION BAR (MOBILE ONLY) ══ --}}
<div class="{{ request()->routeIs('product.show') ? 'hidden' : 'lg:hidden' }} fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 z-50 flex items-center justify-around px-2 py-3 pb-[calc(12px+env(safe-area-inset-bottom))]" id="mob-bottom-bar" style="padding-bottom:env(safe-area-inset-bottom);">
  <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 text-primary hover:text-secondary transition-colors {{ request()->routeIs('home') ? 'text-secondary' : '' }}">
    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.592 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
    <span class="text-[9px] font-bold tracking-widest uppercase">Home</span>
  </a>
  <a href="{{ route('shop') }}" class="flex flex-col items-center gap-1 text-primary hover:text-secondary transition-colors {{ request()->routeIs('shop') ? 'text-secondary' : '' }}">
    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
    <span class="text-[9px] font-bold tracking-widest uppercase">Shop</span>
  </a>
  <a href="{{ route('cart') }}" class="flex flex-col items-center gap-1 text-primary hover:text-secondary transition-colors relative {{ request()->routeIs('cart') ? 'text-secondary' : '' }}">
    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
    <span id="mob-bottom-cart-count" class="absolute -top-1 -right-2 bg-secondary text-primary text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center {{ ($cartCount ?? 0) > 0 ? '' : 'hidden' }}">{{ $cartCount ?? 0 }}</span>
    <span class="text-[9px] font-bold tracking-widest uppercase">Bag</span>
  </a>
  <a href="{{ route('account') }}" class="flex flex-col items-center gap-1 text-primary hover:text-secondary transition-colors {{ request()->routeIs('account') ? 'text-secondary' : '' }}">
    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
    <span class="text-[9px] font-bold tracking-widest uppercase">Profile</span>
  </a>
</div>

<script>
(function(){

  function toggleDrawer(back, panel, open){
    back.classList.toggle('open', open);
    panel.classList.toggle('open', open);
  }

  // Search
  var sb = document.getElementById('search-back'), sbox = document.getElementById('search-box');
  document.getElementById('open-search')?.addEventListener('click', function(){ toggleDrawer(sb,sbox,true); setTimeout(function(){ document.getElementById('search-input')?.focus(); },350); });
  document.getElementById('search-close')?.addEventListener('click', function(){ toggleDrawer(sb,sbox,false); });
  sb?.addEventListener('click', function(){ toggleDrawer(sb,sbox,false); });

  // Mobile menu
  var mb = document.getElementById('mob-back'), mp = document.getElementById('mob-panel');
  document.getElementById('mob-open')?.addEventListener('click', function(){ toggleDrawer(mb,mp,true); });
  document.getElementById('mob-close')?.addEventListener('click', function(){ toggleDrawer(mb,mp,false); });
  mb?.addEventListener('click', function(){ toggleDrawer(mb,mp,false); });
})();
</script>
