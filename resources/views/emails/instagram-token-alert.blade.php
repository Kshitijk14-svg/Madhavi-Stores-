<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Instagram Token Alert</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #faf8f5; color: #181818; line-height: 1.6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; padding: 40px; }
        h1 { font-family: 'Georgia', serif; font-weight: normal; font-size: 22px; margin-bottom: 8px; }
        .banner { padding: 14px 20px; margin-bottom: 24px; font-size: 13px; font-weight: 600; background: #fef2f2; color: #dc2626; }
        p { font-size: 14px; margin-bottom: 12px; color: #444; }
        .details-box { background: #faf8f5; padding: 20px; margin: 20px 0; }
        .details-box p { margin: 0 0 8px 0; font-size: 13px; }
        .details-box p:last-child { margin-bottom: 0; }
        ol { font-size: 14px; color: #444; padding-left: 20px; }
        .cta { display: inline-block; background: #181818; color: #ffffff; text-decoration: none; font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; padding: 12px 24px; margin-top: 12px; }
        code { background: #f0ede8; padding: 1px 4px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Madhavi Stores — Admin Alert</h1>

        <div class="banner">The automatic Instagram token refresh is failing. If it is not fixed, the homepage Instagram feed will stop showing real posts.</div>

        <div class="details-box">
            <p><strong>Error:</strong> {{ $error }}</p>
            <p><strong>Token expires:</strong>
                @if($expiresAt)
                    {{ $expiresAt->toDayDateTimeString() }} ({{ $expiresAt->diffForHumans() }})
                @else
                    unknown
                @endif
            </p>
        </div>

        <p><strong>How to fix it:</strong></p>
        <ol>
            <li>Open the admin Design Manager and try <strong>Refresh now</strong> in the Instagram Feed section.</li>
            <li>If that still fails, generate a fresh long-lived token in the Meta Developer Console and paste it into the <em>Instagram access token</em> field on the same page.</li>
        </ol>

        <a href="{{ route('admin.design.index') }}" class="cta">Open Design Manager</a>
    </div>
</body>
</html>
