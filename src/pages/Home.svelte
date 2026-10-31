<script lang="ts">
    import AppLayout from '../layouts/AppLayout.svelte';
    import Card from '../components/Card.svelte';

    interface Feature {
        title: string;
        description: string;
    }

    interface Props {
        app_name?: string;
        php_version?: string;
        environment?: string;
        framework_version?: string;
        frontend?: string;
        asset_mode?: string;
        rendered_at?: string;
        features?: Feature[];
    }

    let {
        app_name = 'SahilPHP',
        php_version = 'unknown',
        environment = 'development',
        framework_version = 'SahilPHP',
        frontend = 'Svelte 5 + TypeScript + Vite',
        asset_mode = 'unknown',
        rendered_at = '',
        features = [],
    }: Props = $props();

    const snippet = `$data = [
    'app_name' => (string) $this->application->config()->get(
        'app.name',
        'SahilPHP'
    ),
    'php_version' => PHP_VERSION,
];

return Svelte::render('pages.home', $data);`;
</script>

<AppLayout appName={app_name} phpVersion={php_version}>
    <section class="hero">
        <p class="eyebrow">SahilPHP + Svelte</p>
        <h1>A PHP application with a modern Svelte frontend.</h1>
        <p class="hero__lede">
            This page is a Svelte component mounted by a SahilPHP controller. The controller
            passes plain PHP data; the renderer serializes it safely and Vite supplies the
            development client or production assets.
        </p>

        <div class="hero__actions">
            <a class="button button--primary" href="/example">See PHP data in Svelte</a>
            <a class="button" href="/form">Try the validated form</a>
        </div>
    </section>

    <section class="section" aria-labelledby="runtime-heading">
        <div class="section__heading">
            <h2 id="runtime-heading">Runtime</h2>
            <p>Values below were prepared by <code>HomeController</code>.</p>
        </div>

        <dl class="stats">
            <div class="stat">
                <dt>Application</dt>
                <dd>{app_name}</dd>
            </div>
            <div class="stat">
                <dt>PHP</dt>
                <dd>{php_version}</dd>
            </div>
            <div class="stat">
                <dt>Environment</dt>
                <dd>{environment}</dd>
            </div>
            <div class="stat">
                <dt>Assets</dt>
                <dd>{asset_mode}</dd>
            </div>
            <div class="stat">
                <dt>Frontend</dt>
                <dd>{frontend}</dd>
            </div>
            <div class="stat">
                <dt>Framework</dt>
                <dd>{framework_version}</dd>
            </div>
        </dl>
    </section>

    <section class="section" aria-labelledby="features-heading">
        <div class="section__heading">
            <h2 id="features-heading">How the integration works</h2>
            <p>Each piece stays small and replaceable.</p>
        </div>

        <div class="grid grid--two">
            {#each features as feature}
                <Card title={feature.title}>
                    <p>{feature.description}</p>
                </Card>
            {/each}
        </div>
    </section>

    <section class="section grid grid--two" aria-label="Getting started">
        <Card title="Render from a controller" subtitle="One call returns a normal HTTP response">
            <pre class="code"><code>{snippet}</code></pre>
        </Card>

        <Card title="Run it locally" subtitle="PHP and Vite together">
            <ol class="steps">
                <li><code>composer install</code></li>
                <li><code>npm install</code></li>
                <li><code>npm run dev</code></li>
                <li><code>php sahil start</code></li>
            </ol>
            <p class="muted">Production: <code>npm run build</code>, then point the web server at <code>public/</code>.</p>
        </Card>
    </section>

    {#if rendered_at}
        <p class="render-note">Rendered by PHP at {rendered_at}.</p>
    {/if}
</AppLayout>
