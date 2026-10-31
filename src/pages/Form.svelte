<script lang="ts">
    import AppLayout from '../layouts/AppLayout.svelte';
    import Alert from '../components/Alert.svelte';
    import Card from '../components/Card.svelte';
    import FormField from '../components/FormField.svelte';

    interface FormValues {
        name: string;
        email: string;
        message: string;
    }

    interface Props {
        app_name?: string;
        php_version?: string;
        values?: Partial<FormValues>;
        errors?: Record<string, string>;
        success?: string | null;
        status?: number;
    }

    let {
        app_name = 'SahilPHP',
        php_version = 'unknown',
        values = {},
        errors = {},
        success = null,
        status = 200,
    }: Props = $props();

    // The form is seeded once from the server response; subsequent edits are
    // client state and are submitted by the browser.
    // svelte-ignore state_referenced_locally
    let name = $state(values.name ?? '');
    // svelte-ignore state_referenced_locally
    let email = $state(values.email ?? '');
    // svelte-ignore state_referenced_locally
    let message = $state(values.message ?? '');
    let clientErrors = $state<Record<string, string>>({});

    const visibleErrors = $derived.by(() => {
        const merged: Record<string, string> = { ...errors };

        for (const [field, value] of Object.entries(clientErrors)) {
            if (value) {
                merged[field] = value;
            }
        }

        return merged;
    });

    const hasErrors = $derived(Object.keys(visibleErrors).length > 0);

    function validate(): boolean {
        const next: Record<string, string> = {};

        if (name.trim().length < 2 || name.trim().length > 80) {
            next.name = 'Enter a name between 2 and 80 characters.';
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim())) {
            next.email = 'Enter a valid email address.';
        }

        if (message.trim().length < 10 || message.trim().length > 2000) {
            next.message = 'Enter a message between 10 and 2000 characters.';
        }

        clientErrors = next;

        return Object.keys(next).length === 0;
    }

    function handleSubmit(event: SubmitEvent): void {
        if (!validate()) {
            event.preventDefault();
        }
    }
</script>

<AppLayout appName={app_name} phpVersion={php_version}>
    <section class="form-layout">
        <div class="form-layout__intro">
            <p class="eyebrow">Validation feedback</p>
            <h1>Contact form</h1>
            <p>
                Client-side checks run before submission; the same rules run again in
                <code>FormController</code>. If the server rejects the values, this page is
                rendered with a <strong>422</strong> status, the original values and per-field
                messages.
            </p>

            <ul class="checklist">
                <li>Progressive: the form posts normally without JavaScript.</li>
                <li>Server errors arrive as Svelte props, not injected markup.</li>
                <li>The response status is preserved by <code>Svelte::render()</code>.</li>
            </ul>
        </div>

        <Card title="Send a message" subtitle={`Response status: ${status}`}>
            {#if success}
                <Alert variant="success" title="Message accepted">
                    {success}
                </Alert>
            {/if}

            {#if hasErrors}
                <Alert variant="danger" title="Please fix the highlighted fields">
                    {Object.keys(visibleErrors).length} field(s) need attention. Client-side and
                    server-side errors are shown together.
                </Alert>
            {/if}

            <form class="form" method="post" action="/form" novalidate onsubmit={handleSubmit}>
                <FormField
                    id="field-name"
                    name="name"
                    label="Name"
                    bind:value={name}
                    placeholder="Ada Lovelace"
                    autocomplete="name"
                    error={visibleErrors.name}
                    required
                />

                <FormField
                    id="field-email"
                    name="email"
                    label="Email"
                    type="email"
                    bind:value={email}
                    placeholder="you@example.com"
                    autocomplete="email"
                    error={visibleErrors.email}
                    required
                />

                <FormField
                    id="field-message"
                    name="message"
                    label="Message"
                    bind:value={message}
                    placeholder="What are you building?"
                    error={visibleErrors.message}
                    textarea
                    rows={7}
                    required
                />

                <button class="button button--primary" type="submit">Send message</button>
            </form>
        </Card>
    </section>
</AppLayout>
