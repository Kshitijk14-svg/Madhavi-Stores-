{{-- ══ MOBILE NAV BAR ══ --}}
<header class="w-full px-4 py-3 flex items-center justify-between bg-white sticky top-0 z-50 border-b border-gray-100 shadow-sm">
  {{-- Mobile Logo --}}
  <div class="flex-shrink-0">
    <a href="{{ route('home') }}">
      <span style="font-family:'Cormorant Garamond',serif;font-size:1.75rem;font-style:italic;font-weight:300;letter-spacing:0.02em;color:var(--primary);">Madhavi</span>
    </a>
  </div>

  {{-- Mobile Right Actions --}}
  <div class="flex items-center gap-4">
    <a href="{{ route('shop') }}?search=open" aria-label="Search" class="text-primary hover:text-secondary">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
    </a>
  </div>
</header>

{{-- ══ BOTTOM ACTION BAR (MOBILE) ══ --}}
<div class="{{ request()->routeIs('product.show') ? 'hidden' : 'flex' }} fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-gray-100 z-50 items-center justify-around px-2 py-3" id="mob-bottom-bar" style="padding-bottom:env(safe-area-inset-bottom);">
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
    <span id="mob-cart-count" class="absolute -top-1 -right-2 bg-secondary text-primary text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center {{ ($cartCount ?? 0) > 0 ? '' : 'hidden' }}">{{ $cartCount ?? 0 }}</span>
    <span class="text-[9px] font-bold tracking-widest uppercase">Bag</span>
  </a>
  <a href="{{ route('account') }}" class="flex flex-col items-center gap-1 text-primary hover:text-secondary transition-colors {{ request()->routeIs('account') ? 'text-secondary' : '' }}">
    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
    <span class="text-[9px] font-bold tracking-widest uppercase">Profile</span>
  </a>
</div>
