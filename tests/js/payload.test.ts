import { describe, expect, it } from 'vitest';
import { componentModuleKey, parsePayload } from '../../src/lib/payload';

describe('parsePayload', () => {
    it('returns an empty object for missing or blank payloads', () => {
        expect(parsePayload(null)).toEqual({});
        expect(parsePayload(undefined)).toEqual({});
        expect(parsePayload('   ')).toEqual({});
    });

    it('parses an object payload with nested values', () => {
        expect(
            parsePayload(
                JSON.stringify({
                    title: 'Home',
                    profile: { name: 'Ada', skills: ['PHP', 'Svelte'] },
                    stats: [{ label: 'Projects', value: '12' }],
                    success: null,
                    status: 200,
                }),
            ),
        ).toEqual({
            title: 'Home',
            profile: { name: 'Ada', skills: ['PHP', 'Svelte'] },
            stats: [{ label: 'Projects', value: '12' }],
            success: null,
            status: 200,
        });
    });

    it('reports invalid JSON instead of silently falling back', () => {
        expect(() => parsePayload('{not json')).toThrow(/not valid JSON/);
    });

    it.each([
        ['null', 'null'],
        ['a list', '[]'],
        ['a string', '"hello"'],
        ['a number', '42'],
    ])('rejects %s as the payload', (_label, raw) => {
        expect(() => parsePayload(raw)).toThrow(/must be a JSON object/);
    });
});

describe('componentModuleKey', () => {
    it('maps the server component name to the import.meta.glob key', () => {
        expect(componentModuleKey('Pages/Home.svelte')).toBe('./pages/Home.svelte');
    });

    it('supports nested pages', () => {
        expect(componentModuleKey('Pages/Dashboard/Index.svelte')).toBe(
            './pages/Dashboard/Index.svelte',
        );
    });

    it('rejects a missing component name', () => {
        expect(() => componentModuleKey(null)).toThrow(/missing its data-component/);
        expect(() => componentModuleKey('')).toThrow(/missing its data-component/);
    });

    it.each([
        'pages/Home.svelte',
        'Pages/Home',
        'Pages/../Home.svelte',
        'Pages/Home.svelte?raw',
        'Pages//Home.svelte',
        'Pages/Home.vue',
    ])('rejects the malformed component name %s', (component) => {
        expect(() => componentModuleKey(component)).toThrow(/Invalid Svelte component name/);
    });
});
