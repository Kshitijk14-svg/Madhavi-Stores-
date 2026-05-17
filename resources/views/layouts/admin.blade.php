<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Madhavi Stores</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=Manrope:wght@300;400;500;600&display=swap" rel="stylesheet">

    {{-- Use static CSS to avoid Vite/Node dependency errors --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <style>
        /* Admin specific layout tweaks since it uses a lot of grid/flex utilities */
        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .min-h-screen { min-height: 100vh; }
        .w-64 { width: 16rem; }
        .fixed { position: fixed; }
        .inset-y-0 { top: 0; bottom: 0; }
        .flex-grow { flex-grow: 1; }
        .ml-64 { margin-left: 16rem; }
        .p-12 { padding: 3rem; }
        .mb-12 { margin-bottom: 3rem; }
        .grid { display: grid; }
        .grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
        @media (min-width: 768px) { .md\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .lg\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .gap-8 { gap: 2rem; }
        .bg-white { background-color: #ffffff; }
        .p-8 { padding: 2rem; }
        .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .space-x-4 > * + * { margin-left: 1rem; }
        .space-y-2 > * + * { margin-top: 0.5rem; }
        .rounded-sm { border-radius: 0.125rem; }
        .text-white { color: #ffffff; }
        .text-xs { font-size: 0.75rem; }
        .text-sm { font-size: 0.875rem; }
        .tracking-widest { letter-spacing: 0.1em; }
        .uppercase { text-transform: uppercase; }
        .font-bold { font-weight: 700; }
        .font-medium { font-weight: 500; }
        .font-light { font-weight: 300; }
        .block { display: block; }
        .mb-4 { margin-bottom: 1rem; }
        .text-3xl { font-size: 1.875rem; }
        .border-b { border-bottom-width: 1px; }
        .w-full { width: 100%; }
        .text-left { text-align: left; }
        .pb-4 { padding-bottom: 1rem; }
        .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
        .last\:border-0:last-child { border-bottom-width: 0px; }
    </style>
</head>
<body class="bg-silk font-body text-primary antialiased">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-primary text-white flex flex-col fixed inset-y-0" style="z-index: 50;">
            <div class="p-8">
                <a href="{{ route('home') }}">
                    <span style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-style:italic;color:#fff;">Madhavi</span>
                </a>
            </div>
            
            <nav class="flex-grow px-6 py-8 space-y-2">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-4 px-4 py-3 rounded-sm transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-secondary' : 'hover:bg-white/5' }}" style="text-decoration:none; color:inherit;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="height:1.25rem; width:1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    <span class="text-sm font-medium">Dashboard</span>
                </a>
                <a href="{{ route('admin.products.index') }}" class="flex items-center space-x-4 px-4 py-3 rounded-sm transition-colors {{ request()->routeIs('admin.products.*') ? 'bg-white/10 text-secondary' : 'hover:bg-white/5' }}" style="text-decoration:none; color:inherit;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="height:1.25rem; width:1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    <span class="text-sm font-medium">Products</span>
                </a>
                <a href="#" class="flex items-center space-x-4 px-4 py-3 rounded-sm hover:bg-white/5 transition-colors" style="text-decoration:none; color:inherit;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="height:1.25rem; width:1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    <span class="text-sm font-medium">Collections</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" style="margin-top: 2rem;">
                    @csrf
                    <button type="submit" class="flex items-center space-x-4 px-4 py-3 rounded-sm hover:bg-white/5 transition-colors text-white/60 hover:text-white w-full text-left" style="background:none; border:none; cursor:pointer; font-family:inherit;">
                        <svg xmlns="http://www.w3.org/2000/svg" style="height:1.25rem; width:1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        <span class="text-sm font-medium">Logout</span>
                    </button>
                </form>
            </nav>

            <div class="p-8 border-t border-white/10">
                <a href="{{ route('home') }}" class="flex items-center space-x-4 text-white/60 hover:text-white transition-colors text-xs tracking-widest uppercase" style="text-decoration:none;">
                    <span>Back to Shop</span>
                    <svg xmlns="http://www.w3.org/2000/svg" style="height:1rem; width:1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-grow ml-64 p-12">
            <header class="flex justify-between items-center mb-12">
                <div>
                    <h2 class="text-xs tracking-widest uppercase font-bold text-muted mb-1">Admin Panel</h2>
                    <h1 class="font-display text-4xl font-light">@yield('title', 'Dashboard')</h1>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="w-10 h-10 rounded-full bg-secondary flex items-center justify-center text-white font-bold">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                </div>
            </header>

            <section>
                @yield('content')
            </section>
        </main>
    </div>

</body>
</html>
