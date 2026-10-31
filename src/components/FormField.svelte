<script lang="ts">
    import type { HTMLInputAttributes } from 'svelte/elements';

    interface Props {
        id: string;
        name: string;
        label: string;
        value?: string;
        type?: 'text' | 'email' | 'password' | 'url' | 'tel';
        placeholder?: string;
        autocomplete?: HTMLInputAttributes['autocomplete'];
        error?: string;
        required?: boolean;
        textarea?: boolean;
        rows?: number;
    }

    let {
        id,
        name,
        label,
        value = $bindable(''),
        type = 'text',
        placeholder = '',
        autocomplete = 'off',
        error,
        required = false,
        textarea = false,
        rows = 5,
    }: Props = $props();

    const errorId = $derived(`${id}-error`);
</script>

<div class:has-error={Boolean(error)} class="field">
    <label class="field__label" for={id}>{label}</label>

    {#if textarea}
        <textarea
            class="field__input field__input--textarea"
            {id}
            {name}
            {placeholder}
            {required}
            {rows}
            bind:value
            aria-invalid={error ? 'true' : undefined}
            aria-describedby={error ? errorId : undefined}
        ></textarea>
    {:else}
        <input
            class="field__input"
            {id}
            {name}
            {type}
            {placeholder}
            {autocomplete}
            {required}
            bind:value
            aria-invalid={error ? 'true' : undefined}
            aria-describedby={error ? errorId : undefined}
        />
    {/if}

    {#if error}
        <p class="field__error" id={errorId}>{error}</p>
    {/if}
</div>
