{{-- ══ MOBILE NAV BAR ══ --}}
<header class="w-full px-4 py-3 flex items-center justify-between bg-white sticky top-0 z-50 border-b border-gray-100 shadow-sm">
  <div class="flex-shrink-0">
    <a href="{{ route('home') }}">
      <span style="font-family:'Cormorant Garamond',serif;font-size:1.75rem;font-style:italic;font-weight:300;letter-spacing:0.02em;color:var(--primary);">Madhavi</span>
    </a>
  </div>

  <div class="flex items-center gap-4">
    <a href="{{ route('shop') }}?search=open" aria-label="Search" class="text-primary hover:text-secondary">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
    </a>
    <button onclick="openSidebar()" aria-label="Menu" class="text-primary">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
    </button>
  </div>
</header>

{{-- ══ SLIDE-IN SIDEBAR ══ --}}
<div id="mob-sidebar" class="fixed inset-0 z-[70] translate-x-full transition-transform duration-300" style="will-change:transform;">
  {{-- Backdrop --}}
  <div class="absolute inset-0 bg-black/40" onclick="closeSidebar()"></div>

  {{-- Panel --}}
  <div class="absolute right-0 top-0 bottom-0 w-72 bg-white flex flex-col shadow-xl">

    {{-- Panel Header --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
      <span style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;font-style:italic;font-weight:300;color:var(--primary);">Madhavi</span>
      <button onclick="closeSidebar()" class="text-primary p-1" aria-label="Close">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    {{-- Nav Links --}}
    <nav class="flex-1 overflow-y-auto px-5 py-3">
      @php $cur = request()->route()?->getName(); @endphp

      <a href="{{ route('home') }}" class="flex items-center gap-3 py-3.5 border-b border-gray-100 text-xs font-bold tracking-wider uppercase {{ $cur === 'home' ? 'text-secondary' : 'text-primary' }}">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.592 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
        Home
      </a>

      <a href="{{ route('shop') }}" class="flex items-center gap-3 py-3.5 border-b border-gray-100 text-xs font-bold tracking-wider uppercase {{ $cur === 'shop' ? 'text-secondary' : 'text-primary' }}">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
        Shop
      </a>

      <a href="{{ route('collections.index') }}" class="flex items-center gap-3 py-3.5 border-b border-gray-100 text-xs font-bold tracking-wider uppercase {{ $cur === 'collections.index' ? 'text-secondary' : 'text-primary' }}">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 01-1.125-1.125v-3.75zM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 01-1.125-1.125v-8.25zM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 01-1.125-1.125v-2.25z"/></svg>
        Collections
      </a>

      <a href="{{ route('cart') }}" class="flex items-center gap-3 py-3.5 border-b border-gray-100 text-xs font-bold tracking-wider uppercase {{ $cur === 'cart' ? 'text-secondary' : 'text-primary' }}">
        <div class="relative">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
          <span id="mob-cart-count" class="absolute -top-1.5 -right-2 bg-secondary text-primary text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center {{ ($cartCount ?? 0) > 0 ? '' : 'hidden' }}">{{ $cartCount ?? 0 }}</span>
        </div>
        Bag
      </a>

      <a href="{{ route('wishlist') }}" class="flex items-center gap-3 py-3.5 border-b border-gray-100 text-xs font-bold tracking-wider uppercase {{ $cur === 'wishlist' ? 'text-secondary' : 'text-primary' }}">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
        Wishlist
      </a>

      <a href="{{ route('account') }}" class="flex items-center gap-3 py-3.5 border-b border-gray-100 text-xs font-bold tracking-wider uppercase {{ $cur === 'account' ? 'text-secondary' : 'text-primary' }}">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
        My Account
      </a>

      <a href="{{ route('about') }}" class="flex items-center gap-3 py-3.5 border-b border-gray-100 text-xs font-bold tracking-wider uppercase {{ $cur === 'about' ? 'text-secondary' : 'text-primary' }}">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
        About
      </a>
    </nav>

    {{-- Auth Footer --}}
    <div class="px-5 py-5 border-t border-gray-100">
      @auth
        <p class="text-[11px] text-gray-400 mb-3 tracking-wide">Signed in as <strong class="text-primary">{{ Auth::user()->name }}</strong></p>
        <form action="{{ route('logout') }}" method="POST">
          @csrf
          <button type="submit" class="w-full text-xs font-bold tracking-widest uppercase py-3 border border-primary text-primary hover:bg-primary hover:text-white transition-colors">
            Sign Out
          </button>
        </form>
      @else
        <a href="{{ route('login') }}" class="block w-full text-center text-xs font-bold tracking-widest uppercase py-3 bg-primary text-white mb-2">
          Sign In
        </a>
        <a href="{{ route('register') }}" class="block w-full text-center text-xs font-bold tracking-widest uppercase py-3 border border-primary text-primary">
          Create Account
        </a>
      @endauth
    </div>

  </div>
</div>

<script>
function openSidebar() {
  var s = document.getElementById('mob-sidebar');
  s.classList.remove('translate-x-full');
  document.body.style.overflow = 'hidden';
}
function closeSidebar() {
  var s = document.getElementById('mob-sidebar');
  s.classList.add('translate-x-full');
  document.body.style.overflow = '';
}
document.addEventListener('pjax:success', closeSidebar);
</script>
