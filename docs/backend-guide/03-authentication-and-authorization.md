# Chapter 3 — Authentication & Authorization

*How the backend knows **who you are** (authentication) and **what you're allowed to do** (authorization).*

← [Back to Chapter 2](02-data-modeling-and-orm.md) · Next → [Chapter 4: Security](04-security.md)

---

## 🧠 The Concept: two different questions

People constantly confuse these two words. They answer different questions:

- **Authentication (authN)** — *"Who are you?"* Proving identity. Logging in with a password is authentication.
- **Authorization (authZ)** — *"What may you do?"* Checking permissions. "Only admins can delete products" is authorization.

You authenticate **once** (log in), then the system authorizes **every** sensitive action after that. A logged-in *customer* is authenticated but *not authorized* to reach the admin panel.

---

## 🧠 The Concept: how login is *remembered* — sessions vs. tokens

HTTP is **stateless**: each request is independent and the server doesn't inherently remember the last one ([Chapter 1](01-mvc-and-request-lifecycle.md)). So after you log in, how does the *next* request know it's still you? Two dominant approaches:

### 1. Session-based (cookies)
- You log in. The server creates a **session** — a small record on the server side that says "this session belongs to user 42" — and gives your browser a **cookie** containing only a random session ID.
- Your browser automatically sends that cookie on every future request. The server looks up the session ID, finds "user 42," and knows it's you.
- The secret (the identity) stays on the *server*; the browser just holds a meaningless ticket stub.
- **Great for:** traditional websites where the same server renders your pages.

### 2. Token-based (e.g. JWT)
- On login the server hands the client a **token** — a signed string that *itself contains* the identity ("user 42") plus a cryptographic signature proving the server issued it.
- The client sends this token on each request. The server verifies the signature; it doesn't need to store anything.
- **Great for:** APIs, mobile apps, and systems spread across many servers, because the server keeps no session — the token is self-contained ("stateless auth").

> Your project is a classic server-rendered website, so it uses **session-based** auth. That's the right tool for the job. If you later add a mobile app or a public API, that's when tokens enter the picture.

A key security move in both models: when you log in, the server **regenerates the session ID**. This defeats "session fixation," where an attacker tricks you into using a session ID they already know. New ID at login = the attacker's known ID becomes worthless.

---

## 🧠 The Concept: never store passwords — *hash* them

This is the single most important rule in auth. **You must never store a password as-is.** If your database leaks (it happens), plaintext passwords mean every account everywhere (people reuse passwords) is instantly compromised.

Instead you store a **hash**:

- A **hash function** is a one-way maths blender: easy to turn `"hunter2"` → a scrambled string, *practically impossible* to reverse the scrambled string back into `"hunter2"`.
- At login, you hash the password the user typed and compare it to the stored hash. Match = correct password. **The real password is never stored and never needs to be.**

But not just any hash. For passwords you specifically use a **slow, salted password hash** like **bcrypt** (or argon2, scrypt):

- **Salt** — a random value mixed into each password before hashing. It means two users with the same password get *different* hashes, and it defeats "rainbow tables" (precomputed hash dictionaries). Modern password hashers generate and store the salt for you automatically.
- **Slow on purpose ("work factor" / "rounds")** — bcrypt is deliberately expensive to compute. For a real login that costs a few milliseconds: invisible. For an attacker trying *billions* of guesses against a stolen database, that slowness is the difference between cracking in hours vs. centuries. More rounds = slower = stronger. (The default is 10; higher is stronger.)

---

## 🧠 The Concept: OTP and multi-factor authentication

A password is *something you know*. But knowledge can be stolen (phishing, leaks, guessing). Stronger systems add a second, different *kind* of proof:

- *Something you know* — a password.
- *Something you have* — your phone/email (proven by a code sent to it).
- *Something you are* — a fingerprint or face.

A **One-Time Password (OTP)** is a short code, valid briefly and only once, sent to something you *have* (your email or phone). Proving you can read a code emailed to `you@example.com` proves you *control that inbox*. Using two of these categories together is **Multi-Factor Authentication (MFA / 2FA)**.

OTPs need a few safety properties to be trustworthy:
- **Short expiry** — a code that works for 10 minutes, not forever.
- **Single-use** — once used (or after too many wrong tries), it's burned.
- **Attempt cap** — only a handful of guesses, or a 6-digit code could be brute-forced (a million combinations is nothing to a computer otherwise).
- **Timing-safe comparison** — see the box below.

