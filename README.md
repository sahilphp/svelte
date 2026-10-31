# SahilPHP Svelte starter-kit

A complete, independently installable SahilPHP application with a Svelte 5 +
TypeScript + Vite frontend. Controllers render Svelte pages with ordinary PHP
data; Vite handles development HMR and production builds.

## Requirements

- PHP 8.2+
- Composer 2
- Node.js 18+
- npm 9+

## Create or install

```bash
composer create-project sahilphp/starter-kit-svelte my-app
cd my-app
npm install
```

For a checkout that already exists:

```bash
composer install
cp .env.example .env
php bin/sahil key:generate
npm install
```

## Development

Run PHP and Vite in separate terminals:

```bash
npm run dev          # Vite on http://127.0.0.1:5173
php sahil start      # PHP on http://127.0.0.1:8000
```

Open `http://127.0.0.1:8000`. The PHP-rendered document loads
`@vite/client` and `src/main.ts` from the dev server, so Svelte components hot
reload. If Vite chooses another port, `public/hot` overrides
`frontend.dev_server`.

## Production build

```bash
npm run build
```

Vite writes `public/build/` and `public/build/.vite/manifest.json`. Point the
web server document root at `public/`. In production (`APP_ENV=production`)
the PHP renderer reads the manifest, recursively collects imported CSS,
verifies every referenced file and emits immutable script/style tags. No Vite
server is needed at runtime.

## Rendering from PHP

```php
use App\Frontend\Svelte;

final class HomeController
{
    public function response(): \Sahil\Http\Response
    {
        $data = [
            'app_name' => (string) $this->application->config()->get('app.name', 'SahilPHP'),
            'php_version' => PHP_VERSION,
        ];

        return Svelte::render('pages.home', $data);
    }
}
```

Page names map predictably:

| PHP page name | Svelte component sent to the browser | File |
| --- | --- | --- |
| `pages.home` | `Pages/Home.svelte` | `src/pages/Home.svelte` |
| `pages.notFound` | `Pages/NotFound.svelte` | `src/pages/NotFound.svelte` |
| `pages.dashboard.index` | `Pages/Dashboard/Index.svelte` | `src/pages/Dashboard/Index.svelte` |

The leading `pages` segment is optional. Every other segment is PascalCased.
A missing component fails with an exception naming the expected path.

`Svelte::render()` returns a normal `Sahil\Http\Response`; use `withStatus()`
or `withHeader()` for additional HTTP behaviour. The example 404 route does
exactly that.

### Data safety

- Data is JSON encoded with `JSON_HEX_TAG`, `JSON_HEX_AMP`, `JSON_HEX_APOS`
  and `JSON_HEX_QUOT`, so values such as `</script>` cannot escape the payload
  element.
- The payload lives in `<script type="application/json">`, not executable
  JavaScript.
- The component name, mount id, title, language and asset URLs are validated
  and HTML-escaped.
- Only JSON-serializable data can be passed; encoding failures throw a clear
  `SvelteRenderException`.

## Project structure

```
app/
  Frontend/            Svelte page resolution, payload, Vite assets, renderer
  Http/                Controllers and middleware
  Providers/           Frontend and route/kernel wiring
bootstrap/             Application and provider manifests
config/                app.php, frontend.php
public/                Front controller, .htaccess, public assets
routes/web.php         Route table
src/
  components/          Reusable Svelte components (Nav, Card, Alert, FormField)
  layouts/             Shared AppLayout
  lib/payload.ts       Payload and component-name helpers
  pages/               Svelte pages
  styles/global.css    Application styles
  main.ts              Client entry point (mount + fatal error UI)
tests/
  Feature/             HTTP integration and container/provider wiring
  Frontend/            Page resolution, payload rendering and Vite assets
  Support/             Isolated filesystem fixtures
  js/                  Vitest tests for helpers and component rendering
```

## Example application

- `/` — home page with PHP runtime data, features and getting-started code.
- `/example` — nested objects, lists and request metadata passed to Svelte.
- `/form` — form with client-side and PHP-side validation feedback.
- `/health` — JSON health endpoint.
- Any unknown path — Svelte not-found page with a real 404 status.

## Tests and checks

```bash
composer test       # PHPUnit: rendering, assets, routing, form validation
npm test            # Vitest: payload helpers and Svelte server rendering
npm run check       # svelte-check / TypeScript
npm run build       # production asset build
```

## Configuration

`config/frontend.php` controls the mount id, payload title key, language,
development mode, dev server URL, build directory/public path, manifest
location, entry point, asset verification and pages directory. Defaults are
safe for local development and production builds.
# starter-svelte
