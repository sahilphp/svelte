<script lang="ts">
    import type { Snippet } from 'svelte';

    type Variant = 'info' | 'success' | 'warning' | 'danger';

    interface Props {
        variant?: Variant;
        title?: string;
        children: Snippet;
    }

    let { variant = 'info', title, children }: Props = $props();
</script>

<div class="alert alert--{variant}" role={variant === 'danger' ? 'alert' : 'status'}>
    {#if title}
        <p class="alert__title">{title}</p>
    {/if}
    <div class="alert__message">
        {@render children()}
    </div>
</div>
