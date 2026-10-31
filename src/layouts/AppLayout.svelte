<script lang="ts">
    import type { Snippet } from 'svelte';
    import Nav from '../components/Nav.svelte';

    interface Props {
        appName?: string;
        phpVersion?: string;
        children: Snippet;
    }

    let { appName = 'SahilPHP', phpVersion = 'unknown', children }: Props = $props();

    const year = new Date().getFullYear();
</script>

<div class="app-shell">
    <Nav {appName} />

    <main class="app-main">
        {@render children()}
    </main>

    <footer class="app-footer">
        <div class="app-footer__inner">
            <p>{appName} — built with SahilPHP and Svelte</p>
            <p class="app-footer__meta">PHP {phpVersion} · {year}</p>
        </div>
    </footer>
</div>
