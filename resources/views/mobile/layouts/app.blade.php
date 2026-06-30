<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @hasSection('meta_description')
    <meta name="description" content="@yield('meta_description')">
  @else
    <meta name="description" content="Madhavi Stores — Premium Indian ethnic wear. Handcrafted luxury.">
  @endif
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Favicon --}}
  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/Brand_Favicon.svg') }}">
  <link rel="apple-touch-icon" href="{{ asset('images/brand/Brand_Favicon.svg') }}">

  {{-- Theme Color for Mobile Browsers --}}
  <meta name="theme-color" content="#f9f8f3">

  <title>@yield('title', 'Madhavi Stores | Quiet Luxury. Indian Heritage.')</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
  
  <script>
    function setViewport() {
      document.documentElement.style.setProperty('--vh', (window.innerHeight * 0.01) + 'px');
      const main = document.getElementById('main');
      let bottomPadding = 0;
      // Measure any fixed bottom bar present on this page (product bar or checkout bar)
      // and add its height as body padding so the footer is never hidden behind it.
      ['mob-product-bar', 'mob-checkout-bar'].forEach(function(id) {
        const bar = document.getElementById(id);
        if (bar && window.getComputedStyle(bar).display !== 'none' && window.getComputedStyle(bar).position === 'fixed') {
          bottomPadding = Math.max(bottomPadding, bar.offsetHeight);
        }
      });
      if (main) main.style.paddingBottom = '';
      document.body.style.paddingBottom = bottomPadding + 'px';
      // Float the WhatsApp button above whichever fixed bar is tallest.
      document.documentElement.style.setProperty('--fab-offset', bottomPadding + 'px');
    }
    window.addEventListener('resize', setViewport);
    window.addEventListener('orientationchange', setViewport);
    document.addEventListener('DOMContentLoaded', setViewport);
  </script>
