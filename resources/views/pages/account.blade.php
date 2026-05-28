@extends('layouts.app')
@section('title', 'My Account | Madhavi Stores')

@section('content')

<section style="padding: 64px 0; background: var(--white); min-height: 80vh;">
  <div class="wrap" style="max-width: 1000px; margin: 0 auto;">
    
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 40px; border-bottom: 1px solid var(--border); padding-bottom: 24px;">
      <div>
        <h1 style="font-family:'Cormorant Garamond',serif; font-size: 3rem; font-weight: 300; font-style: italic; color: var(--primary);">My Atelier</h1>
        <p style="color: var(--muted); margin-top: 8px;">Welcome back, {{ $user->name }}</p>
      </div>
      <div style="display: flex; gap: 16px;">
        @if($user->is_admin)
          <a href="{{ route('admin.dashboard') }}" class="btn-secondary" style="background: var(--primary); color: var(--white);">Admin Dashboard</a>
        @endif
        <form action="{{ route('logout') }}" method="POST">
          @csrf
          <button type="submit" class="btn-secondary">Sign Out</button>
        </form>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 48px;">
      
      {{-- Sidebar Navigation --}}
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <button class="account-tab active" onclick="switchTab('orders')" id="tab-orders">Order History</button>
        <button class="account-tab" onclick="switchTab('wishlist')" id="tab-wishlist">Wishlist</button>
        <button class="account-tab" onclick="switchTab('profile')" id="tab-profile">Profile Settings</button>
      </div>

      {{-- Tab Content --}}
      <div style="background: var(--silk); padding: 40px; border-radius: 8px;">
        
        {{-- ORDERS TAB --}}
        <div id="content-orders" class="account-content">
          <h2 style="font-family:'Cormorant Garamond',serif; font-size: 2rem; font-weight: 300; margin-bottom: 24px;">Order History</h2>
          @if(empty($orders))
            <p style="color: var(--muted);">You haven't placed any orders yet.</p>
            <a href="{{ route('shop') }}" class="btn-primary" style="display: inline-block; margin-top: 16px;">Explore Collections</a>
          @else
            {{-- Order list logic would go here --}}
          @endif
        </div>

        {{-- WISHLIST TAB --}}
        <div id="content-wishlist" class="account-content" style="display: none;">
          <h2 style="font-family:'Cormorant Garamond',serif; font-size: 2rem; font-weight: 300; margin-bottom: 24px;">My Wishlist</h2>
          @if(empty($wishlist))
            <p style="color: var(--muted);">Your wishlist is empty. Save your favorite pieces here.</p>
            <a href="{{ route('lookbook') }}" class="btn-primary" style="display: inline-block; margin-top: 16px;">View Lookbook</a>
          @else
            {{-- Wishlist grid logic would go here --}}
          @endif
        </div>

        {{-- PROFILE TAB --}}
        <div id="content-profile" class="account-content" style="display: none;">
          <h2 style="font-family:'Cormorant Garamond',serif; font-size: 2rem; font-weight: 300; margin-bottom: 24px;">Profile Settings</h2>
          <form action="#" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            @csrf
            <div>
              <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--muted);">Full Name</label>
              <input type="text" name="name" value="{{ $user->name }}" style="width: 100%; padding: 12px 16px; border: 1px solid var(--border); background: var(--white); margin-top: 8px;">
            </div>
            <div>
              <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--muted);">Email Address</label>
              <input type="email" name="email" value="{{ $user->email }}" disabled style="width: 100%; padding: 12px 16px; border: 1px solid var(--border); background: #f9f9f9; color: var(--muted); margin-top: 8px;">
            </div>
            <div>
              <button type="submit" class="btn-primary">Save Changes</button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</section>

<style>
  .account-tab {
    background: none;
    border: none;
    text-align: left;
    padding: 16px 24px;
    font-size: 14px;
    font-weight: 500;
    color: var(--muted);
    cursor: pointer;
    transition: all 0.3s ease;
    border-left: 2px solid transparent;
  }
  .account-tab:hover {
    color: var(--primary);
    background: var(--silk);
  }
  .account-tab.active {
    color: var(--primary);
    border-left: 2px solid var(--secondary);
    font-weight: 600;
  }
</style>

<script>
  function switchTab(tabId) {
    document.querySelectorAll('.account-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.account-tab').forEach(el => el.classList.remove('active'));
    
    document.getElementById('content-' + tabId).style.display = 'block';
    document.getElementById('tab-' + tabId).classList.add('active');
  }
</script>

@endsection