> **Timing-safe comparison.** A naïve string compare returns *faster* when the first character is wrong than when the first ten match. An attacker can *measure* those microsecond differences to guess a secret character-by-character (a "timing attack"). A **timing-safe** comparison always takes the same amount of time regardless of how much matches, leaking nothing. Any time you compare a secret (OTP, signature, token), you use one.

---

## 🧠 The Concept: authorization models (RBAC)

Once you know *who* someone is, you decide *what they can do*. The most common model is **Role-Based Access Control (RBAC)**:

- Every user has one or more **roles** (`customer`, `admin`, `superadmin`).
- Permissions attach to roles, not to individuals. "Admins can edit products." "Only superadmins can change other people's roles."
- To authorize an action you check the user's role, not a giant list of individual permissions.

Where do you *enforce* it? Two layers, ideally both:

1. **At the gate (middleware)** — block whole sections of the site. "Every `/admin/*` URL requires the admin role." A non-admin never even reaches the code.
2. **At the action (fine-grained checks)** — inside sensitive operations. "An admin may change roles, *but not* promote someone to superadmin or modify another superadmin." This stops *privilege escalation* — a lower-privileged user gaining powers they shouldn't.

A subtle but vital rule: **the role must be the single source of truth, and it must never be settable by the user.** If a registration form let you submit `role=admin`, you'd have a catastrophe. Privilege fields are set *only* by trusted server code. (This connects to "mass assignment" in [Chapter 4](04-security.md).)

---

## 🔍 In Your Project

Your auth lives mainly in `app/Http/Controllers/AuthController.php`, the `User` model, and the `otp_codes` table. It uses **every** concept above.

### Sessions + bcrypt

Login is session-based. Your `login()` method does the textbook thing:

```php
// app/Http/Controllers/AuthController.php
if (Auth::attempt(['email' => $request->email, 'password' => $request->password], true)) {
    RateLimiter::clear($this->throttleKey($request));
    $request->session()->regenerate();   // ← new session ID on login (anti session-fixation)
    // … then redirect based on role
}
```

Passwords are hashed with **bcrypt** (your `User` model casts the `password` field as `'hashed'`, and `.env` sets `BCRYPT_ROUNDS=12` — *higher than the default 10, i.e. deliberately extra-slow for attackers*). The stored hash is never the real password; `Auth::attempt` hashes the submitted password and compares.

Notice too: after a successful password match, the code *still* checks `email_verified_at` and logs the user back out if their email was never verified. Authentication isn't only "right password" — it's "right password **and** verified identity."

### OTP email verification — every safety property, by the book

Your registration doesn't trust an email address until the person proves they control it. The flow (`AuthController::register` → `verify`):

1. On register, the account is **not created yet** — the pending details sit in the session and a 6-digit OTP is emailed. (Smart: this avoids leaving half-made unverified accounts cluttering the users table.)
2. The user enters the code. `verify()` checks it with all four safety properties:

```php
// app/Http/Controllers/AuthController.php — verify()
$row = DB::table('otp_codes')->where('email', $email)
        ->where('expires_at', '>', now())     // ① short expiry (10 minutes)
        ->orderByDesc('id')->lockForUpdate()->first();   // row-locked (anti-race, see Ch 7)

if ($row->attempts >= 5) {                    // ② attempt cap — burn the code after 5 wrong tries
    DB::table('otp_codes')->where('email', $email)->delete();
    return 'Too many incorrect attempts…';
}
if (! hash_equals((string) $row->code, (string) $request->otp)) {  // ③ timing-safe comparison
    DB::table('otp_codes')->where('id', $row->id)->increment('attempts');
    return 'Invalid or expired code…';
}
DB::table('otp_codes')->where('email', $email)->delete();          // ④ single-use — consume on success
```

That's expiry + attempt-cap + timing-safe compare + single-use — exactly the four properties the concept section demanded. Only *after* this succeeds is the real user account created and logged in. The *same* OTP machinery is reused for **password reset** (`forgotPassword` → `verify` → `resetPassword`), so "reset my password" also requires proving you control the inbox.

