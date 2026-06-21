<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('code') — Madhavi Stores</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300;1,400&display=swap" rel="stylesheet">
  <style>
    :root { --primary:#181818; --secondary:#b8986e; }
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family: system-ui, -apple-system, sans-serif;
      background:#fff; color:var(--primary);
      min-height:100vh; display:flex; align-items:center; justify-content:center;
      text-align:center; padding:24px;
    }
    .wrap { max-width:480px; }
    .code {
      font-family:'Cormorant Garamond', serif; font-style:italic; font-weight:300;
      font-size:clamp(5rem, 18vw, 9rem); line-height:1; color:var(--secondary);
      margin-bottom:8px;
    }
    .title {
      font-family:'Cormorant Garamond', serif; font-weight:400;
      font-size:1.75rem; margin-bottom:16px;
    }
    .msg { font-size:14px; line-height:1.7; color:#666; margin-bottom:32px; }
    .btn {
      display:inline-block; padding:14px 32px; background:var(--primary); color:#fff;
      font-size:11px; font-weight:700; letter-spacing:0.2em; text-transform:uppercase;
      text-decoration:none; transition:opacity .2s;
    }
    .btn:hover { opacity:.85; }
    .brand {
      margin-top:48px; font-family:'Cormorant Garamond', serif; font-style:italic;
      font-size:1.25rem; color:var(--primary); opacity:.5; letter-spacing:.02em;
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="code">@yield('code')</div>
    <h1 class="title">@yield('title')</h1>
    <p class="msg">@yield('message')</p>
    <a href="{{ url('/') }}" class="btn">Return Home</a>
    <div class="brand">Madhavi Stores</div>
  </div>
</body>
</html>
