<script lang="ts">
    import AppLayout from '../layouts/AppLayout.svelte';

    interface Props {
        app_name?: string;
        php_version?: string;
        path?: string;
    }

    let { app_name = 'SahilPHP', php_version = 'unknown', path = '/' }: Props = $props();
</script>

<AppLayout appName={app_name} phpVersion={php_version}>
    <section class="not-found">
        <p class="not-found__code" aria-hidden="true">404</p>
        <h1>Page not found</h1>
        <p>
            Nothing is routed at <code>{path}</code>. The PHP router matched its final route and
            rendered this Svelte page with a real 404 response.
        </p>
        <a class="button button--primary" href="/">Back to home</a>
    </section>
</AppLayout>
