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

  {{-- Theme Color for Mobile Browsers --}}
  <meta name="theme-color" content="#ffffff">

  <title>@yield('title', 'Madhavi Stores | Quiet Luxury. Indian Heritage.')</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
  
  <script>
    function setViewport() {
      document.documentElement.style.setProperty('--vh', (window.innerHeight * 0.01) + 'px');
      const main = document.getElementById('main');
      const bottomBar = document.getElementById('mob-bottom-bar');
      const productBar = document.getElementById('mob-product-bar');
      
      let bottomPadding = 0;
      if (productBar && window.getComputedStyle(productBar).display !== 'none' && window.getComputedStyle(productBar).position === 'fixed') {
        bottomPadding = productBar.offsetHeight;
      } else if (bottomBar && window.getComputedStyle(bottomBar).display !== 'none') {
        bottomPadding = bottomBar.offsetHeight;
      }
      
      if(main) main.style.paddingBottom = bottomPadding + 'px';
    }
    window.addEventListener('resize', setViewport);
    window.addEventListener('orientationchange', setViewport);
    document.addEventListener('DOMContentLoaded', setViewport);
  </script>
</head>
<body class="bg-white text-primary pb-16">

  @include('mobile.components.navbar', ['cartCount' => $cartCount ?? 0])

  <main id="main">
    @yield('content')
    @yield('scripts')
  </main>

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

    // Stripped down PJAX for mobile speed
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

        gsap.to('#main', {
            opacity: 0, y: 8, duration: 0.2,
            onComplete: () => {
                loadingBar.style.width = '70%';
                fetch(url)
                    .then(response => { if (!response.ok) throw new Error(); return response.text(); })
                    .then(html => {
                        loadingBar.style.width = '100%';
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        document.title = doc.title;
                        
                        const newMainContent = doc.getElementById('main');
                        const currentMain = document.getElementById('main');
                        if (newMainContent && currentMain) {
                            currentMain.innerHTML = newMainContent.innerHTML;
                            const scripts = currentMain.querySelectorAll('script');
                            scripts.forEach(oldScript => {
                                const newScript = document.createElement('script');
                                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                                newScript.textContent = oldScript.textContent;
                                oldScript.parentNode.replaceChild(newScript, oldScript);
                            });
                        } else {
                            window.location.href = url; return;
                        }
                        
                        if (pushState) history.pushState({ url: url }, doc.title, url);
                        
                        // update mobile nav state
                        document.querySelectorAll('#mob-bottom-bar a').forEach(a => {
                            if(url.includes(new URL(a.href).pathname) && new URL(a.href).pathname !== '/') a.classList.add('text-secondary');
                            else if(new URL(a.href).pathname === '/' && url === window.location.origin + '/') a.classList.add('text-secondary');
                            else a.classList.remove('text-secondary');
                        });

                        window.scrollTo({ top: 0, behavior: 'instant' });
                        gsap.fromTo('#main', { opacity: 0, y: -8 }, { opacity: 1, y: 0, duration: 0.3, onComplete: setViewport });
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

    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.url) navigateToPage(e.state.url, false);
        else window.location.reload();
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
                            const b = document.getElementById('mob-bottom-cart-count');
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
