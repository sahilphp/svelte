<script lang="ts">
    import { onMount } from 'svelte';

    interface Props {
        appName?: string;
    }

    let { appName = 'SahilPHP' }: Props = $props();

    const links = [
        { href: '/', label: 'Home' },
        { href: '/example', label: 'PHP data' },
        { href: '/form', label: 'Form' },
    ];

    let path = $state('/');

    onMount(() => {
        path = window.location.pathname;
    });

    function isActive(href: string): boolean {
        return href === '/' ? path === '/' : path === href || path.startsWith(`${href}/`);
    }
</script>

<header class="site-header">
    <div class="site-header__inner">
        <a class="brand" href="/" aria-label={`${appName} home`}>
            <span class="brand__mark" aria-hidden="true">S</span>
            <span class="brand__text">{appName}</span>
        </a>

        <nav class="site-nav" aria-label="Main navigation">
            {#each links as link}
                <a
                    class:active={isActive(link.href)}
                    href={link.href}
                    aria-current={isActive(link.href) ? 'page' : undefined}
                >
                    {link.label}
                </a>
            {/each}
        </nav>
    </div>
</header>
