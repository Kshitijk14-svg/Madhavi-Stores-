# Chapter 4 — Security

*The common ways web apps get attacked, and the standard defences — every one of which appears in your project.*

← [Back to Chapter 3](03-authentication-and-authorization.md) · Next → [Chapter 5: Rate Limiting](05-rate-limiting-and-abuse-prevention.md)

---

## 🧠 The Concept: the attacker mindset & "defense in depth"

Security starts with a shift in thinking. A normal developer asks *"how do I make this work?"* A security-minded developer also asks *"how could someone abuse this?"* Every input a user can send — form fields, URLs, cookies, headers, uploaded files — is a door an attacker can knock on. **Treat all input as hostile until proven safe.**

The second core idea is **defense in depth**: never rely on a single wall. Stack multiple independent defences so that if one fails, others still hold. A login is protected by hashing *and* rate limiting *and* HTTPS *and* validation — not just one of them.

The rest of this chapter walks the "greatest hits" of web vulnerabilities (these are roughly the industry-standard **OWASP Top 10**). For each: *what it is → how the attack works → how you defend.* Then we see your project's defence.

---

## 🧠 Vulnerability 1: SQL Injection (SQLi)

**What it is:** tricking the database into running commands the attacker wrote.

**How the attack works:** if you build a query by gluing user input into a string —

```
"SELECT * FROM users WHERE email = '" + userInput + "'"
```

— an attacker types `' OR '1'='1` as their "email," turning the query into something that matches every row, or worse (`'; DROP TABLE users; --`). The input *became code*.

**How you defend:** **never concatenate input into SQL.** Use **parameterised queries** (a.k.a. prepared statements): the query structure and the data travel separately, so input is *always* treated as a value, never as code. An ORM does this for you automatically ([Chapter 2](02-data-modeling-and-orm.md)).

---

## 🧠 Vulnerability 2: Cross-Site Scripting (XSS)

**What it is:** tricking *a victim's browser* into running the attacker's JavaScript.

**How the attack works:** say a product review form lets you post `<script>steal(document.cookie)</script>`. If the site later displays that review *as raw HTML*, every visitor's browser runs the attacker's script — which can steal session cookies, deface the page, or submit actions as the victim.

**How you defend:**
- **Output escaping** — when displaying user content, convert dangerous characters (`<`, `>`, `&`) into harmless display versions (`&lt;`, `&gt;`). The browser then *shows* `<script>` as text instead of *running* it. Modern templating escapes by default.
- **Content Security Policy (CSP)** — a response header that tells the browser "only run scripts from *these* trusted sources." Even if a script sneaks in, the browser refuses to execute it. (More below.)

---

## 🧠 Vulnerability 3: Cross-Site Request Forgery (CSRF)

**What it is:** making a logged-in victim's browser perform an action *they didn't intend*, on a site where they're already authenticated.

**How the attack works:** you're logged into your store. An attacker emails you a link or hides an auto-submitting form on another website. Because your browser *automatically attaches your session cookie* to any request to your store, the forged request arrives looking exactly like *you* clicked it — "change my email to attacker@evil.com," "place this order."

**How you defend:** the **CSRF token** (a.k.a. "synchronizer token"). On every form your server embeds a secret, per-session random value. A genuine submission sends it back; a forged request from another site *can't know it*. No valid token → request rejected. (This is why state-changing actions use POST with a token, never a bare GET link.)

---

## 🧠 Vulnerability 4: Mass Assignment / privilege escalation

**What it is:** a user setting fields they were never meant to touch by adding extra form parameters.

**How the attack works:** a "save profile" form normally sends `name` and `email`. The attacker adds `role=admin` (or `is_admin=1`) to the submitted data. If your code blindly saves *whatever fields arrived* straight into the database, the attacker just made themselves an admin.

**How you defend:** an **allow-list** of mass-assignable fields. Only explicitly-listed columns can be set from request data; sensitive fields (roles, prices, verification flags, IDs) are excluded and set *only* by trusted server code. (We met this in [Chapter 3](03-authentication-and-authorization.md) with the `User` model.)

