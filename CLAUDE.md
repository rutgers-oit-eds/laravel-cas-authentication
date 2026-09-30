# CLAUDE.md

## Project

`rutgers-oit-eds/laravel-cas-authentication` — a Laravel package wrapping phpCAS (`apereo/phpcas ~1.6.2`). It registers a custom `cas` **auth guard** (not middleware), so apps use the normal Laravel auth API (`Auth::user()`, `auth` middleware) while CAS performs authentication. Auto-discovered via `extra.laravel` (provider + `Cas` facade).

## Layout

- `src/Rutgers/Cas/` — PSR-4 root for `Rutgers\Cas\`
  - `CasServiceProvider` — binds `cas` singleton (`CasManager` from `config('cas')`), `Auth::extend('cas', ...)` creating `CasGuard`, loads routes/views, publishes tags `laravel-cas` (config + views) and `laravel-cas-authconfig` (replacement `auth.php`, needs `--force`).
  - `CasManager` — configures phpCAS statically in its constructor (logger, session cookie, client/proxy, SAML vs CAS version, cert validation, login/logout URLs). Supports `cas_masquerade` dev mode that bypasses CAS. `__call` proxies unknown methods to `phpCAS`.
  - `CasGuard` — implements `Guard`. `attempt()` → `Cas::authenticate()` → `login($netid)` → `provider->retrieveById()`; throws `CasAuthorizationException` if the CAS user isn't in the app's user table. `user()` returns null unless `Cas::isAuthenticated()`.
  - `CasLoginController` — `GET /login`, `POST /logout` (app logout only), `GET /auth/sso_logout` (CAS logout).
  - `Traits/UsesCasAuthentication` — sets auth identifier name to `cas_username`.
  - `Events/Login` — package's own login event (Laravel's `Illuminate\Auth\Events\Login` is not fired).
  - `Facades/Cas`, `Exceptions/CasAuthorizationException`.
- `src/config/` — `cas.php` (env-driven), `auth.php` (sample auth config).
- `src/routes/cas_routes.php`, `src/views/` (`loggedout`, `casUserNotAuthorized`, namespace `laravel-cas::`).
- `workbench/` — Orchestra Testbench workbench app.

## Commands

```bash
composer install
composer test            # or: vendor/bin/phpunit
```

## Testing

- PHPUnit 11 + Orchestra Testbench. Strict config: `failOnWarning`, `failOnRisky`, `requireCoverageMetadata` — every test class needs a `#[CoversClass(...)]` attribute.
- phpCAS is static and needs a CAS server, so tests avoid it:
  - `tests/Unit/CasManagerTest.php` uses a `TestableCasManager` subclass that skips phpCAS setup.
  - `tests/Feature/CasGuardTest.php` mocks `UserProvider`/`Session` and swaps the `Cas` facade with a mocked `CasManager` (clear with `Cas::clearResolvedInstances()` in tearDown).
- Test method naming: `test_snake_case_description`.

## CI

- `.github/workflows/tests.yml` — matrix PHP 8.2–8.5 × Laravel 12 (testbench ^10) / 13 (testbench ^11).
- `.github/workflows/release.yml` — on `v*` tag push, runs tests and creates a GitHub release.

## Style

Match existing code; note `CasManager` uses tabs and WordPress-style spacing (`foo( $bar )`) while other files use 4-space PSR style.

## Known issues (not yet fixed)

- `CasGuard::__construct` uses implicit nullable `Request $request = null` (deprecated in PHP 8.4+).
- `CasManager` calls `env('APP_DOMAIN')` / `env('HTTPS_ONLY_COOKIES')` at runtime — returns null under `config:cache`.
- `config/cas.php` reads `config('app.url')` — load-order dependent.
- `CasManager::logout()` log message is inverted and the method calls `exit`.
- `fireLoginEvent` / `fireAttemptEvent` are never called; `validate()` returns `null`.
- composer allows `illuminate/support` ^8+, but dev deps/CI only cover Laravel 11+ (CI: 12–13).