> **Honest note about your code:** a comment in `verify()` says codes are "stored hashed," but the actual implementation stores the plain 6-digit code and compares it with `hash_equals`. The `sendOtp()` method even documents *why* plaintext is acceptable here: the code is single-use, expires in 10 minutes, is attempt-capped, deleted on use, and the route is rate-limited — so at-rest hashing added little while making the column width a correctness risk. The protection comes from the four runtime properties, not from hashing the code. (Worth fixing the stale comment someday, but the behaviour is sound.)

### User enumeration — a subtle leak your code closes

When you click "forgot password," many naïve sites say *"no account with that email"* for unknown emails and *"code sent"* for real ones. That difference lets an attacker discover **which emails are registered** (a privacy leak and a phishing target list). Your `forgotPassword()` deliberately shows the **same** message either way:

```php
// Always show same message to prevent user enumeration
return redirect()->route('verify.show')
    ->with('info', 'If that email is registered, a reset code has been sent.');
```

That's a real, professional-grade security habit baked into your project.

### RBAC — role as the single source of truth

Your `User` model defines three roles and enforces the "role is authoritative, never user-settable" rule:

```php
// app/Models/User.php
protected $fillable = ['name', 'email', 'password', 'email_verified_at'];
// NOTE: 'role' and 'is_admin' are intentionally NOT mass-assignable — privilege flags,
// set only by trusted code. 'is_admin' is DERIVED from 'role' on every save:
protected static function booted(): void {
    static::saving(fn (User $user) => $user->is_admin = in_array($user->role, ['admin','superadmin']));
}
public function isAdmin(): bool      { return in_array($this->role, ['admin','superadmin']); }
public function isSuperAdmin(): bool { return $this->role === 'superadmin'; }
```

Two lessons here: (1) `role` can't arrive from a web form because it's left out of `$fillable`; (2) the older `is_admin` flag is *derived* from `role` automatically, so the two can never contradict each other — one source of truth.

### Enforcement at both layers

**At the gate:** the route group `Route::middleware(['auth', 'admin'])->prefix('admin')` guards every admin URL. The `CheckAdmin` middleware sends non-admins away before any admin code runs.

**At the action:** the `AdminController::updateUserRole` method adds fine-grained checks so an admin can't escalate privileges — a non-superadmin cannot change a superadmin's role, nor grant the superadmin role to anyone. That's *privilege-escalation defence* exactly as the concept described.

### 📊 Diagram: OTP registration, end to end

```mermaid
sequenceDiagram
    participant B as Browser
    participant A as AuthController
    participant S as Session (server)
    participant DB as otp_codes table
    participant E as Email

    B->>A: POST /register (name, email, password)
    A->>A: validate; check email not taken
    A->>S: stash pending registration (NOT a real account yet)
    A->>A: hash a random 6-digit code's siblings; store code
    A->>DB: save OTP (expires in 10 min, attempts=0)
    A->>E: email the 6-digit code (sent synchronously)
    A-->>B: redirect to "enter your code" page

    B->>A: POST /verify-email (otp)
    A->>DB: lock latest unexpired code for this email
    alt code missing/expired
        A-->>B: "invalid or expired"
    else too many attempts
        A->>DB: delete codes
        A-->>B: "too many attempts, request new code"
    else wrong code
        A->>DB: attempts += 1
        A-->>B: "invalid code"
    else correct (timing-safe match)
        A->>DB: delete codes (single-use)
        A->>A: NOW create the real User, mark verified
        A->>S: log in, regenerate session id
        A-->>B: redirect to account (or /admin if admin)
    end
```

---

## ✅ Takeaways

1. **Authentication = who you are; authorization = what you may do.** You authenticate once and authorize every sensitive action.
2. Logins are remembered via **sessions** (server stores identity, browser holds a random cookie — your project's approach) or **tokens** (self-contained, for APIs). Regenerate the session ID at login.
3. **Never store passwords** — store a **salted, slow hash** (bcrypt). Slowness protects against mass guessing; your project uses bcrypt with extra rounds.
4. **OTP/MFA** adds "something you have." A trustworthy OTP is short-lived, single-use, attempt-capped, and compared in a **timing-safe** way — your project does all four.
5. **RBAC** authorizes by role; the role must be the single source of truth and **never settable by the user**. Enforce access **at the gate** (middleware) *and* **at the action** (fine-grained checks).
6. Small touches matter: **session regeneration** and **anti-enumeration messaging** are both present in your code and both are professional habits.

Next: the broader world of attacks and defences → [Chapter 4: Security](04-security.md)
