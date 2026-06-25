@extends('admin.layout')
@section('admin_title', 'Coupons')

@section('admin_content')
<div class="mb-4">
  <h2 class="font-display text-2xl text-primary">Coupon Registry</h2>
  <p class="text-[11px] text-muted mt-0.5">Promotional vouchers with limits and expiry (IST).</p>
</div>

{{-- Create toggle --}}
<button type="button" onclick="document.getElementById('coupon-create').classList.toggle('hidden')"
        class="w-full text-center text-[10px] font-bold tracking-widest uppercase text-white bg-primary py-3 mb-5">✦ Create Coupon</button>

{{-- Create form (collapsible) --}}
<div id="coupon-create" class="hidden mb-6">
  <form action="{{ route('admin.coupons.store') }}" method="POST" class="bg-silk/25 border border-gray-200 p-5 space-y-4">
    @csrf
    <div>
      <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Coupon Code</label>
      <input type="text" name="code" required placeholder="e.g. FESTIVE20" value="{{ old('code') }}"
             class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none uppercase">
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Type</label>
        <select name="type" required class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
          <option value="percent" {{ old('type') === 'percent' ? 'selected' : '' }}>Percent (%)</option>
          <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixed (₹)</option>
        </select>
      </div>
      <div>
        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Value</label>
        <input type="number" step="0.01" name="value" required placeholder="e.g. 20" value="{{ old('value') }}"
               class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
      </div>
    </div>
    <div>
      <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Min Order Amount (₹)</label>
      <input type="number" step="0.01" name="min_cart_value" required value="{{ old('min_cart_value', 0) }}"
             class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
    </div>
    <div>
      <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Expiry Date & Time (IST)</label>
      <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
             class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
      <span class="text-[8px] text-muted block mt-1">Leave empty for no expiry.</span>
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Total Limit</label>
        <input type="number" name="max_uses" min="1" placeholder="Unlimited" value="{{ old('max_uses') }}"
               class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
      </div>
      <div>
        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Uses Per User</label>
        <input type="number" name="max_uses_per_user" min="1" required value="{{ old('max_uses_per_user', 1) }}"
               class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
      </div>
    </div>
    <div class="flex items-center gap-2">
      <input type="checkbox" name="is_active" id="create_is_active" value="1" checked class="w-4 h-4 accent-primary">
      <label for="create_is_active" class="text-xs text-primary">Active immediately</label>
    </div>
    <button type="submit" class="w-full btn-primary !py-3 text-[10px] uppercase tracking-wider font-semibold">✦ Issue Coupon</button>
  </form>
</div>

{{-- Coupon cards --}}
@if($coupons->isEmpty())
  <div class="text-center py-12 border border-dashed border-gray-200 bg-white">
    <span class="text-2xl">✦</span>
    <p class="text-sm text-muted mt-2">No coupons registered yet.</p>
  </div>
