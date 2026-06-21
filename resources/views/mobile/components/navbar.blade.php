{{-- ══ MOBILE NAV BAR ══ --}}
<header class="w-full px-4 py-3 flex items-center justify-between bg-white sticky top-0 z-50 border-b border-gray-100 shadow-sm">
  <div class="flex-shrink-0">
    <a href="{{ route('home') }}">
      <span style="font-family:'Cormorant Garamond',serif;font-size:1.75rem;font-style:italic;font-weight:300;letter-spacing:0.02em;color:var(--primary);">Madhavi</span>
    </a>
  </div>

  <div class="flex items-center gap-4">
    <a href="{{ route('shop') }}?search=open" aria-label="Search" class="text-primary">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
    </a>
    <button onclick="openSidebar()" aria-label="Menu" class="text-primary flex items-center justify-center w-7 h-7">
      <svg id="mob-hamburger-icon" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
      <svg id="mob-close-icon" class="hidden" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  </div>
</header>

{{-- ══ SIDEBAR BACKDROP (independent, fades) ══ --}}
<div id="mob-sidebar-backdrop"
     onclick="closeSidebar()"
     class="fixed inset-0 z-[70] bg-black/50 opacity-0 pointer-events-none transition-opacity duration-300">
</div>

{{-- ══ SIDEBAR PANEL (independent, slides from right) ══ --}}
<div id="mob-sidebar-panel"
     class="fixed top-0 right-0 bottom-0 z-[71] bg-white flex flex-col shadow-2xl translate-x-full transition-transform duration-300"
     style="width:85vw;max-width:360px;">

  {{-- Panel Header --}}
  <div class="flex items-center justify-between px-6 py-5">
    <span style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-style:italic;font-weight:300;color:var(--primary);">Madhavi Stores</span>
    <button onclick="closeSidebar()" aria-label="Close" class="text-primary p-1 -mr-1">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  </div>

  {{-- Gold decorative rule --}}
  <div style="height:1px;background:var(--secondary);margin:0 24px 4px;opacity:0.6;"></div>

  {{-- Nav Links --}}
  <nav class="flex-1 overflow-y-auto px-7 py-5">
    @php $cur = request()->route()?->getName(); @endphp

    <a href="{{ route('home') }}"
       style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;display:block;padding:10px 0;color:{{ $cur === 'home' ? 'var(--secondary)' : 'var(--primary)' }};">
      Home
    </a>

    <a href="{{ route('shop') }}"
       style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;display:block;padding:10px 0;color:{{ $cur === 'shop' ? 'var(--secondary)' : 'var(--primary)' }};">
      Shop
    </a>

    <a href="{{ route('collections.index') }}"
       style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;display:block;padding:10px 0;color:{{ $cur === 'collections.index' ? 'var(--secondary)' : 'var(--primary)' }};">
      Collections
    </a>

    <a href="{{ route('cart') }}"
       style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;display:flex;align-items:center;gap:10px;padding:10px 0;color:{{ $cur === 'cart' ? 'var(--secondary)' : 'var(--primary)' }};">
      Bag
      <span id="mob-cart-count"
            class="{{ ($cartCount ?? 0) > 0 ? '' : 'hidden' }}"
            style="font-family:system-ui,sans-serif;font-size:10px;font-style:normal;font-weight:700;background:var(--secondary);color:var(--primary);width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        {{ $cartCount ?? 0 }}
      </span>
    </a>

    <a href="{{ route('wishlist') }}"
       style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;display:block;padding:10px 0;color:{{ $cur === 'wishlist' ? 'var(--secondary)' : 'var(--primary)' }};">
      Wishlist
    </a>

    <a href="{{ route('account') }}"
       style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;display:block;padding:10px 0;color:{{ $cur === 'account' ? 'var(--secondary)' : 'var(--primary)' }};">
      My Account
    </a>

    <a href="{{ route('about') }}"
       style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;display:block;padding:10px 0;color:{{ $cur === 'about' ? 'var(--secondary)' : 'var(--primary)' }};">
      About
    </a>
  </nav>

  {{-- Auth Footer --}}
  <div class="px-7 py-6 border-t border-gray-100">
    @auth
      <p style="font-size:11px;color:#aaa;margin-bottom:12px;letter-spacing:0.04em;">
        Signed in as <strong style="color:var(--primary);">{{ Auth::user()->name }}</strong>
      </p>
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit"
                style="width:100%;font-size:10px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;padding:12px;border:1px solid var(--primary);color:var(--primary);background:transparent;">
          Sign Out
        </button>
      </form>
    @else
      <a href="{{ route('login') }}"
         style="display:block;width:100%;text-align:center;font-size:10px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;padding:12px;background:var(--primary);color:#fff;margin-bottom:8px;">
        Sign In
      </a>
      <a href="{{ route('register') }}"
         style="display:block;width:100%;text-align:center;font-size:10px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;padding:12px;border:1px solid var(--primary);color:var(--primary);">
        Create Account
      </a>
    @endauth
  </div>

</div>

<script>
function openSidebar() {
  var b = document.getElementById('mob-sidebar-backdrop');
  var p = document.getElementById('mob-sidebar-panel');
  b.classList.remove('opacity-0', 'pointer-events-none');
  p.classList.remove('translate-x-full');
  document.body.style.overflow = 'hidden';
  document.getElementById('mob-hamburger-icon').classList.add('hidden');
  document.getElementById('mob-close-icon').classList.remove('hidden');
}
function closeSidebar() {
  var b = document.getElementById('mob-sidebar-backdrop');
  var p = document.getElementById('mob-sidebar-panel');
  b.classList.add('opacity-0', 'pointer-events-none');
  p.classList.add('translate-x-full');
  document.body.style.overflow = '';
  document.getElementById('mob-hamburger-icon').classList.remove('hidden');
  document.getElementById('mob-close-icon').classList.add('hidden');
}
document.addEventListener('pjax:success', closeSidebar);
</script>
