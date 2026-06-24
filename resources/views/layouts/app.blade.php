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
  @hasSection('meta_keywords')
    <meta name="keywords" content="@yield('meta_keywords')">
  @endif
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @yield('meta')

  {{-- Favicon --}}
  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/Brand_Favicon.svg') }}">
  <link rel="apple-touch-icon" href="{{ asset('images/brand/Brand_Favicon.svg') }}">

  {{-- Canonical Link --}}
  <link rel="canonical" href="{{ url()->current() }}">

  {{-- OpenGraph Tags --}}
  <meta property="og:site_name" content="Madhavi Stores">
  <meta property="og:title" content="@yield('title', 'Madhavi Stores | Quiet Luxury. Indian Heritage.')">
  <meta property="og:description" content="@yield('meta_description', 'Madhavi Stores — Premium Indian ethnic wear. Handcrafted luxury.')">
  <meta property="og:type" content="@yield('og_type', 'website')">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:image" content="@yield('og_image', asset('images/brand/logo.png'))">

  {{-- Twitter Card Tags --}}
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="@yield('title', 'Madhavi Stores | Quiet Luxury. Indian Heritage.')">
  <meta name="twitter:description" content="@yield('meta_description', 'Madhavi Stores — Premium Indian ethnic wear. Handcrafted luxury.')">
  <meta name="twitter:image" content="@yield('og_image', asset('images/brand/logo.png'))">

  <title>@yield('title', 'Madhavi Stores | Quiet Luxury. Indian Heritage.')</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- Swiper CSS --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
  
  {{-- Chart.js (UMD Build for Global Scope) --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>

  <script>
    // Fix viewport height on mobile browsers
    function setViewport() {
      document.documentElement.style.setProperty('--vh', (window.innerHeight * 0.01) + 'px');
      
      // Calculate dynamic bottom padding to prevent content overlapping with mobile bottom bars
      const main = document.getElementById('main');
      if (main && window.innerWidth < 1024) {
        const bottomBar = document.getElementById('mob-bottom-bar');
        const productBar = document.getElementById('mob-product-bar');
        
        let bottomPadding = 0;
        if (productBar && window.getComputedStyle(productBar).display !== 'none' && window.getComputedStyle(productBar).position === 'fixed') {
          bottomPadding = productBar.offsetHeight;
        } else if (bottomBar && window.getComputedStyle(bottomBar).display !== 'none') {
          bottomPadding = bottomBar.offsetHeight;
        }
        
        main.style.paddingBottom = bottomPadding + 'px';
      } else if (main) {
        main.style.paddingBottom = '0px';
      }
    }
    window.addEventListener('resize', setViewport);
    window.addEventListener('orientationchange', setViewport);
    document.addEventListener('DOMContentLoaded', setViewport);
  </script>
</head>
<body>

  @include('components.navbar', ['cartCount' => $cartCount ?? 0])

  <main id="main" class="pb-24 lg:pb-0">
    @yield('content')
    @yield('scripts')
  </main>

  @include('components.footer')

  @include('components.whatsapp-float')

  <!-- Premium Glassmorphism Toast Container -->
  <div id="toast-container" class="fixed bottom-8 right-8 z-50 flex flex-col gap-3 max-w-sm w-full"></div>

  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

  <script>
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `transform translate-y-4 opacity-0 transition-all duration-500 ease-out flex items-center gap-3 px-6 py-4 border backdrop-blur-md shadow-lg rounded-none text-xs tracking-wider uppercase font-semibold`;
        
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

        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-y-4', 'opacity-0');
        }, 50);

        // Auto dismiss
        setTimeout(() => {
            toast.classList.add('translate-y-[-10px]', 'opacity-0');
            setTimeout(() => {
                toast.remove();
            }, 500);
        }, 4000);
    }

    // --- PJAX Navigation Engine ---
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link) return;
        
        // Only intercept same-origin, non-hash, standard links
        if (link.href && link.origin === window.location.origin && !link.hash && !link.getAttribute('target') && !link.classList.contains('no-pjax')) {
            const url = link.href;
            
            // Bypass special actions
            if (url.includes('/logout') || url.includes('/invoice') || url.includes('/admin/orders/invoice')) {
                return;
            }
            
            e.preventDefault();
            navigateToPage(url);
        }
    });

    function navigateToPage(url, pushState = true) {
        // Create luxury top loading indicator bar if it doesn't exist
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

        // Smoothly exit content via GSAP
        gsap.to('#main', {
            opacity: 0,
            y: 8,
            duration: 0.25,
            ease: 'power2.out',
            onComplete: () => {
                loadingBar.style.width = '70%';
                
                fetch(url)
                    .then(response => {
                        if (!response.ok) throw new Error('Network error');
                        return response.text();
                    })
                    .then(html => {
                        loadingBar.style.width = '100%';
                        
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        // Update title
                        document.title = doc.title;
                        
                        // Swap main container content
                        const newMainContent = doc.getElementById('main');
                        const currentMain = document.getElementById('main');
                        if (newMainContent && currentMain) {
                            currentMain.innerHTML = newMainContent.innerHTML;
                            
                            // Extract and run script tags inside the swapped container
                            const scripts = currentMain.querySelectorAll('script');
                            scripts.forEach(oldScript => {
                                const newScript = document.createElement('script');
                                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                                newScript.textContent = oldScript.textContent;
                                oldScript.parentNode.replaceChild(newScript, oldScript);
                            });
                        } else {
                            // Fallback for non-PJAX target pages (e.g. auth layout pages)
                            window.location.href = url;
                            return;
                        }
                        
                        if (pushState) {
                            history.pushState({ url: url }, doc.title, url);
                        }
                        
                        // Re-initialize active navigation link styling
                        updateActiveNavbarLinks(url);
                        
                        // Re-bind all dynamic elements and form intercepts
                        bindDynamicContentListeners();

                        // Scroll back to top smoothly
                        window.scrollTo({ top: 0, behavior: 'instant' });
                        
                        // Fade dynamic content back in
                        gsap.fromTo('#main', 
                            { opacity: 0, y: -8 },
                            { opacity: 1, y: 0, duration: 0.35, ease: 'power2.out', onComplete: () => { if(typeof setViewport === 'function') setViewport(); } }
                        );
                        
                        // Dismiss loader
                        setTimeout(() => {
                            loadingBar.style.opacity = '0';
                            setTimeout(() => { loadingBar.style.width = '0%'; }, 300);
                        }, 200);
                    })
                    .catch(error => {
                        console.error('PJAX navigation fallback:', error);
                        window.location.href = url;
                    });
            }
        });
    }

    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.url) {
            navigateToPage(e.state.url, false);
        } else {
            window.location.reload();
        }
    });

    function updateActiveNavbarLinks(currentUrl) {
        // Desktop / Mobile Nav Bar active styles
        const navLinks = document.querySelectorAll('#nav-center-links a, .nav-link, .mob-panel nav a');
        navLinks.forEach(link => {
            const isHome = link.href === window.location.origin + '/';
            if (isHome) {
                if (currentUrl === window.location.origin || currentUrl === window.location.origin + '/') {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            } else if (currentUrl.startsWith(link.href)) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
        
        // Admin Sidebar menu highlights
        const adminLinks = document.querySelectorAll('.wrap a');
        adminLinks.forEach(link => {
            if (currentUrl.startsWith(link.href)) {
                link.classList.add('active', 'border-b-2', 'border-primary', 'text-primary');
            }
        });
    }

    // --- Dynamic AJAX Form Intercepts ---
    function bindDynamicContentListeners() {
        // 1. Intercept Design Manager tabs/forms
        const designForms = document.querySelectorAll('form[action*="/design/update"]');
        designForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (form.id === 'layout-form' && typeof updateLayoutJson === 'function') {
                    updateLayoutJson();
                }
                if (form.id === 'lookbook-form' && typeof updateLookbookLayoutJson === 'function') {
                    updateLookbookLayoutJson();
                }
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerHTML : 'Save';
                
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `<span class="inline-block animate-spin mr-2">✦</span> Saving...`;
                    submitBtn.style.opacity = '0.7';
                }
                
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message || 'Settings updated successfully!', 'success');
                    } else {
                        showToast(data.message || 'Failed to update settings.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('An unexpected error occurred.', 'error');
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        submitBtn.style.opacity = '1';
                    }
                });
            });
        });

        // 2. Intercept Coupon Toggle Forms
        const couponToggleForms = document.querySelectorAll('.coupon-toggle-form');
        couponToggleForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;
                
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        
                        // Update button styling dynamically without reload
                        if (submitBtn) {
                            const isCurrentlyActive = submitBtn.innerText.trim().toLowerCase() === 'deactivate';
                            if (isCurrentlyActive) {
                                submitBtn.innerText = 'Activate';
                                submitBtn.className = 'text-[9px] px-3 py-1.5 font-bold uppercase tracking-wide border transition-colors border-emerald-300 text-emerald-700 hover:bg-emerald-50';
                            } else {
                                submitBtn.innerText = 'Deactivate';
                                submitBtn.className = 'text-[9px] px-3 py-1.5 font-bold uppercase tracking-wide border transition-colors border-gray-300 text-gray-600 hover:bg-gray-50';
                            }
                            
                            // Dynamically update the status badge on the coupon card
                            const parentCard = form.closest('[id^="coupon-card-"]');
                            if (parentCard) {
                                const badge = parentCard.querySelector('span.tracking-wide');
                                if (badge) {
                                    if (isCurrentlyActive) {
                                        badge.innerText = 'Inactive';
                                        badge.className = 'text-[9px] px-2 py-0.5 font-bold uppercase tracking-wide bg-gray-100 text-gray-600';
                                    } else {
                                        badge.innerText = 'Active';
                                        badge.className = 'text-[9px] px-2 py-0.5 font-bold uppercase tracking-wide bg-emerald-50 text-emerald-800';
                                    }
                                }
                            }
                        }
                    } else {
                        showToast(data.message || 'Error toggling coupon status.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('Communication error.', 'error');
                })
                .finally(() => {
                    if (submitBtn) submitBtn.disabled = false;
                });
            });
        });

        // 3. Intercept Order status and payment updates
        const orderUpdateForms = document.querySelectorAll('.order-update-form');
        orderUpdateForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerText : 'Save';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerText = 'Saving...';
                }
                
                const formData = new FormData(form);
                const orderId = form.action.split('/').pop();
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        
                        // Close modal dynamically
                        if (typeof toggleOrderModal === 'function') {
                            toggleOrderModal(orderId, false);
                        }
                        
                        // Dynamically reload list or refresh content safely
                        navigateToPage(window.location.href, false);
                    } else {
                        showToast(data.message || 'Error updating order status.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('Communication error occurred.', 'error');
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerText = originalText;
                    }
                });
            });
        });

        // 4. Hook Add to Bag forms globally
        const addToBagForms = document.querySelectorAll('form[action*="/cart/add"]');
        addToBagForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerHTML : 'Add to Bag';
                
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `Adding...`;
                }
                
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    // Check if it's a redirect to login (usually 401 or redirected to HTML login page)
                    if(response.redirected && response.url.includes('/login')) {
                        window.location.href = response.url;
                        throw new Error('Not logged in');
                    }
                    if(!response.ok && response.status === 401) {
                        window.location.href = '/login';
                        throw new Error('Not logged in');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.success) {
                        showToast(data.message || 'Item added to bag!', 'success');
                        
                        // Dynamically update navbar count badges without full reload
                        const count = data.cartCount || data.cart_count || 0;
                        const mainBadge = document.getElementById('navbar-cart-count');
                        const mobBadge = document.getElementById('mob-cart-count');
                        const mobBottomBadge = document.getElementById('mob-bottom-cart-count');

                        if (mainBadge) {
                            mainBadge.innerText = count;
                            if (count > 0) {
                                mainBadge.classList.remove('hidden');
                            } else {
                                mainBadge.classList.add('hidden');
                            }
                        }
                        if (mobBadge) {
                            mobBadge.innerText = count;
                        }
                        if (mobBottomBadge) {
                            mobBottomBadge.innerText = count;
                            if (count > 0) mobBottomBadge.classList.remove('hidden');
                            else mobBottomBadge.classList.add('hidden');
                        }
                    } else if(data) {
                        showToast(data.message || 'Failed to add item to bag.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    if(err.message !== 'Not logged in') {
                        showToast('Please log in first.', 'error');
                        window.location.href = '/login';
                    }
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
            });
        });

        // 5. Hook Wishlist forms globally
        const wishlistForms = document.querySelectorAll('form[action*="/wishlist/toggle"]');
        wishlistForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"], button.pcard-wish');
                if (submitBtn) submitBtn.disabled = true;
                
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if(response.redirected && response.url.includes('/login')) {
                        window.location.href = response.url;
                        throw new Error('Not logged in');
                    }
                    if(!response.ok && response.status === 401) {
                        window.location.href = '/login';
                        throw new Error('Not logged in');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.success) {
                        showToast(data.message, 'success');
                        
                        if (submitBtn) {
                            const isAdded = data.added;
                            
                            // Handle product show page button
                            if(submitBtn.innerText.includes('Wishlist')) {
                                submitBtn.innerHTML = isAdded ? `Remove from Wishlist` : `✦ Add to Wishlist`;
                            } 
                            // Handle card wishlist button
                            else {
                                const svg = submitBtn.querySelector('svg');
                                if(svg) {
                                    svg.setAttribute('fill', isAdded ? 'currentColor' : 'none');
                                    if(isAdded) {
                                        submitBtn.classList.add('text-red-500');
                                        submitBtn.classList.remove('text-primary');
                                    } else {
                                        submitBtn.classList.remove('text-red-500');
                                        submitBtn.classList.add('text-primary');
                                    }
                                }
                            }
                        }
                    } else if(data) {
                        showToast(data.message || 'Error processing wishlist request.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    if(err.message !== 'Not logged in') {
                        showToast('Please log in first.', 'error');
                        window.location.href = '/login';
                    }
                })
                .finally(() => {
                    if (submitBtn) submitBtn.disabled = false;
                });
            });
        });

        // 6. Hook Apply Coupon / Remove Coupon forms on cart page
        const applyCouponForm = document.getElementById('apply-coupon-form');
        if (applyCouponForm) {
            applyCouponForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = applyCouponForm.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                const formData = new FormData(applyCouponForm);

                fetch(applyCouponForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        navigateToPage(window.location.href, false);
                    } else {
                        showToast(data.message || 'Error applying coupon.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('Connection error.', 'error');
                })
                .finally(() => {
                    if (submitBtn) submitBtn.disabled = false;
                });
            });
        }

        const removeCouponForm = document.getElementById('remove-coupon-form');
        if (removeCouponForm) {
            removeCouponForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = removeCouponForm.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                fetch(removeCouponForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        navigateToPage(window.location.href, false);
                    } else {
                        showToast(data.message || 'Error removing coupon.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('Connection error.', 'error');
                })
                .finally(() => {
                    if (submitBtn) submitBtn.disabled = false;
                });
            });
        }

        // 7. Check if salesChart element exists and initialize the Chart
        const salesCanvas = document.getElementById('salesChart');
        if (salesCanvas) {
            loadSalesChart('month');
        }

        // 8. Cart Page Quantity & Remove Buttons
        document.querySelectorAll('.cart-qty-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const itemId = this.getAttribute('data-id');
                const action = this.getAttribute('data-action');
                const qtySpan = document.getElementById('item-qty-' + itemId);
                if (!qtySpan) return;

                fetch("/cart/update", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ cart_item_id: itemId, action: action })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        
                        const navbarBadge = document.getElementById('navbar-cart-count');
                        if (navbarBadge) {
                            navbarBadge.innerText = data.cart_count;
                            if (data.cart_count > 0) navbarBadge.classList.remove('hidden');
                            else navbarBadge.classList.add('hidden');
                        }
                        const mobBottomBadge = document.getElementById('mob-bottom-cart-count');
                        if (mobBottomBadge) {
                            mobBottomBadge.innerText = data.cart_count;
                            if (data.cart_count > 0) mobBottomBadge.classList.remove('hidden');
                            else mobBottomBadge.classList.add('hidden');
                        }

                        if (data.item_quantity === 0) {
                            const row = document.getElementById('cart-item-' + itemId);
                            if (row) {
                                gsap.to(row, {
                                    opacity: 0, y: -20, height: 0, paddingBottom: 0, marginBottom: 0, duration: 0.35, ease: 'power2.inOut',
                                    onComplete: () => {
                                        row.remove();
                                        if (data.cart_count === 0) navigateToPage(window.location.href, false);
                                    }
                                });
                            }
                        } else {
                            qtySpan.innerText = data.item_quantity;
                            const priceSpan = document.getElementById('item-price-' + itemId);
                            if (priceSpan) priceSpan.innerText = data.item_total;
                        }

                        document.getElementById('cart-subtotal').innerText = data.subtotal;
                        document.getElementById('cart-total').innerText = data.total;
                        const discountVal = document.getElementById('cart-discount');
                        if (discountVal) discountVal.innerText = '- ' + data.discount;
                    } else {
                        showToast(data.message || 'Error updating quantity.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('Connection error.', 'error');
                });
            });
        });

        document.querySelectorAll('.cart-remove-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!confirm('Remove this product from your bag?')) return;
                const itemId = this.getAttribute('data-id');

                fetch("/cart/remove/" + itemId, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        
                        const navbarBadge = document.getElementById('navbar-cart-count');
                        if (navbarBadge) {
                            navbarBadge.innerText = data.cart_count;
                            if (data.cart_count > 0) navbarBadge.classList.remove('hidden');
                            else navbarBadge.classList.add('hidden');
                        }
                        const mobBottomBadgeR = document.getElementById('mob-bottom-cart-count');
                        if (mobBottomBadgeR) {
                            mobBottomBadgeR.innerText = data.cart_count;
                            if (data.cart_count > 0) mobBottomBadgeR.classList.remove('hidden');
                            else mobBottomBadgeR.classList.add('hidden');
                        }

                        const row = document.getElementById('cart-item-' + itemId);
                        if (row) {
                            gsap.to(row, {
                                opacity: 0, y: -20, height: 0, paddingBottom: 0, marginBottom: 0, duration: 0.35, ease: 'power2.inOut',
                                onComplete: () => {
                                    row.remove();
                                    if (data.cart_count === 0) navigateToPage(window.location.href, false);
                                }
                            });
                        }

                        document.getElementById('cart-subtotal').innerText = data.subtotal;
                        document.getElementById('cart-total').innerText = data.total;
                        const discountVal = document.getElementById('cart-discount');
                        if (discountVal) discountVal.innerText = '- ' + data.discount;
                    } else {
                        showToast(data.message || 'Error removing item.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('Connection error.', 'error');
                });
            });
        });
    }

    // --- User Role Asynchronous AJAX Selector ---
    function submitRoleAjax(selectElement) {
        const form = selectElement.form;
        const url = form.action;
        const roleValue = selectElement.value;
        
        selectElement.disabled = true;
        selectElement.style.opacity = '0.5';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ role: roleValue })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                
                const parentCard = selectElement.closest('.border-gray-200');
                if (parentCard) {
                    const badge = parentCard.querySelector('span.tracking-wider');
                    if (badge) {
                        badge.innerText = roleValue === 'superadmin' ? 'Super Admin' : (roleValue === 'admin' ? 'Admin' : 'Customer');
                        badge.className = 'text-[9px] px-2 py-0.5 font-bold uppercase tracking-wider ' + 
                            (roleValue === 'superadmin' ? 'bg-violet-50 text-violet-700 border border-violet-100' : 
                            (roleValue === 'admin' ? 'bg-slate-100 text-primary border border-gray-200' : 'bg-gray-50 text-muted border border-gray-200'));
                    }
                }
            } else {
                showToast(data.message || 'Error updating role.', 'error');
                window.location.reload(); 
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Error communicating with server.', 'error');
        })
        .finally(() => {
            selectElement.disabled = false;
            selectElement.style.opacity = '1';
        });
    }

    let salesChartInstance = null;
    function loadSalesChart(range = 'month') {
        // Dynamic self-healing fallback for PJAX: If Chart isn't loaded in memory yet, fetch it instantly!
        if (typeof Chart === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js';
            script.onload = () => loadSalesChart(range); // Recall the function once library is ready
            document.head.appendChild(script);
            return;
        }

        // Show active tab styling on filters
        document.querySelectorAll('.chart-filter').forEach(btn => {
            btn.classList.remove('bg-primary', 'text-white');
            btn.classList.add('bg-slate-100', 'text-primary');
            if (btn.getAttribute('data-range') === range) {
                btn.classList.add('bg-primary', 'text-white');
                btn.classList.remove('bg-slate-100', 'text-primary');
            }
        });

        const canvas = document.getElementById('salesChart');
        if (!canvas) return;

        let fetchUrl = `{{ url('/admin/sales-chart-data') }}?range=${range}`;
        if (range === 'custom') {
            const start = document.getElementById('chart-start-date').value;
            const end = document.getElementById('chart-end-date').value;
            if (start && end) {
                fetchUrl += `&start=${start}&end=${end}`;
            } else {
                showToast('Please select both a start and end date to view custom analytics.', 'error');
                return;
            }
        }

        // Fetch dynamic JSON data
        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Update stats on chart header
            const totalSalesSpan = document.getElementById('chart-total-sales');
            if (totalSalesSpan) {
                totalSalesSpan.innerText = data.total;
            }

            const ctx = canvas.getContext('2d');
            
            // Destroy existing chart instance to avoid registry collision
            if (salesChartInstance) {
                salesChartInstance.destroy();
            }

            // Create gold gradient
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(235, 184, 41, 0.25)'); // Luxurious Gold theme
            gradient.addColorStop(1, 'rgba(255, 255, 255, 0.0)');

            salesChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Sales Revenue',
                        data: data.values,
                        borderColor: '#ebb829', // Gold color
                        borderWidth: 2,
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#ebb829',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#181818',
                        pointHoverBorderColor: '#ebb829',
                        pointHoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#181818',
                            titleColor: '#ebb829',
                            bodyColor: '#fff',
                            cornerRadius: 0,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return '₹' + Number(context.parsed.y).toLocaleString('en-IN');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#888888',
                                font: {
                                    size: 10,
                                    family: 'Manrope, sans-serif'
                                }
                            }
                        },
                        y: {
                            min: 0,
                            suggestedMax: 1000,
                            grid: {
                                color: '#f0ebe3',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#888888',
                                font: {
                                    size: 10,
                                    family: 'Manrope, sans-serif'
                                },
                                callback: function(value) {
                                    return '₹' + Number(value).toLocaleString('en-IN');
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(err => {
            console.error('Error fetching sales chart data:', err);
        });
    }

    // Trigger on initial page load
    document.addEventListener('DOMContentLoaded', () => {
        bindDynamicContentListeners();

        // Global Enter-to-Save interceptor for all inputs/textareas inside forms
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const target = e.target;
                if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') {
                    // If it is a textarea, Enter submits, and Shift+Enter inserts standard newline
                    if (target.tagName === 'TEXTAREA' && e.shiftKey) {
                        return; // Let standard newline happen
                    }
                    
                    e.preventDefault();
                    
                    // Find the parent form
                    const form = target.closest('form');
                    if (form) {
                        // Find the submit button
                        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                        if (submitBtn) {
                            submitBtn.click();
                        } else {
                            // Fallback if no submit button exists
                            const submitEvent = new Event('submit', { cancelable: true, bubbles: true });
                            form.dispatchEvent(submitEvent);
                            if (!submitEvent.defaultPrevented) {
                                form.submit();
                            }
                        }
                    }
                }
            }
        });
        
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif
        @if(session('error'))
            showToast("{{ session('error') }}", 'error');
        @endif
    });
  </script>

</body>
</html>
