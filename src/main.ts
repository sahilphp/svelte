import './styles/global.css';

import { mount } from 'svelte';
import type { Component } from 'svelte';
import { componentModuleKey, parsePayload } from './lib/payload';

type PageModule = {
    default: Component;
};

const pages = import.meta.glob<PageModule>('./pages/**/*.svelte');

async function boot(): Promise<void> {
    const mountId = document.body.dataset.mountId ?? 'app';
    const target = document.getElementById(mountId);

    if (target === null) {
        showFatal(
            document.body,
            `The Svelte mount element (#${mountId}) is missing from the document.`,
        );
        return;
    }

    try {
        const script = document.getElementById(`${mountId}-props`);
        const props = parsePayload(script?.textContent);
        const key = componentModuleKey(target.dataset.component);
        const loader = pages[key];

        if (loader === undefined) {
            throw new Error(
                `No Svelte page module was registered for [${target.dataset.component}]. ` +
                    `Available pages: ${Object.keys(pages).join(', ') || 'none'}.`,
            );
        }

        const module = await loader();
        mount(module.default, { target, props });
    } catch (error) {
        showFatal(target, error);
    }
}

function showFatal(target: HTMLElement, error: unknown): void {
    const message = error instanceof Error ? error.message : String(error);

    target.innerHTML = '';

    const panel = document.createElement('section');
    panel.className = 'boot-error';
    panel.setAttribute('role', 'alert');

    const heading = document.createElement('h1');
    heading.textContent = 'This page could not start';

    const detail = document.createElement('pre');
    detail.textContent = message;

    panel.append(heading, detail);
    target.append(panel);

    console.error('[SahilPHP/Svelte]', error);
}

void boot();
