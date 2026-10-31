import { render } from 'svelte/server';
import { describe, expect, it } from 'vitest';
import Home from '../../src/pages/Home.svelte';
import Form from '../../src/pages/Form.svelte';
import NotFound from '../../src/pages/NotFound.svelte';

describe('Home page', () => {
    it('renders PHP props and the navigation', () => {
        const { body } = render(Home, {
            props: {
                app_name: 'Demo App',
                php_version: '8.3.0',
                environment: 'production',
                asset_mode: 'Production build',
                framework_version: 'SahilPHP 1.0',
                frontend: 'Svelte 5',
                rendered_at: '2026-01-01T00:00:00+00:00',
                features: [
                    { title: 'Typed props', description: 'Data crosses the boundary safely.' },
                ],
            },
        });

        expect(body).toContain('Demo App');
        expect(body).toContain('8.3.0');
        expect(body).toContain('Production build');
        expect(body).toContain('Typed props');
        expect(body).toContain('href="/example"');
        expect(body).toContain('href="/form"');
        expect(body).toContain('Rendered by PHP at 2026-01-01T00:00:00+00:00.');
    });

    it('renders sensible defaults without props', () => {
        const { body } = render(Home);

        expect(body).toContain('SahilPHP');
        expect(body).toContain('A PHP application with a modern Svelte frontend.');
        expect(body).not.toContain('Rendered by PHP at');
    });
});

describe('Form page', () => {
    it('renders server errors and the submitted values', () => {
        const { body } = render(Form, {
            props: {
                app_name: 'Demo App',
                values: { name: 'A', email: 'not-an-email', message: 'short' },
                errors: {
                    name: 'Enter a name between 2 and 80 characters.',
                    email: 'Enter a valid email address.',
                    message: 'Enter a message between 10 and 2000 characters.',
                },
                status: 422,
            },
        });

        expect(body).toContain('Response status: 422');
        expect(body).toContain('Enter a valid email address.');
        expect(body).toContain('aria-invalid="true"');
        expect(body).toContain('Please fix the highlighted fields');
        expect(body).toContain('value="A"');
    });

    it('renders the success state', () => {
        const { body } = render(Form, {
            props: {
                values: {
                    name: 'Ada Lovelace',
                    email: 'ada@example.com',
                    message: 'A valid message.',
                },
                success: 'Thanks — your message passed validation.',
            },
        });

        expect(body).toContain('Message accepted');
        expect(body).toContain('Thanks — your message passed validation.');
        expect(body).not.toContain('Please fix the highlighted fields');
    });
});

describe('NotFound page', () => {
    it('escapes the requested path', () => {
        const { body } = render(NotFound, {
            props: { path: '/missing/<script>alert(1)</script>' },
        });

        expect(body).toContain('Page not found');
        expect(body).toContain('&lt;script>alert(1)&lt;/script>');
        expect(body).not.toContain('<script>alert(1)</script>');
    });
});