---

## 🧠 Vulnerability 5: Clickjacking & MIME-sniffing (header-based attacks)

- **Clickjacking** — the attacker loads *your* site invisibly inside a transparent frame on *their* page, tricking the victim into clicking your buttons while thinking they're clicking something else. **Defence:** the `X-Frame-Options` (or CSP `frame-ancestors`) header tells browsers "don't let other sites embed me in a frame."
- **MIME-sniffing** — browsers sometimes *guess* a file's type and may execute an uploaded "image" as a script. **Defence:** the `X-Content-Type-Options: nosniff` header forbids that guessing.

These are fixed by sending the right **HTTP response headers** — cheap, powerful, and easy to forget.

---

## 🧠 The Concept: Content Security Policy (CSP), in depth

CSP is worth its own moment because it's the strongest XSS backstop. It's a response header listing *exactly* which sources the browser may load each kind of resource from:

```
script-src 'self' https://checkout.razorpay.com   →  only run JS from our own domain + Razorpay
img-src 'self' data: https://…                     →  only load images from these places
object-src 'none'                                  →  never load Flash/embeds (a classic attack vector)
form-action 'self' https://api.razorpay.com        →  forms may only submit to us or Razorpay
```

If an injected `<script src="evil.com">` appears, the browser checks the policy, sees `evil.com` isn't allowed, and refuses. It's defense-in-depth: even if escaping somehow fails, CSP catches it.

