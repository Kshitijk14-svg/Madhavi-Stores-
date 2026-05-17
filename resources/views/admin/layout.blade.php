@extends('layouts.app')

@section('title', 'Atelier Admin Panel — Madhavi Stores')

@section('content')
<div class="bg-silk py-12">
    <div class="wrap">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-6 mb-8 gap-4">
            <div>
                <span class="eyebrow">Atelier Management</span>
                <h1 class="font-display text-4xl mt-1">Admin Dashboard</h1>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('account') }}" class="btn-secondary !py-2.5 !px-5 text-[10px]">My Profile</a>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-primary !py-2.5 !px-5 text-[10px]">Logout</button>
                </form>
            </div>
        </div>

        {{-- Luxury Dashboard Sub-navigation --}}
        <div class="flex gap-8 border-b border-gray-200/50 pb-4 mb-8 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('admin.dashboard') }}" 
               class="nav-link font-semibold text-xs tracking-wider uppercase {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
               Overview
            </a>
            <a href="{{ route('admin.products.index') }}" 
               class="nav-link font-semibold text-xs tracking-wider uppercase {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
               Products
            </a>
            <a href="{{ route('admin.categories.index') }}" 
               class="nav-link font-semibold text-xs tracking-wider uppercase {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
               Collections
            </a>
            <a href="{{ route('admin.orders.index') }}" 
               class="nav-link font-semibold text-xs tracking-wider uppercase {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
               Orders
            </a>
            <a href="{{ route('admin.users.index') }}" 
               class="nav-link font-semibold text-xs tracking-wider uppercase {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
               Active Carts & Wishlists
            </a>
            <a href="{{ route('admin.coupons.index') }}" 
               class="nav-link font-semibold text-xs tracking-wider uppercase {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
               Coupons
            </a>
            <a href="{{ route('admin.design.index') }}" 
               class="nav-link font-semibold text-xs tracking-wider uppercase {{ request()->routeIs('admin.design.*') ? 'active' : '' }}">
               Design Manager
            </a>
        </div>

        {{-- Success/Error Alerts --}}
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-6 py-4 rounded-none mb-8 text-sm tracking-wide">
                ✦ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-6 py-4 rounded-none mb-8 text-sm tracking-wide">
                ✦ {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-6 py-4 rounded-none mb-8 text-sm tracking-wide">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Content Area --}}
        <div class="admin-content bg-white p-6 md:p-10 border border-gray-100 shadow-sm">
            @yield('admin_content')
        </div>
    </div>
</div>
@endsection
