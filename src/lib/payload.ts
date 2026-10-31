export type PageProps = Record<string, unknown>;

/**
 * Parse the JSON payload emitted by the PHP renderer.
 *
 * The payload is data, never executable code: PHP escapes it and the browser
 * reads it from a `type="application/json"` element. Invalid or non-object
 * payloads fail loudly instead of mounting a component with surprising props.
 */
export function parsePayload(raw: string | null | undefined): PageProps {
    if (raw === null || raw === undefined || raw.trim() === '') {
        return {};
    }

    let parsed: unknown;

    try {
        parsed = JSON.parse(raw);
    } catch (error) {
        throw new Error(
            `The Svelte page payload is not valid JSON: ${
                error instanceof Error ? error.message : String(error)
            }`,
        );
    }

    if (parsed === null || typeof parsed !== 'object' || Array.isArray(parsed)) {
        throw new Error('The Svelte page payload must be a JSON object.');
    }

    return parsed as PageProps;
}

/**
 * Convert the server's `Pages/Home.svelte` component name into the key used
 * by Vite's `import.meta.glob` result: `./pages/Home.svelte`.
 */
export function componentModuleKey(component: string | null | undefined): string {
    if (component === null || component === undefined || component.trim() === '') {
        throw new Error('The Svelte mount element is missing its data-component attribute.');
    }

    const match = /^Pages\/([A-Za-z0-9_-]+(?:\/[A-Za-z0-9_-]+)*)\.svelte$/.exec(component);

    if (match === null) {
        throw new Error(
            `Invalid Svelte component name [${component}]. Expected a name like Pages/Home.svelte.`,
        );
    }

    return `./pages/${match[1]}.svelte`;
}