(There's a known weak spot: allowing `'unsafe-inline'` scripts loosens CSP, because it permits inline `<script>` blocks. Many real apps need it for legacy reasons and accept it as a documented trade-off — your project does exactly this and *says so* in a comment.)

---

## 🧠 The Concept: secrets management & HTTPS

- **Secrets** (database passwords, payment API keys) must **never** live in your source code — anyone with the code would have the keys, and code ends up in git history forever. The standard is the **environment file** (`.env`): secrets live in a file that is *not* committed to version control and differs per environment (dev vs production). Code reads `config('razorpay.key_secret')`; the actual value is injected from the environment. This is part of the widely-used **"12-Factor App"** methodology ([Chapter 9](09-testing-and-system-design.md)).
- **HTTPS/TLS** encrypts traffic between browser and server so nobody on the network can read passwords or session cookies in transit. **HSTS** (`Strict-Transport-Security` header) goes further: it tells browsers "always use HTTPS for this site from now on," preventing downgrade-to-HTTP attacks.

---

## 🧠 The Concept: validation — the first gate

Before *any* of the above, there's **input validation**: checking that incoming data is the right shape *before* you act on it. "Email must look like an email," "quantity must be a positive integer ≤ 99," "postal code max 20 chars." Validation:

- rejects malformed/malicious input early,
- protects business rules (no negative quantities),
- and produces clear errors instead of crashes.

It's not a *replacement* for the defences above (you still escape output and parameterise queries), but it's the cheap first filter every request should pass.

---

## 🔍 In Your Project

Your project applies a defence for **every** vulnerability above. Here's the scorecard:

| Threat | Your defence | Where |
|---|---|---|
| SQL injection | Eloquent ORM everywhere; values always parameterised | all models/queries ([Ch 2](02-data-modeling-and-orm.md)) |
| XSS | Blade auto-escaping (`{{ }}`) **+** a strict CSP header | views + `SecurityHeaders` |
| CSRF | Tokens on all forms; only the webhook is exempt (by signature instead) | framework default + `bootstrap/app.php` |
| Mass assignment | `$fillable` allow-lists; privilege fields excluded | `User`, `Product`, `Coupon`, `Order` models |
| Clickjacking | `X-Frame-Options: SAMEORIGIN` | `SecurityHeaders` |
| MIME-sniffing | `X-Content-Type-Options: nosniff` | `SecurityHeaders` |
| Eavesdropping | HSTS header on HTTPS | `SecurityHeaders` |
| Bad input | `$request->validate([...])` on every write action | controllers |
| Secret leakage | API keys in `.env`, read via `config()` | `.env`, `config/razorpay.php` |
| Brute force | Rate limiting | [Chapter 5](05-rate-limiting-and-abuse-prevention.md) |

### Security headers, in one tidy place

Your `SecurityHeaders` middleware (`app/Http/Middleware/SecurityHeaders.php`) sets the whole protective suite on *every response*:

```php
$response->headers->set('Content-Security-Policy', /* the policy below */);
$response->headers->set('X-Content-Type-Options', 'nosniff');     // no MIME guessing
$response->headers->set('X-Frame-Options', 'SAMEORIGIN');         // no clickjacking
$response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
$response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
if ($request->isSecure()) {                                       // HSTS only over HTTPS
    $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
}
```

Its CSP allows scripts/frames/forms only from your own domain plus the specific external services you actually use (Razorpay for payments, a couple of CDNs and font hosts) and blocks embeds entirely (`object-src 'none'`). The HSTS header is *only* emitted on secure requests — a thoughtful touch so it never breaks local HTTP development. The file even documents the `'unsafe-inline'` trade-off honestly:

> *"script-src still allows 'unsafe-inline'/'unsafe-eval' because the app relies on inline handlers/styles; tightening that is a separate, larger refactor (tracked as a known defence-in-depth gap)."*

That comment is exactly how a professional records a *known, accepted* risk — not hiding it, but flagging it for later.

### CSRF — protected by default, webhook exempt for a *reason*

Laravel verifies a CSRF token on every form POST automatically; your Blade forms include it. The *one* exception is the Razorpay webhook, deliberately exempted in `bootstrap/app.php`:

```php
// /webhooks/* are exempt from CSRF — they come from Razorpay's servers,
// which can't carry your CSRF token. They are verified by SIGNATURE instead.
```

This is the right call and a great teaching moment: CSRF tokens defend *browser→your-server* requests. A webhook is *server→server*, so the CSRF token doesn't apply — but you don't just leave it open; you replace that defence with **signature verification** ([Chapter 6](06-payments-and-third-party-integrations.md)). Remove one wall, add an equivalent one. Defense in depth.

### Mass assignment — privilege fields are locked

As we saw in [Chapter 3](03-authentication-and-authorization.md), your models carefully *exclude* sensitive fields from `$fillable`:

- `User`: `role` and `is_admin` excluded — can't be set from a form.
- `Product`: `rating` and `review_count` excluded — only the review hook may set them.
- `Coupon`: `used_count` is managed by code (`incrementUsage`), not user input.

Each one closes a privilege/abuse hole.

### Validation on every write

Every state-changing controller method validates first. Checkout is typical (`CheckoutController::store`):

```php
$request->validate([
    'first_name' => 'required|string|max:100',
    'email'      => 'required|email|max:255',
    'postal_code'=> 'required|string|max:20',
    // …
]);
```

And critically, the checkout **never trusts the price or payment method coming from the browser**. The amount is recomputed server-side from `CartService`, and the payment method is hard-set to `'Razorpay'` server-side rather than read from the request — because anything the client sends can be forged. *"Never trust the client"* is the thread running through the whole file.

---

## ✅ Takeaways

1. **Treat all input as hostile** and stack defences (**defense in depth**) so no single failure is fatal.
2. **SQL injection** → parameterised queries / ORM. **XSS** → escape output + CSP. **CSRF** → per-session tokens. **Mass assignment** → `$fillable` allow-lists. Header attacks → the right **response headers**. Your project does all of these.
3. **CSP** is your strongest XSS backstop — it whitelists where scripts/images/forms may come from. Your `SecurityHeaders` middleware sets it (with one honestly-documented relaxation).
4. **Secrets belong in `.env`, never in code**; **HTTPS + HSTS** protect data in transit.
5. **Validate every write**, and **never trust client-supplied prices, roles, or payment details** — recompute or hard-set them server-side, as your checkout does.
6. When you must *remove* a standard defence (the CSRF-exempt webhook), **replace it with an equivalent one** (signature verification).

Next: stopping floods, brute-force, and scraping → [Chapter 5: Rate Limiting](05-rate-limiting-and-abuse-prevention.md)
