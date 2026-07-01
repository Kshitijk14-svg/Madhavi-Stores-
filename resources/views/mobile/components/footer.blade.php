{{-- ══ MOBILE FOOTER ══ --}}
<footer style="background:#181818;color:#fff;font-family:'Montserrat',sans-serif;margin-top:48px;padding:40px 24px calc(40px + env(safe-area-inset-bottom));">

  <div style="text-align:center;">
    <a href="{{ route('home') }}" style="display:inline-block;margin-bottom:8px;">
      <img src="{{ asset('images/brand/Brand_logo.svg') }}" alt="Madhavi Stores" style="height:34px;width:auto;object-fit:contain;">
    </a>
    <p style="font-size:9px;font-weight:700;letter-spacing:0.4em;text-transform:uppercase;color:rgba(235,184,41,0.85);margin-bottom:24px;">The Ethnic Brand</p>
  </div>

  {{-- Contact --}}
  <div style="text-align:center;margin-bottom:28px;">
    <address style="font-style:normal;color:rgba(255,255,255,0.5);font-size:13px;font-weight:300;line-height:1.8;margin-bottom:14px;">
      Opposite Laxmicycle, GG Road,<br>Nanded, Maharashtra 431601
    </address>
    <a href="tel:+918799998770" style="display:inline-block;font-size:13px;color:#ebb829;text-decoration:none;margin-bottom:6px;">+91 87999 98770</a><br>
    <a href="https://wa.me/918799998770" target="_blank" rel="noopener" style="font-size:13px;color:rgba(255,255,255,0.5);text-decoration:none;">Chat on WhatsApp</a>
  </div>

  {{-- Explore (mirror navbar pages) --}}
  <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:16px 20px;margin-bottom:28px;">
    @foreach([['Home',route('home')],['Shop',route('shop')],['Collections',route('collections.index')],['Our Story',route('about')]] as [$l,$h])
      <a href="{{ $h }}" style="font-size:11px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;color:rgba(255,255,255,0.6);text-decoration:none;">{{ $l }}</a>
    @endforeach
  </div>

  {{-- Social --}}
  <div style="display:flex;justify-content:center;gap:24px;margin-bottom:28px;">
    @foreach([['Instagram','https://www.instagram.com/madhavi_stores?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=='],['WhatsApp','https://wa.me/918799998770']] as [$social, $url])
      <a href="{{ $url }}" target="_blank" rel="noopener" style="font-size:9px;font-weight:700;letter-spacing:0.3em;text-transform:uppercase;color:rgba(255,255,255,0.4);text-decoration:none;">{{ $social }}</a>
    @endforeach
  </div>

  {{-- Policy links --}}
  <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:16px;border-top:1px solid rgba(255,255,255,0.08);padding-top:24px;">
    @foreach([['Shipping Policy', route('shipping.policy')],['Returns', route('return.policy')],['Privacy', route('privacy.policy')],['Terms', route('terms.conditions')]] as [$label, $href])
      <a href="{{ $href }}" style="font-size:9px;font-weight:700;letter-spacing:0.25em;text-transform:uppercase;color:rgba(255,255,255,0.3);text-decoration:none;">{{ $label }}</a>
    @endforeach
  </div>

  <p style="text-align:center;font-size:9px;font-weight:700;letter-spacing:0.3em;text-transform:uppercase;color:rgba(255,255,255,0.2);margin-top:24px;">
    &copy; {{ date('Y') }} Madhavi Stores
  </p>

</footer>
