<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Madhavi Stores — Premium Indian ethnic wear. Handcrafted luxury.">
  <title>@yield('title', 'Madhavi Stores | Quiet Luxury. Indian Heritage.')</title>

  {{-- Static CSS — no Vite needed --}}
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#181818',
            secondary: '#b8986e',
            accent: '#d44d44',
            silk: '#f0ebe3',
            muted: '#888888',
            border: '#e5e5e5',
            background: '#faf8f5',
          },
          fontFamily: {
            display: ['"Cormorant Garamond"', 'serif'],
            sans: ['Manrope', 'sans-serif'],
          }
        }
      }
    }
  </script>

  {{-- Swiper CSS --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
</head>
<body>

  @include('components.navbar', ['cartCount' => $cartCount ?? 0])

  <main id="main">
    @yield('content')
  </main>

  @include('components.footer')

  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

  @yield('scripts')
</body>
</html>