</head>
<body class="bg-white text-primary">

  @include('mobile.components.navbar', ['cartCount' => $cartCount ?? 0])

  <main id="main">
    @yield('content')
    @yield('scripts')
  </main>

  @include('mobile.components.footer')

  @include('components.whatsapp-float')

  <div id="toast-container" class="fixed bottom-24 right-4 left-4 z-[60] flex flex-col gap-2"></div>

  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

  <script>
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `transform translate-y-4 opacity-0 transition-all duration-300 ease-out flex items-center gap-3 px-4 py-3 border backdrop-blur-md shadow-lg rounded-xl text-xs tracking-wide font-semibold`;
        
        if (type === 'success' || type === 'activated') {
            toast.classList.add('bg-primary/95', 'text-secondary', 'border-secondary/20');
            toast.innerHTML = `<span class="text-secondary font-bold text-sm">✦</span> <span>${message}</span>`;
        } else if (type === 'error' || type === 'deactivated') {
            toast.classList.add('bg-red-950/95', 'text-red-200', 'border-red-500/20');
            toast.innerHTML = `<span class="text-red-400 font-bold text-xs">✕</span> <span>${message}</span>`;
        } else {
            toast.classList.add('bg-white/95', 'text-primary', 'border-primary/10');
            toast.innerHTML = `<span class="text-secondary font-bold text-sm">✦</span> <span>${message}</span>`;
        }

        container.appendChild(toast);
        setTimeout(() => toast.classList.remove('translate-y-4', 'opacity-0'), 50);
        setTimeout(() => {
            toast.classList.add('translate-y-[-10px]', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Share a product via the native share sheet, falling back to copying the link.
    function shareProduct(url, title) {
        const absUrl = new URL(url, window.location.origin).href;
        if (navigator.share) {
            navigator.share({ title: title || document.title, url: absUrl }).catch(() => {});
            return;
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(absUrl)
                .then(() => showToast('Product link copied!', 'success'))
                .catch(() => window.open('https://wa.me/?text=' + encodeURIComponent((title ? title + ' — ' : '') + absUrl), '_blank'));
            return;
        }
        window.open('https://wa.me/?text=' + encodeURIComponent((title ? title + ' — ' : '') + absUrl), '_blank');
    }
    </script>
    @include('components.search-suggest-script')
    <script>
    // Stripped down PJAX for mobile speed.
    // Manual scroll restoration so back/forward returns to the prior position.
    if ('scrollRestoration' in history) history.scrollRestoration = 'manual';

    let currentPjaxUrl = window.location.href;
    const pjaxScrollPositions = {};
    const pjaxPageCache = {};
    const pjaxTitles = {};
    history.replaceState({ url: currentPjaxUrl }, document.title, currentPjaxUrl);

    // Keep the active page's scroll position fresh — popstate fires *after* the
    // URL changes, so we cannot read it reliably at that point.
    let pjaxScrollTimer = null;
    window.addEventListener('scroll', function() {
        if (pjaxScrollTimer) return;
        pjaxScrollTimer = setTimeout(() => {
            pjaxScrollPositions[currentPjaxUrl] = window.scrollY;
            pjaxScrollTimer = null;
        }, 100);
    }, { passive: true });

    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link) return;
        if (link.href && link.origin === window.location.origin && !link.hash && !link.getAttribute('target') && !link.classList.contains('no-pjax')) {
            const url = link.href;
            if (url.includes('/logout') || url.includes('/invoice')) return;
            e.preventDefault();
            navigateToPage(url);
        }
    });

    // Swap #main content and re-run any inline page scripts (Swiper, etc.).
    function applyPjaxContent(html) {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        document.title = doc.title;
        const newMainContent = doc.getElementById('main');
        const currentMain = document.getElementById('main');
        if (!newMainContent || !currentMain) return false;
        currentMain.innerHTML = newMainContent.innerHTML;
        currentMain.querySelectorAll('script').forEach(oldScript => {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
        return true;
    }

    function navigateToPage(url, pushState = true) {
        let loadingBar = document.getElementById('pjax-loading-bar');
        if (!loadingBar) {
            loadingBar = document.createElement('div');
            loadingBar.id = 'pjax-loading-bar';
            loadingBar.className = 'fixed top-0 left-0 h-[3px] bg-secondary z-[9999] transition-all duration-300 ease-out';
            loadingBar.style.width = '0%';
            document.body.appendChild(loadingBar);
        }

        loadingBar.style.opacity = '1';
        loadingBar.style.width = '30%';

        // Remember the page we are leaving (scroll + markup + title) for instant back nav.
        pjaxScrollPositions[currentPjaxUrl] = window.scrollY;
        pjaxTitles[currentPjaxUrl] = document.title;
        const leavingMain = document.getElementById('main');
        if (leavingMain) pjaxPageCache[currentPjaxUrl] = leavingMain.innerHTML;

        gsap.to('#main', {
            opacity: 0, y: 8, duration: 0.2,
            onComplete: () => {
                loadingBar.style.width = '70%';
                fetch(url)
                    .then(response => { if (!response.ok) throw new Error(); return response.text(); })
                    .then(html => {
                        loadingBar.style.width = '100%';
                        if (!applyPjaxContent(html)) { window.location.href = url; return; }
                        pjaxPageCache[url] = document.getElementById('main').innerHTML;
                        pjaxTitles[url] = document.title;

                        if (pushState) history.pushState({ url: url }, document.title, url);
                        currentPjaxUrl = url;

                        window.scrollTo({ top: 0, behavior: 'instant' });
                        // clearProps:'transform' prevents GSAP from leaving an inline
                        // transform on #main, which would break position:fixed children
                        // (the product bar, sort/filter drawers).
                        gsap.fromTo('#main', { opacity: 0, y: -8 }, { opacity: 1, y: 0, duration: 0.3, clearProps: 'transform', onComplete: setViewport });
                        document.dispatchEvent(new Event('pjax:success'));

                        setTimeout(() => {
                            loadingBar.style.opacity = '0';
                            setTimeout(() => { loadingBar.style.width = '0%'; }, 300);
                        }, 200);
                    })
                    .catch(err => { window.location.href = url; });
            }
        });
    }

    // Back / forward: restore from cache instantly (no fade) and recover scroll.
    function restorePjaxPage(url) {
        const targetScroll = pjaxScrollPositions[url] || 0;
        const cached = pjaxPageCache[url];
        currentPjaxUrl = url;

        const finish = () => {
            gsap.set('#main', { clearProps: 'transform', opacity: 1, y: 0 });
            setViewport();
            document.dispatchEvent(new Event('pjax:success'));
            window.scrollTo({ top: targetScroll, behavior: 'instant' });
            // Re-apply after layout/images settle.
            requestAnimationFrame(() => window.scrollTo({ top: targetScroll, behavior: 'instant' }));
        };

        if (cached) {
            // The cache holds innerHTML (not a full HTML page), so we apply it
            // directly rather than going through applyPjaxContent (which parses a
            // full document and would fail to find #main in a fragment).
            const currentMain = document.getElementById('main');
            if (!currentMain) { window.location.href = url; return; }
            currentMain.innerHTML = cached;
            if (pjaxTitles[url]) document.title = pjaxTitles[url];
            currentMain.querySelectorAll('script').forEach(oldScript => {
                const newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                newScript.textContent = oldScript.textContent;
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });
            finish();
        } else {
            fetch(url)
                .then(r => { if (!r.ok) throw new Error(); return r.text(); })
                .then(html => {
                    if (!applyPjaxContent(html)) { window.location.href = url; return; }
                    pjaxPageCache[url] = document.getElementById('main').innerHTML;
                    pjaxTitles[url] = document.title;
                    finish();
                })
                .catch(() => { window.location.href = url; });
        }
    }

    window.addEventListener('popstate', function(e) {
        const url = (e.state && e.state.url) ? e.state.url : window.location.href;
        restorePjaxPage(url);
    });

    function bindMobileCartListeners() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // AJAX add-to-cart forms on mobile
        document.querySelectorAll('form[action*="/cart/add"]:not([data-mob-bound])').forEach(form => {
            form.setAttribute('data-mob-bound', '1');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const body = new FormData(form);
                fetch(form.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }, body })
                    .then(r => r.json())
                    .then(data => {
                        showToast(data.message || (data.success ? 'Added to bag!' : 'Could not add item.'), data.success ? 'success' : 'error');
                        if (data.success) {
                            const b = document.getElementById('mob-cart-count');
                            if (b) { b.innerText = data.cart_count || data.cartCount || 0; b.classList.remove('hidden'); }
                        }
                    }).catch(() => showToast('Connection error.', 'error'));
            });
        });

        // AJAX wishlist toggle forms on mobile
        document.querySelectorAll('form[action*="/wishlist/toggle"]:not([data-mob-bound])').forEach(form => {
            form.setAttribute('data-mob-bound', '1');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                fetch(form.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken } })
                    .then(r => r.json())
                    .then(data => {
                        showToast(data.message || 'Wishlist updated.', data.success ? 'success' : 'error');
                    }).catch(() => {});
            });
        });
    }

    document.addEventListener('DOMContentLoaded', bindMobileCartListeners);
    document.addEventListener('pjax:success', bindMobileCartListeners);
  </script>
</body>
</html>
