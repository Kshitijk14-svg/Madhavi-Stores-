document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Announcement bar rotation + dismiss
    const initAnnouncementBar = () => {
        const bar = document.getElementById('announcement-bar');
        if (!bar) return;

        if (sessionStorage.getItem('announcementDismissed') === 'true') {
            bar.style.display = 'none';
            document.documentElement.style.setProperty('--announcement-height', '0px');
            return;
        }

        const messages = bar.querySelectorAll('.announcement-msg');
        let currentIndex = 0;

        if (messages.length > 1) {
            setInterval(() => {
                messages[currentIndex].classList.remove('opacity-100');
                messages[currentIndex].classList.add('opacity-0', 'absolute');
                
                currentIndex = (currentIndex + 1) % messages.length;
                
                messages[currentIndex].classList.remove('opacity-0', 'absolute');
                messages[currentIndex].classList.add('opacity-100');
            }, 3500);
        }

        const closeBtn = document.getElementById('close-announcement');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                bar.style.transition = 'max-height 0.3s ease, opacity 0.3s ease';
                bar.style.maxHeight = '0';
                bar.style.opacity = '0';
                bar.style.overflow = 'hidden';
                setTimeout(() => bar.style.display = 'none', 300);
                sessionStorage.setItem('announcementDismissed', 'true');
                document.documentElement.style.setProperty('--announcement-height', '0px');
            });
        }
    };

    // 2. Navbar scroll state change
    const initNavbar = () => {
        const navbar = document.getElementById('main-navbar');
        if (!navbar) return;

        const onScroll = () => {
            if (window.scrollY > 60) {
                navbar.classList.remove('bg-transparent', 'text-white');
                navbar.classList.add('bg-white/95', 'backdrop-blur-md', 'shadow-sm', 'text-primary');
            } else {
                navbar.classList.add('bg-transparent', 'text-white');
                navbar.classList.remove('bg-white/95', 'backdrop-blur-md', 'shadow-sm', 'text-primary');
            }
        };

        window.addEventListener('scroll', onScroll);
        onScroll(); 
    };

    // 3. Mobile hamburger menu toggle
    const initMobileMenu = () => {
        const toggleBtn = document.getElementById('mobile-menu-toggle');
        const closeBtn = document.getElementById('mobile-menu-close');
        const mobileMenu = document.getElementById('mobile-menu');

        if (toggleBtn && mobileMenu && closeBtn) {
            const openMenu = () => {
                mobileMenu.classList.remove('translate-x-full');
                document.body.style.overflow = 'hidden';
                toggleBtn.setAttribute('aria-expanded', 'true');
            };
            
            const closeMenuAction = () => {
                mobileMenu.classList.add('translate-x-full');
                document.body.style.overflow = '';
                toggleBtn.setAttribute('aria-expanded', 'false');
            };

            toggleBtn.addEventListener('click', openMenu);
            closeBtn.addEventListener('click', closeMenuAction);
        }
    };

    // 4 & 5. GSAP Animations
    const initGSAP = () => {
        if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReducedMotion) return;

        gsap.registerPlugin(ScrollTrigger);

        // Hero stagger
        gsap.from(".hero-label",   { y: 20, opacity: 0, duration: 0.8, delay: 0.3 });
        gsap.from(".hero-heading", { y: 40, opacity: 0, duration: 1,   delay: 0.5 });
        gsap.from(".hero-sub",     { y: 20, opacity: 0, duration: 0.8, delay: 0.8 });
        gsap.from(".hero-ctas",    { y: 20, opacity: 0, duration: 0.8, delay: 1.0 });

        // ScrollTrigger reveal-up
        document.querySelectorAll('.reveal-up').forEach(el => {
            gsap.from(el, {
                scrollTrigger: { trigger: el, start: "top 85%", toggleActions: "play none none none" },
                y: 50, opacity: 0, duration: 0.9, ease: "power3.out"
            });
        });
    };

    // 6 & 7. Product slider drag-to-scroll & arrow navigation
    const initSlider = () => {
        const slider = document.getElementById('product-slider');
        const prevBtn = document.getElementById('slider-prev');
        const nextBtn = document.getElementById('slider-next');
        
        if (!slider) return;

        let isDown = false;
        let startX;
        let scrollLeft;

        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            slider.classList.add('cursor-grabbing');
            slider.classList.remove('cursor-grab');
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
        });
        
        slider.addEventListener('mouseleave', () => {
            isDown = false;
            slider.classList.remove('cursor-grabbing');
            slider.classList.add('cursor-grab');
        });
        
        slider.addEventListener('mouseup', () => {
            isDown = false;
            slider.classList.remove('cursor-grabbing');
            slider.classList.add('cursor-grab');
        });
        
        slider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 2; 
            slider.scrollLeft = scrollLeft - walk;
        });

        // Arrow navigation
        const scrollAmount = 300;
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                slider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            });
        }
    };

    // 8. Search overlay open/close
    const initSearch = () => {
        const searchBtn = document.getElementById('search-btn');
        const searchOverlay = document.getElementById('search-overlay');
        const closeSearchBtn = document.getElementById('close-search');
        const searchInput = document.getElementById('search-input');

        if (searchBtn && searchOverlay && closeSearchBtn) {
            const openSearch = () => {
                searchOverlay.classList.remove('hidden');
                setTimeout(() => searchOverlay.classList.remove('opacity-0'), 10);
                setTimeout(() => searchInput?.focus(), 100);
                document.body.style.overflow = 'hidden';
            };

            const closeSearch = () => {
                searchOverlay.classList.add('opacity-0');
                setTimeout(() => searchOverlay.classList.add('hidden'), 300);
                document.body.style.overflow = '';
            };

            searchBtn.addEventListener('click', (e) => {
                e.preventDefault();
                openSearch();
            });
            
            closeSearchBtn.addEventListener('click', closeSearch);

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !searchOverlay.classList.contains('hidden')) {
                    closeSearch();
                }
            });
        }
    };

    // 9. Lazy image intersection observer fallback
    const initLazyLoad = () => {
        if ('loading' in HTMLImageElement.prototype) {
            return;
        }
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if(img.dataset.src) {
                        img.src = img.dataset.src;
                    }
                    observer.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    };

    initAnnouncementBar();
    initNavbar();
    initMobileMenu();
    initGSAP();
    initSlider();
    initSearch();
    initLazyLoad();
});
