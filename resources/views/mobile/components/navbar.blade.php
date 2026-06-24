{{-- ══ MOBILE NAV BAR ══ --}}
<header style="position:sticky;top:0;z-index:100;background:#fff;border-bottom:1px solid #f0f0f0;box-shadow:0 1px 4px rgba(0,0,0,0.04);">
  <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;">
    <div style="flex-shrink:0;">
      <a href="{{ route('home') }}" style="display:flex;align-items:center;">
        <img src="{{ asset('images/brand/Brand_logo.svg') }}" alt="Madhavi Stores" style="height:30px;width:auto;object-fit:contain;">
      </a>
    </div>
    <div style="display:flex;align-items:center;gap:16px;">
      <a href="{{ route('shop') }}?search=open" aria-label="Search" style="color:var(--primary);display:flex;align-items:center;">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
      </a>
      <button id="mob-menu-btn" onclick="toggleSidebar()" aria-label="Menu" style="color:var(--primary);display:flex;align-items:center;justify-content:center;width:28px;height:28px;background:none;border:none;cursor:pointer;padding:0;">
        <svg id="mob-hamburger-icon" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
        <svg id="mob-close-icon" style="display:none;" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
  </div>
</header>

{{-- ══ SIDEBAR BACKDROP (blurs + dims the page, z-index above everything) ══ --}}
<div id="mob-sidebar-backdrop"
     onclick="closeSidebar()"
     style="position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,0.35);-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);opacity:0;pointer-events:none;transition:opacity 0.3s ease;">
</div>

{{-- ══ SIDEBAR PANEL (slides from right, above backdrop) ══ --}}
<div id="mob-sidebar-panel"
     style="position:fixed;top:0;right:0;bottom:0;z-index:9999;width:85vw;max-width:360px;background:#fff;display:flex;flex-direction:column;box-shadow:-8px 0 40px rgba(0,0,0,0.18);transform:translateX(100%);transition:transform 0.32s cubic-bezier(0.4,0,0.2,1);">

  {{-- Panel Header --}}
  <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;">
    <img src="{{ asset('images/brand/Brand_logo.svg') }}" alt="Madhavi Stores" style="height:26px;width:auto;object-fit:contain;">
    <button onclick="closeSidebar()" aria-label="Close" style="color:var(--primary);background:none;border:none;cursor:pointer;padding:4px;display:flex;align-items:center;">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  </div>

  {{-- Gold decorative rule --}}
  <div style="height:1px;background:var(--secondary);margin:0 24px 4px;opacity:0.6;"></div>

  {{-- Nav Links --}}
  <nav style="flex:1;overflow-y:auto;padding:20px 28px;">
    @php $cur = request()->route()?->getName(); @endphp

    <a href="{{ route('home') }}"
       style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;display:block;padding:10px 0;color:{{ $cur === 'home' ? 'var(--secondary)' : 'var(--primary)' }};text-decoration:none;">
      Home
    </a>

    <a href="{{ route('shop') }}"
       style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;display:block;padding:10px 0;color:{{ $cur === 'shop' ? 'var(--secondary)' : 'var(--primary)' }};text-decoration:none;">
      Shop
    </a>

    <a href="{{ route('collections.index') }}"
       style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;display:block;padding:10px 0;color:{{ $cur === 'collections.index' ? 'var(--secondary)' : 'var(--primary)' }};text-decoration:none;">
      Collections
    </a>

    <a href="{{ route('cart') }}"
       style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;display:flex;align-items:center;gap:10px;padding:10px 0;color:{{ $cur === 'cart' ? 'var(--secondary)' : 'var(--primary)' }};text-decoration:none;">
      Bag
      <span id="mob-cart-count"
            style="{{ ($cartCount ?? 0) > 0 ? '' : 'display:none;' }}font-family:system-ui,sans-serif;font-size:10px;font-style:normal;font-weight:700;background:var(--secondary);color:var(--primary);width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        {{ $cartCount ?? 0 }}
      </span>
    </a>

    <a href="{{ route('wishlist') }}"
       style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;display:block;padding:10px 0;color:{{ $cur === 'wishlist' ? 'var(--secondary)' : 'var(--primary)' }};text-decoration:none;">
      Wishlist
    </a>

    <a href="{{ route('account') }}"
       style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;display:block;padding:10px 0;color:{{ $cur === 'account' ? 'var(--secondary)' : 'var(--primary)' }};text-decoration:none;">
      My Account
    </a>

    <a href="{{ route('about') }}"
       style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;display:block;padding:10px 0;color:{{ $cur === 'about' ? 'var(--secondary)' : 'var(--primary)' }};text-decoration:none;">
      About
    </a>
  </nav>

  {{-- Auth Footer --}}
  <div style="padding:24px 28px;border-top:1px solid #f0f0f0;">
    @auth
      <p style="font-size:11px;color:#aaa;margin-bottom:12px;letter-spacing:0.04em;">
        Signed in as <strong style="color:var(--primary);">{{ Auth::user()->name }}</strong>
      </p>
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit"
                style="width:100%;font-size:10px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;padding:12px;border:1px solid var(--primary);color:var(--primary);background:transparent;cursor:pointer;">
          Sign Out
        </button>
      </form>
    @else
      <a href="{{ route('login') }}"
         style="display:block;width:100%;text-align:center;font-size:10px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;padding:12px;background:var(--primary);color:#fff;margin-bottom:8px;text-decoration:none;">
        Sign In
      </a>
      <a href="{{ route('register') }}"
         style="display:block;width:100%;text-align:center;font-size:10px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;padding:12px;border:1px solid var(--primary);color:var(--primary);text-decoration:none;">
        Create Account
      </a>
    @endauth
  </div>

</div>

<script>
var _sidebarOpen = false;

function openSidebar() {
  _sidebarOpen = true;
  var b = document.getElementById('mob-sidebar-backdrop');
  var p = document.getElementById('mob-sidebar-panel');
  b.style.opacity = '1';
  b.style.pointerEvents = 'auto';
  p.style.transform = 'translateX(0)';
  document.body.style.overflow = 'hidden';
  document.getElementById('mob-hamburger-icon').style.display = 'none';
  document.getElementById('mob-close-icon').style.display = 'block';
}

function closeSidebar() {
  _sidebarOpen = false;
  var b = document.getElementById('mob-sidebar-backdrop');
  var p = document.getElementById('mob-sidebar-panel');
  b.style.opacity = '0';
  b.style.pointerEvents = 'none';
  p.style.transform = 'translateX(100%)';
  document.body.style.overflow = '';
  document.getElementById('mob-hamburger-icon').style.display = 'block';
  document.getElementById('mob-close-icon').style.display = 'none';
}

function toggleSidebar() {
  if (_sidebarOpen) closeSidebar();
  else openSidebar();
}

document.addEventListener('pjax:success', closeSidebar);
</script>
