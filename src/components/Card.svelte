<script lang="ts">
    import type { Snippet } from 'svelte';

    interface Props {
        title?: string;
        subtitle?: string;
        children: Snippet;
    }

    let { title, subtitle, children }: Props = $props();
</script>

<section class="card">
    {#if title || subtitle}
        <header class="card__header">
            {#if title}
                <h2 class="card__title">{title}</h2>
            {/if}
            {#if subtitle}
                <p class="card__subtitle">{subtitle}</p>
            {/if}
        </header>
    {/if}

    <div class="card__body">
        {@render children()}
    </div>
</section>
