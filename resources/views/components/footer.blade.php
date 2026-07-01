{{-- ══ FOOTER ══ --}}
<footer style="background:#181818;color:#fff;font-family:'Manrope',sans-serif;">

  {{-- Main grid --}}
  <div style="max-width:1440px;margin:0 auto;padding:80px 40px;display:grid;grid-template-columns:2fr 1fr 1fr 1.5fr;gap:64px;">

    {{-- Brand --}}
    <div>
      <a href="{{ route('home') }}" style="display:block;margin-bottom:8px;">
        <img src="{{ asset('images/brand/Brand_logo.svg') }}" alt="Madhavi Stores" style="height:38px;width:auto;object-fit:contain;">
      </a>
      <p style="font-size:9px;font-weight:700;letter-spacing:0.4em;text-transform:uppercase;color:rgba(235,184,41,0.85);margin-bottom:20px;">The Ethnic Brand</p>
      <p style="color:rgba(255,255,255,0.45);font-size:13px;font-weight:300;line-height:1.8;max-width:260px;margin-bottom:28px;">
        A tribute to Indian heritage, reimagined for the modern muse. Every thread tells a story of craft, culture, and quiet elegance.
      </p>
      <div style="display:flex;gap:20px;">
        @foreach([['Instagram','https://www.instagram.com/madhavi_stores?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=='],['WhatsApp','https://wa.me/918799998770']] as [$social, $url])
          <a href="{{ $url }}" target="_blank" rel="noopener" style="font-size:9px;font-weight:700;letter-spacing:0.35em;text-transform:uppercase;color:rgba(255,255,255,0.3);text-decoration:none;transition:color 0.2s;"
             onmouseover="this.style.color='#ebb829'" onmouseout="this.style.color='rgba(255,255,255,0.3)'">{{ $social }}</a>
        @endforeach
      </div>
    </div>

    {{-- Explore links (mirror the navbar pages) --}}
    <div>
      <p style="font-size:9px;font-weight:700;letter-spacing:0.4em;text-transform:uppercase;color:rgba(255,255,255,0.3);margin-bottom:28px;">Explore</p>
      <ul style="list-style:none;display:flex;flex-direction:column;gap:14px;">
        @foreach([['Home',route('home')],['Shop',route('shop')],['Collections',route('collections.index')],['Our Story',route('about')]] as [$l,$h])
          <li><a href="{{ $h }}" style="font-size:13px;color:rgba(255,255,255,0.5);text-decoration:none;transition:color 0.2s;"
             onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">{{ $l }}</a></li>
        @endforeach
      </ul>
    </div>

    {{-- Contact --}}
    <div>
      <p style="font-size:9px;font-weight:700;letter-spacing:0.4em;text-transform:uppercase;color:rgba(255,255,255,0.3);margin-bottom:28px;">Visit Us</p>
      <address style="font-style:normal;color:rgba(255,255,255,0.5);font-size:13px;font-weight:300;line-height:1.9;margin-bottom:18px;">
        Opposite Laxmicycle,<br>
        GG Road, Nanded,<br>
        Maharashtra 431601
      </address>
      <a href="tel:+918799998770" style="font-size:13px;color:rgba(255,255,255,0.5);text-decoration:none;transition:color 0.2s;display:block;margin-bottom:8px;"
         onmouseover="this.style.color='#ebb829'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">+91 87999 98770</a>
      <a href="https://wa.me/918799998770" target="_blank" rel="noopener" style="font-size:13px;color:rgba(255,255,255,0.5);text-decoration:none;transition:color 0.2s;"
         onmouseover="this.style.color='#ebb829'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">Chat on WhatsApp</a>
    </div>

    {{-- Newsletter --}}
    <div>
      <p style="font-size:9px;font-weight:700;letter-spacing:0.4em;text-transform:uppercase;color:rgba(255,255,255,0.3);margin-bottom:28px;">Join the Atelier</p>
      <p style="font-size:13px;color:rgba(255,255,255,0.5);font-weight:300;margin-bottom:20px;">New drops, exclusive offers, editorial content.</p>
      <form action="#" method="POST" style="display:flex;border-bottom:1px solid rgba(255,255,255,0.15);padding-bottom:12px;">
        @csrf
        <input type="email" placeholder="Your email" required
               style="flex:1;background:transparent;border:none;font-size:13px;color:#fff;font-family:'Manrope',sans-serif;outline:none;"
               onfocus="this.parentElement.style.borderBottomColor='#ebb829'" onblur="this.parentElement.style.borderBottomColor='rgba(255,255,255,0.15)'">
        <button type="submit" style="background:none;border:none;cursor:pointer;color:rgba(255,255,255,0.4);padding-left:12px;transition:color 0.2s;"
                onmouseover="this.style.color='#ebb829'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3"/></svg>
        </button>
      </form>
    </div>

  </div>

  {{-- Bottom bar --}}
  <div style="border-top:1px solid rgba(255,255,255,0.05);">
    <div style="max-width:1440px;margin:0 auto;padding:20px 40px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
      <p style="font-size:9px;font-weight:700;letter-spacing:0.35em;text-transform:uppercase;color:rgba(255,255,255,0.2);">
        &copy; {{ date('Y') }} Madhavi Stores. Crafted with intention.
      </p>
      <div style="display:flex;gap:24px;flex-wrap:wrap;">
        @foreach([['Shipping Policy', route('shipping.policy')],['Exchange &amp; Return Policy', route('return.policy')],['Privacy Policy', route('privacy.policy')],['Terms &amp; Conditions', route('terms.conditions')]] as [$label, $href])
          <a href="{{ $href }}" style="font-size:9px;font-weight:700;letter-spacing:0.35em;text-transform:uppercase;color:rgba(255,255,255,0.2);text-decoration:none;transition:color 0.2s;"
             onmouseover="this.style.color='rgba(255,255,255,0.5)'" onmouseout="this.style.color='rgba(255,255,255,0.2)'">{!! $label !!}</a>
        @endforeach
      </div>
    </div>
  </div>

</footer>