@else
  <div class="space-y-3">
    @foreach($coupons as $coupon)
      @php
        $isExpired   = $coupon->expires_at && $coupon->expires_at->isPast();
        $isExhausted = $coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses;
        $statusLabel = !$coupon->is_active ? 'Inactive' : ($isExpired ? 'Expired' : ($isExhausted ? 'Exhausted' : 'Active'));
        $statusClass = match($statusLabel) {
          'Active'    => 'bg-emerald-50 text-emerald-800',
          'Inactive'  => 'bg-gray-100 text-gray-600',
          'Expired'   => 'bg-rose-50 text-rose-800',
          'Exhausted' => 'bg-amber-50 text-amber-700',
          default     => 'bg-gray-100 text-gray-600',
        };
        $expiryIST = $coupon->expires_at ? $coupon->expires_at->setTimezone('Asia/Kolkata') : null;
      @endphp
      <div class="border border-gray-100 bg-white p-4 relative" id="coupon-card-{{ $coupon->id }}">
        <form action="{{ route('admin.coupons.delete', $coupon->id) }}" method="POST"
              onsubmit="return confirm('Delete coupon {{ $coupon->code }}?')" class="absolute top-3 right-3">
          @csrf
          <button type="submit" class="text-muted hover:text-red-600 text-sm">✕</button>
        </form>

        <div class="flex items-center gap-2 flex-wrap pr-6">
          <span class="font-mono text-sm font-bold text-primary bg-silk px-2.5 py-1 uppercase border border-gray-200/50">{{ $coupon->code }}</span>
          <span class="text-[8px] px-2 py-0.5 font-bold uppercase tracking-wide {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>

        <div class="text-xs space-y-1.5 text-muted mt-3">
          <div class="text-primary font-semibold">{{ $coupon->type === 'percent' ? $coupon->value . '% off' : '₹' . number_format($coupon->value) . ' off' }}</div>
          <div>Min Order: <strong class="text-primary">₹{{ number_format($coupon->min_cart_value) }}</strong></div>
          <div class="flex gap-4 flex-wrap">
            <span>Used: <strong class="text-primary">{{ $coupon->used_count }}</strong> @if($coupon->max_uses) / {{ $coupon->max_uses }} @else / ∞ @endif</span>
            <span>Per User: <strong class="text-primary">{{ $coupon->max_uses_per_user }}</strong></span>
          </div>
          @if($coupon->max_uses)
            @php $pct = min(100, round(($coupon->used_count / $coupon->max_uses) * 100)); @endphp
            <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden mt-1">
              <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-rose-400' : 'bg-emerald-400' }}" style="width: {{ $pct }}%"></div>
            </div>
            <div class="text-[9px] text-muted">{{ $pct }}% used</div>
          @endif
          @if($expiryIST)
            <div class="text-[10px] {{ $isExpired ? 'text-red-500' : 'text-muted' }}">Expires: {{ $expiryIST->format('d M Y, h:i A') }} IST</div>
          @else
            <div class="text-[10px] text-emerald-600">✦ No Expiration</div>
          @endif
        </div>

        <div class="flex gap-2 mt-3 pt-3 border-t border-gray-100">
          <form action="{{ route('admin.coupons.toggle', $coupon->id) }}" method="POST" class="flex-1">
            @csrf
            <button type="submit" class="w-full text-[9px] py-2 font-bold uppercase tracking-wide border transition-colors
                {{ $coupon->is_active ? 'border-gray-300 text-gray-600' : 'border-emerald-300 text-emerald-700' }}">
              {{ $coupon->is_active ? 'Deactivate' : 'Activate' }}
            </button>
          </form>
          <button type="button" onclick="toggleEdit({{ $coupon->id }})"
                  class="flex-1 text-[9px] py-2 font-bold uppercase tracking-wide border border-gray-300 text-gray-600">Edit</button>
        </div>

        {{-- Inline edit --}}
        <div id="edit-form-{{ $coupon->id }}" class="hidden mt-4 border-t border-gray-100 pt-4">
          <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-[8px] font-bold text-muted uppercase tracking-wider block mb-1">Code</label>
                <input type="text" name="code" value="{{ $coupon->code }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-2 py-2 outline-none uppercase">
              </div>
              <div>
                <label class="text-[8px] font-bold text-muted uppercase tracking-wider block mb-1">Type</label>
                <select name="type" class="w-full text-xs text-primary bg-white border border-gray-200 px-2 py-2 outline-none">
                  <option value="percent" {{ $coupon->type === 'percent' ? 'selected' : '' }}>Percent %</option>
                  <option value="fixed" {{ $coupon->type === 'fixed' ? 'selected' : '' }}>Fixed ₹</option>
                </select>
              </div>
              <div>
                <label class="text-[8px] font-bold text-muted uppercase tracking-wider block mb-1">Value</label>
                <input type="number" step="0.01" name="value" value="{{ $coupon->value }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-2 py-2 outline-none">
              </div>
              <div>
                <label class="text-[8px] font-bold text-muted uppercase tracking-wider block mb-1">Min Order (₹)</label>
                <input type="number" step="0.01" name="min_cart_value" value="{{ $coupon->min_cart_value }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-2 py-2 outline-none">
              </div>
              <div>
                <label class="text-[8px] font-bold text-muted uppercase tracking-wider block mb-1">Total Limit</label>
                <input type="number" name="max_uses" value="{{ $coupon->max_uses }}" placeholder="∞" class="w-full text-xs text-primary bg-white border border-gray-200 px-2 py-2 outline-none">
              </div>
              <div>
                <label class="text-[8px] font-bold text-muted uppercase tracking-wider block mb-1">Per User</label>
                <input type="number" name="max_uses_per_user" value="{{ $coupon->max_uses_per_user }}" min="1" class="w-full text-xs text-primary bg-white border border-gray-200 px-2 py-2 outline-none">
              </div>
              <div class="col-span-2">
                <label class="text-[8px] font-bold text-muted uppercase tracking-wider block mb-1">Expiry (IST)</label>
                <input type="datetime-local" name="expires_at"
                       value="{{ $coupon->expires_at ? $coupon->expires_at->setTimezone('Asia/Kolkata')->format('Y-m-d\TH:i') : '' }}"
                       class="w-full text-xs text-primary bg-white border border-gray-200 px-2 py-2 outline-none">
              </div>
            </div>
            <div class="flex gap-2">
              <button type="submit" class="flex-1 btn-primary !py-2 text-[9px] uppercase tracking-wider">Save</button>
              <button type="button" onclick="toggleEdit({{ $coupon->id }})" class="px-4 py-2 text-[9px] uppercase tracking-wider border border-gray-200 text-muted">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    @endforeach
  </div>
@endif
@endsection

@section('scripts')
<script>
  function toggleEdit(id) {
    const f = document.getElementById('edit-form-' + id);
    if (f) f.classList.toggle('hidden');
  }
</script>
@endsection
