@extends('admin.layout')

@section('admin_content')
<div>
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-primary">Coupon Registry</h2>
        <p class="text-xs text-muted mt-1">Create and manage promotional coupons with usage limits, expiry (IST), and active/inactive controls.</p>
    </div>

    {{-- Notifications --}}
    @if(session('success'))
        <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-6 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            @foreach($errors->all() as $error)
                <p>✕ {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        {{-- ── CREATE COUPON PANEL ────────────────────────────── --}}
        <div class="bg-silk/25 border border-gray-200 p-6 md:p-8 space-y-6">
            <div>
                <h3 class="text-sm font-bold tracking-widest text-primary uppercase">Generate Coupon</h3>
                <p class="text-[10px] text-muted mt-1">Define promotional vouchers with all constraints.</p>
            </div>

            <form action="{{ route('admin.coupons.store') }}" method="POST" class="space-y-4">
                @csrf

                {{-- Code --}}
                <div>
                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1.5">Coupon Code</label>
                    <input type="text"
                           name="code"
                           required
                           placeholder="e.g. FESTIVE20"
                           value="{{ old('code') }}"
                           class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none focus:border-primary transition-colors uppercase">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Type --}}
                    <div>
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1.5">Type</label>
                        <select name="type" required class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none cursor-pointer">
                            <option value="percent" {{ old('type') === 'percent' ? 'selected' : '' }}>Percent (%)</option>
                            <option value="fixed"   {{ old('type') === 'fixed'   ? 'selected' : '' }}>Fixed (₹)</option>
                        </select>
                    </div>

                    {{-- Value --}}
                    <div>
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1.5">Discount Value</label>
                        <input type="number"
                               step="0.01"
                               name="value"
                               required
                               placeholder="e.g. 20"
                               value="{{ old('value') }}"
                               class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none focus:border-primary transition-colors">
                    </div>
                </div>

                {{-- Min Order Amount --}}
                <div>
                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1.5">Min Order Amount (₹)</label>
                    <input type="number"
                           step="0.01"
                           name="min_cart_value"
                           required
                           value="{{ old('min_cart_value', 0) }}"
                           class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none focus:border-primary transition-colors">
                </div>

                {{-- Expiry Date & Time (IST) --}}
                <div>
                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1.5">
                        Expiry Date & Time <span class="text-[8px] text-secondary font-normal">(Indian Standard Time)</span>
                    </label>
                    <input type="datetime-local"
                           name="expires_at"
                           value="{{ old('expires_at') }}"
                           class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none cursor-pointer">
                    <span class="text-[8px] text-muted block mt-1">Leave empty for no expiry.</span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Total Usage Limit --}}
                    <div>
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1.5">Total Usage Limit</label>
                        <input type="number"
                               name="max_uses"
                               min="1"
                               placeholder="Unlimited"
                               value="{{ old('max_uses') }}"
                               class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none focus:border-primary transition-colors">
                        <span class="text-[8px] text-muted block mt-1">Leave empty = unlimited.</span>
                    </div>

                    {{-- Usage Per User --}}
                    <div>
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1.5">Uses Per User</label>
                        <input type="number"
                               name="max_uses_per_user"
                               min="1"
                               required
                               value="{{ old('max_uses_per_user', 1) }}"
                               class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none focus:border-primary transition-colors">
                    </div>
                </div>

                {{-- Status --}}
                <div class="flex items-center gap-3">
                    <input type="checkbox"
                           name="is_active"
                           id="create_is_active"
                           value="1"
                           checked
                           class="w-4 h-4 accent-primary">
                    <label for="create_is_active" class="text-xs text-primary cursor-pointer">Active (coupon is usable immediately)</label>
                </div>

                <button type="submit" class="w-full btn-primary !py-3 text-[10px] text-center uppercase tracking-wider font-semibold">
                    ✦ Issue Coupon
                </button>
            </form>
        </div>

        {{-- ── COUPON LEDGER ──────────────────────────────────── --}}
        <div class="lg:col-span-2">
            @if($coupons->isEmpty())
                <div class="text-center py-16 border border-dashed border-gray-100 bg-silk/30">
                    <span class="text-3xl">✦</span>
                    <p class="text-sm text-muted mt-3">No promotional coupons registered yet.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($coupons as $coupon)
                        @php
                            $isValid    = $coupon->isGloballyValid();
                            $isExpired  = $coupon->expires_at && $coupon->expires_at->isPast();
                            $isExhausted = $coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses;
                            $statusLabel = !$coupon->is_active ? 'Inactive' : ($isExpired ? 'Expired' : ($isExhausted ? 'Exhausted' : 'Active'));
                            $statusClass = match($statusLabel) {
                                'Active'    => 'bg-emerald-50 text-emerald-800',
                                'Inactive'  => 'bg-gray-100 text-gray-600',
                                'Expired'   => 'bg-rose-50 text-rose-800',
                                'Exhausted' => 'bg-amber-50 text-amber-700',
                                default     => 'bg-gray-100 text-gray-600',
                            };
                            $expiryIST = $coupon->expires_at
                                ? $coupon->expires_at->setTimezone('Asia/Kolkata')
                                : null;
                        @endphp

                        <div class="border border-gray-150 p-5 bg-white relative hover:shadow-sm transition-shadow" id="coupon-card-{{ $coupon->id }}">
                            {{-- Delete --}}
                            <form action="{{ route('admin.coupons.delete', $coupon->id) }}" method="POST"
                                  onsubmit="return confirm('Delete coupon {{ $coupon->code }}?')"
                                  class="absolute top-4 right-4">
                                @csrf
                                <button type="submit" class="text-muted hover:text-red-600 transition-colors text-xs font-semibold">✕</button>
                            </form>

                            <div class="space-y-4 pr-6">
                                {{-- Header --}}
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono text-sm font-bold text-primary bg-silk px-2.5 py-1 uppercase border border-gray-200/50">
                                        {{ $coupon->code }}
                                    </span>
                                    <span class="text-[9px] px-2 py-0.5 font-bold uppercase tracking-wide {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                {{-- Details --}}
                                <div class="text-xs space-y-1.5 text-muted">
                                    <div class="text-primary font-semibold">
                                        {{ $coupon->type === 'percent' ? $coupon->value . '% off' : '₹' . number_format($coupon->value) . ' off' }}
                                    </div>
                                    <div>Min Order: <strong class="text-primary">₹{{ number_format($coupon->min_cart_value) }}</strong></div>
                                    <div class="flex gap-4 flex-wrap">
                                        <span>Used: <strong class="text-primary">{{ $coupon->used_count }}</strong>
                                            @if($coupon->max_uses) / {{ $coupon->max_uses }} @else / ∞ @endif
                                        </span>
                                        <span>Per User: <strong class="text-primary">{{ $coupon->max_uses_per_user }}</strong></span>
                                    </div>

                                    {{-- Usage Progress Bar --}}
                                    @if($coupon->max_uses)
                                        @php $pct = min(100, round(($coupon->used_count / $coupon->max_uses) * 100)); @endphp
                                        <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden mt-1">
                                            <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-rose-400' : 'bg-emerald-400' }}"
                                                 style="width: {{ $pct }}%"></div>
                                        </div>
                                        <div class="text-[9px] text-muted">{{ $pct }}% used</div>
                                    @endif

                                    @if($expiryIST)
                                        <div class="text-[10px] {{ $isExpired ? 'text-red-500' : 'text-muted' }}">
                                            Expires: {{ $expiryIST->format('d M Y, h:i A') }} IST
                                        </div>
                                    @else
                                        <div class="text-[10px] text-emerald-600">✦ No Expiration</div>
                                    @endif
                                </div>

                                {{-- Actions --}}
                                <div class="flex gap-2 pt-2 border-t border-gray-100">
                                    {{-- Toggle Active/Inactive --}}
                                    <form action="{{ route('admin.coupons.toggle', $coupon->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="text-[9px] px-3 py-1.5 font-bold uppercase tracking-wide border transition-colors
                                                    {{ $coupon->is_active
                                                        ? 'border-gray-300 text-gray-600 hover:bg-gray-50'
                                                        : 'border-emerald-300 text-emerald-700 hover:bg-emerald-50' }}">
                                            {{ $coupon->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>

                                    {{-- Edit toggle --}}
                                    <button type="button"
                                            onclick="toggleEdit({{ $coupon->id }})"
                                            class="text-[9px] px-3 py-1.5 font-bold uppercase tracking-wide border border-gray-300 text-gray-600 hover:bg-gray-50 transition-colors">
                                        Edit
                                    </button>
                                </div>

                                {{-- Inline Edit Form (hidden by default) --}}
                                <div id="edit-form-{{ $coupon->id }}" class="hidden mt-4 border-t border-gray-100 pt-4">
                                    <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST" class="space-y-3">
                                        @csrf
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="text-[8px] font-bold text-muted uppercase tracking-wider block mb-1">Code</label>
                                                <input type="text" name="code" value="{{ $coupon->code }}"
                                                       class="w-full text-xs text-primary bg-white border border-gray-200 px-2 py-1.5 outline-none uppercase">
                                            </div>
                                            <div>
                                                <label class="text-[8px] font-bold text-muted uppercase tracking-wider block mb-1">Type</label>
                                                <select name="type" class="w-full text-xs text-primary bg-white border border-gray-200 px-2 py-1.5 outline-none">
                                                    <option value="percent" {{ $coupon->type === 'percent' ? 'selected' : '' }}>Percent %</option>
                                                    <option value="fixed"   {{ $coupon->type === 'fixed'   ? 'selected' : '' }}>Fixed ₹</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="text-[8px] font-bold text-muted uppercase tracking-wider block mb-1">Value</label>
                                                <input type="number" step="0.01" name="value" value="{{ $coupon->value }}"
                                                       class="w-full text-xs text-primary bg-white border border-gray-200 px-2 py-1.5 outline-none">
                                            </div>
                                            <div>
                                                <label class="text-[8px] font-bold text-muted uppercase tracking-wider block mb-1">Min Order (₹)</label>
                                                <input type="number" step="0.01" name="min_cart_value" value="{{ $coupon->min_cart_value }}"
                                                       class="w-full text-xs text-primary bg-white border border-gray-200 px-2 py-1.5 outline-none">
                                            </div>
                                            <div>
                                                <label class="text-[8px] font-bold text-muted uppercase tracking-wider block mb-1">Total Limit</label>
                                                <input type="number" name="max_uses" value="{{ $coupon->max_uses }}" placeholder="∞"
                                                       class="w-full text-xs text-primary bg-white border border-gray-200 px-2 py-1.5 outline-none">
                                            </div>
                                            <div>
                                                <label class="text-[8px] font-bold text-muted uppercase tracking-wider block mb-1">Per User</label>
                                                <input type="number" name="max_uses_per_user" value="{{ $coupon->max_uses_per_user }}" min="1"
                                                       class="w-full text-xs text-primary bg-white border border-gray-200 px-2 py-1.5 outline-none">
                                            </div>
                                            <div class="col-span-2">
                                                <label class="text-[8px] font-bold text-muted uppercase tracking-wider block mb-1">Expiry (IST)</label>
                                                <input type="datetime-local"
                                                       name="expires_at"
                                                       value="{{ $coupon->expires_at ? $coupon->expires_at->setTimezone('Asia/Kolkata')->format('Y-m-d\TH:i') : '' }}"
                                                       class="w-full text-xs text-primary bg-white border border-gray-200 px-2 py-1.5 outline-none">
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="submit" class="flex-1 btn-primary !py-2 text-[9px] uppercase tracking-wider">Save Changes</button>
                                            <button type="button" onclick="toggleEdit({{ $coupon->id }})"
                                                    class="px-4 py-2 text-[9px] uppercase tracking-wider border border-gray-200 text-muted hover:bg-gray-50">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleEdit(id) {
    const form = document.getElementById('edit-form-' + id);
    form.classList.toggle('hidden');
}
</script>
@endsection
