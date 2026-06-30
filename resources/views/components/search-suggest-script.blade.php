{{-- Shared live-search suggestions + recent-search history.
     Exposes window.MadhaviSearch ({ attach, pushHistory, clearHistory, readHistory }).
     Included by both the desktop and mobile app layouts. --}}
<style>
  .ss-box{position:absolute;left:0;right:0;top:calc(100% + 8px);background:#fff;border:1px solid #ececec;box-shadow:0 14px 44px rgba(0,0,0,0.13);z-index:400;max-height:62vh;overflow-y:auto;display:none;text-align:left;}
  .ss-item{display:flex;align-items:center;gap:12px;padding:10px 14px;text-decoration:none;color:var(--primary,#181818);border-bottom:1px solid #f5f5f5;}
  .ss-item:hover,.ss-recent:hover{background:#fafafa;}
  .ss-img{width:40px;height:52px;object-fit:cover;flex-shrink:0;background:#f5f5f5;}
  .ss-name{flex:1;font-size:13px;line-height:1.3;}
  .ss-price{font-size:13px;font-weight:700;color:var(--secondary,#b8860b);white-space:nowrap;}
  .ss-head{display:flex;align-items:center;justify-content:space-between;padding:10px 14px 6px;font-size:10px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#aaa;}
  .ss-clear{background:none;border:none;cursor:pointer;font:inherit;font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#d44d44;padding:0;}
  .ss-recent{display:block;padding:10px 14px;text-decoration:none;color:#444;font-size:13px;border-bottom:1px solid #f5f5f5;}
  .ss-recent::before{content:'\2197';margin-right:8px;color:#bbb;}
  .ss-empty{padding:16px 14px;font-size:13px;color:#999;}
</style>
<script>
window.MadhaviSearch = (function(){
  var HKEY = 'ms_search_history';
  function read(){ try { return JSON.parse(localStorage.getItem(HKEY) || '[]') || []; } catch(e){ return []; } }
  function write(a){ try { localStorage.setItem(HKEY, JSON.stringify(a.slice(0, 8))); } catch(e){} }
  function push(term){
    term = (term || '').trim();
    if(!term) return;
    var h = read().filter(function(t){ return t.toLowerCase() !== term.toLowerCase(); });
    h.unshift(term);
    write(h);
  }
  function clear(){ write([]); }
  function esc(s){
    return (s == null ? '' : String(s)).replace(/[&<>"']/g, function(c){
      return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
    });
  }

  function attach(input, box, opts){
    if(!input || !box || input.dataset.ssAttached) return;
    input.dataset.ssAttached = '1';
    opts = opts || {};
    var url = opts.url, shopBase = opts.shopBase || '/shop';
    var timer = null;

    function shopUrl(term){ return shopBase + (shopBase.indexOf('?') > -1 ? '&' : '?') + 'search=' + encodeURIComponent(term); }

    function renderRecent(){
      var h = read();
      if(!h.length){ box.style.display = 'none'; box.innerHTML = ''; return; }
      var html = '<div class="ss-head">Recent<button type="button" class="ss-clear">Clear</button></div>';
      html += h.map(function(t){ return '<a class="ss-recent" href="' + shopUrl(t) + '">' + esc(t) + '</a>'; }).join('');
      box.innerHTML = html;
      box.style.display = 'block';
      var c = box.querySelector('.ss-clear');
      if(c) c.addEventListener('click', function(e){ e.preventDefault(); clear(); renderRecent(); input.focus(); });
    }
    function renderResults(items, q){
      if(!items.length){
        box.innerHTML = '<div class="ss-empty">No matches for &ldquo;' + esc(q) + '&rdquo;</div>';
        box.style.display = 'block';
        return;
      }
      box.innerHTML = items.map(function(p){
        return '<a class="ss-item" href="' + esc(p.url) + '">'
          + '<img class="ss-img" src="' + esc(p.image || '') + '" alt="" loading="lazy">'
          + '<span class="ss-name">' + esc(p.name) + '</span>'
          + '<span class="ss-price">&#8377;' + Number(p.price || 0).toLocaleString('en-IN') + '</span>'
          + '</a>';
      }).join('');
      box.style.display = 'block';
    }
    function fetchSuggest(q){
      fetch(url + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
        .then(function(r){ return r.ok ? r.json() : []; })
        .then(function(items){ if(input.value.trim() === q) renderResults(items, q); })
        .catch(function(){});
    }

    input.addEventListener('input', function(){
      var q = input.value.trim();
      clearTimeout(timer);
      if(q.length < 2){ renderRecent(); return; }
      timer = setTimeout(function(){ fetchSuggest(q); }, 200);
    });
    input.addEventListener('focus', function(){ if(input.value.trim().length < 2) renderRecent(); });
    document.addEventListener('click', function(e){
      if(!box.contains(e.target) && e.target !== input) box.style.display = 'none';
    });
  }

  return { attach: attach, pushHistory: push, clearHistory: clear, readHistory: read };
})();
</script>
