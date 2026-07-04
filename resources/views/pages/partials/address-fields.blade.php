@php $addr = $addr ?? null; @endphp
<input type="text" name="label" placeholder="Label (e.g. Home, Work) - optional" value="{{ $addr->label ?? '' }}"
       style="width:100%;padding:12px 16px;border:1px solid var(--border);background:var(--white);">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
  <input type="text" name="first_name" placeholder="First Name" required value="{{ $addr->first_name ?? '' }}"
         style="width:100%;padding:12px 16px;border:1px solid var(--border);background:var(--white);">
  <input type="text" name="last_name" placeholder="Last Name" required value="{{ $addr->last_name ?? '' }}"
         style="width:100%;padding:12px 16px;border:1px solid var(--border);background:var(--white);">
</div>
<input type="tel" name="phone" placeholder="10-digit mobile number" maxlength="10" pattern="[6-9][0-9]{9}" required value="{{ $addr->phone ?? '' }}"
       style="width:100%;padding:12px 16px;border:1px solid var(--border);background:var(--white);">
<input type="text" name="address" placeholder="Address" required value="{{ $addr->address ?? '' }}"
       style="width:100%;padding:12px 16px;border:1px solid var(--border);background:var(--white);">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
  <input type="text" name="city" placeholder="City" required value="{{ $addr->city ?? '' }}"
         style="width:100%;padding:12px 16px;border:1px solid var(--border);background:var(--white);">
  <input type="text" name="postal_code" placeholder="Postal Code" required value="{{ $addr->postal_code ?? '' }}"
         style="width:100%;padding:12px 16px;border:1px solid var(--border);background:var(--white);">
</div>
<label style="display:flex;align-items:center;gap:8px;font-size:11px;color:var(--muted);">
  <input type="checkbox" name="is_default" value="1" {{ ($addr->is_default ?? false) ? 'checked' : '' }}>
  Set as default address
</label>
