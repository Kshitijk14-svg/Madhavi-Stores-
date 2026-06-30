<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cleanup Panel — Madhavi Stores</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:system-ui,sans-serif;font-size:14px;background:#f5f5f5;color:#111;padding:24px}
  h1{font-size:20px;font-weight:700;margin-bottom:4px}
  .subtitle{color:#888;font-size:12px;margin-bottom:28px}
  .section{background:#fff;border:1px solid #e5e5e5;border-radius:8px;margin-bottom:32px;overflow:hidden}
  .section-header{padding:16px 20px;border-bottom:1px solid #e5e5e5;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
  .section-header h2{font-size:15px;font-weight:700}
  table{width:100%;border-collapse:collapse}
  th,td{padding:10px 16px;text-align:left;border-bottom:1px solid #f0f0f0;vertical-align:middle}
  th{background:#fafafa;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#666}
  tr:last-child td{border-bottom:none}
  tr:hover td{background:#fafafa}
  .badge{display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:3px;text-transform:uppercase}
  .badge-admin{background:#fef3c7;color:#92400e}
  .badge-user{background:#f3f4f6;color:#6b7280}
  .badge-shipped{background:#dbeafe;color:#1d4ed8}
  .badge-delivered{background:#dcfce7;color:#15803d}
  .badge-pending{background:#fef9c3;color:#a16207}
  .badge-cancelled{background:#fee2e2;color:#dc2626}
  .btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;font-size:12px;font-weight:700;border:none;border-radius:5px;cursor:pointer;text-decoration:none;letter-spacing:.03em}
  .btn-danger{background:#dc2626;color:#fff}
  .btn-danger:hover{background:#b91c1c}
  .btn-danger-outline{background:#fff;color:#dc2626;border:1px solid #dc2626}
  .btn-danger-outline:hover{background:#fef2f2}
  .btn-sm{padding:4px 10px;font-size:11px}
  .alert{padding:12px 16px;border-radius:6px;margin-bottom:20px;font-size:13px;font-weight:600}
  .alert-success{background:#dcfce7;color:#15803d;border:1px solid #bbf7d0}
  input[type=checkbox]{width:15px;height:15px;cursor:pointer;accent-color:#dc2626}
  .empty{padding:24px;text-align:center;color:#aaa;font-size:13px}
  .warning{background:#fef3c7;border:1px solid #fde68a;border-radius:6px;padding:12px 16px;font-size:12px;color:#92400e;margin-bottom:20px}
</style>
</head>
<body>

<h1>⚠️ Cleanup Panel</h1>
<p class="subtitle">Temporary admin tool — delete this page after use. Only accessible while logged in as admin.</p>

@if(session('success'))
  <div class="alert alert-success">✓ {{ session('success') }}</div>
@endif

<div class="warning">
  <strong>Caution:</strong> Deletions are permanent and cannot be undone. Products and collections are not shown here and will not be affected.
</div>

{{-- ── ORDERS ─────────────────────────────────────────────── --}}
<div class="section">
  <form method="POST" action="{{ route('admin.cleanup.delete_orders') }}" id="orders-form">
    @csrf
    <div class="section-header">
      <h2>Orders &amp; Order Items <span style="color:#888;font-weight:400;font-size:13px;">({{ $orders->count() }} total)</span></h2>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <button type="button" class="btn btn-danger-outline btn-sm" onclick="deleteSelected('orders-form','order_ids','orders-table','order-cb')">Delete Selected</button>
        <button type="button" class="btn btn-danger btn-sm" onclick="deleteAll('orders-form','order_ids')">Delete ALL Orders</button>
      </div>
    </div>
    @if($orders->isEmpty())
      <div class="empty">No orders found.</div>
    @else
    <table id="orders-table">
      <thead>
        <tr>
          <th><input type="checkbox" onchange="toggleAll(this,'order-cb')" title="Select all"></th>
          <th>#</th>
          <th>Order No.</th>
          <th>Customer</th>
          <th>Total</th>
          <th>Status</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($orders as $order)
        <tr>
          <td><input type="checkbox" class="order-cb" name="order_ids[]" value="{{ $order->id }}"></td>
          <td style="color:#aaa;">{{ $order->id }}</td>
          <td style="font-family:monospace;font-weight:600;">{{ $order->order_number }}</td>
          <td>
            <div>{{ $order->first_name }} {{ $order->last_name }}</div>
            <div style="color:#888;font-size:11px;">{{ $order->email }}</div>
          </td>
          <td style="font-weight:700;">₹{{ number_format($order->total, 0) }}</td>
          <td>
            @php $s = strtolower($order->order_status); @endphp
            <span class="badge badge-{{ in_array($s,['shipped','delivered','pending','cancelled']) ? $s : 'pending' }}">{{ $order->order_status }}</span>
          </td>
          <td style="color:#888;font-size:12px;white-space:nowrap;">{{ $order->created_at->format('d M Y') }}</td>
          <td>
            <button type="button" class="btn btn-danger-outline btn-sm"
              onclick="deleteSingle('orders-form','order_ids','{{ $order->id }}')">Delete</button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </form>
</div>

{{-- ── USERS ──────────────────────────────────────────────── --}}
<div class="section">
  <form method="POST" action="{{ route('admin.cleanup.delete_users') }}" id="users-form">
    @csrf
    <div class="section-header">
      <h2>Users <span style="color:#888;font-weight:400;font-size:13px;">({{ $users->count() }} total)</span></h2>
      <button type="button" class="btn btn-danger-outline btn-sm" onclick="deleteSelected('users-form','user_ids','users-table','user-cb')">Delete Selected</button>
    </div>
    @if($users->isEmpty())
      <div class="empty">No users found.</div>
    @else
    <table id="users-table">
      <thead>
        <tr>
          <th><input type="checkbox" onchange="toggleAll(this,'user-cb')" title="Select all"></th>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Joined</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $user)
        <tr>
          <td><input type="checkbox" class="user-cb" name="user_ids[]" value="{{ $user->id }}"></td>
          <td style="color:#aaa;">{{ $user->id }}</td>
          <td style="font-weight:600;">{{ $user->name }}</td>
          <td>{{ $user->email }}</td>
          <td><span class="badge badge-{{ $user->role === 'admin' || $user->is_admin ? 'admin' : 'user' }}">{{ $user->role ?? ($user->is_admin ? 'admin' : 'user') }}</span></td>
          <td style="color:#888;font-size:12px;white-space:nowrap;">{{ $user->created_at->format('d M Y') }}</td>
          <td>
            <button type="button" class="btn btn-danger-outline btn-sm"
              onclick="deleteSingle('users-form','user_ids','{{ $user->id }}')">Delete</button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </form>
</div>

<p style="color:#aaa;font-size:12px;text-align:center;margin-top:8px;">
  Remove this page: delete <code>resources/views/cleanup-panel.blade.php</code> and the routes in <code>routes/web.php</code> marked TEMPORARY.
</p>

<script>
function toggleAll(master, cls) {
  document.querySelectorAll('.' + cls).forEach(cb => cb.checked = master.checked);
}

function deleteSelected(formId, inputName, tableId, cbClass) {
  const checked = [...document.querySelectorAll('.' + cbClass + ':checked')];
  if (!checked.length) { alert('Select at least one row first.'); return; }
  if (!confirm('Delete ' + checked.length + ' selected item(s)? This cannot be undone.')) return;
  const form = document.getElementById(formId);
  // remove any stale hidden inputs
  form.querySelectorAll('input[type=hidden][name="' + inputName + '[]"]').forEach(el => el.remove());
  checked.forEach(cb => {
    const h = document.createElement('input');
    h.type = 'hidden'; h.name = inputName + '[]'; h.value = cb.value;
    form.appendChild(h);
  });
  form.submit();
}

function deleteSingle(formId, inputName, id) {
  if (!confirm('Delete this item permanently?')) return;
  const form = document.getElementById(formId);
  form.querySelectorAll('input[type=hidden][name="' + inputName + '[]"]').forEach(el => el.remove());
  const h = document.createElement('input');
  h.type = 'hidden'; h.name = inputName + '[]'; h.value = id;
  form.appendChild(h);
  form.submit();
}

function deleteAll(formId, inputName) {
  if (!confirm('Delete ALL orders permanently? This cannot be undone.')) return;
  const form = document.getElementById(formId);
  form.querySelectorAll('input[type=hidden][name="' + inputName + '[]"]').forEach(el => el.remove());
  const h = document.createElement('input');
  h.type = 'hidden'; h.name = inputName + '[]'; h.value = 'ALL';
  form.appendChild(h);
  form.submit();
}
</script>
</body>
</html>
