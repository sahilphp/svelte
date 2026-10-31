<script lang="ts">
    import AppLayout from '../layouts/AppLayout.svelte';
    import Card from '../components/Card.svelte';

    interface Profile {
        name: string;
        role: string;
        location: string;
        summary: string;
        skills: string[];
    }

    interface Stat {
        label: string;
        value: string;
    }

    interface RequestInfo {
        method: string;
        path: string;
        query: Record<string, string>;
        server_time: string;
    }

    interface Props {
        app_name?: string;
        php_version?: string;
        profile?: Profile;
        stats?: Stat[];
        request?: RequestInfo;
    }

    const fallbackProfile: Profile = {
        name: 'Unknown',
        role: 'Not provided',
        location: 'Not provided',
        summary: 'No profile data was passed to this page.',
        skills: [],
    };

    const fallbackRequest: RequestInfo = {
        method: 'GET',
        path: '/example',
        query: {},
        server_time: '',
    };

    let {
        app_name = 'SahilPHP',
        php_version = 'unknown',
        profile = fallbackProfile,
        stats = [],
        request = fallbackRequest,
    }: Props = $props();

    const requestJson = $derived(JSON.stringify(request, null, 2));
</script>

<AppLayout appName={app_name} phpVersion={php_version}>
    <section class="hero hero--compact">
        <p class="eyebrow">PHP → JSON → Svelte props</p>
        <h1>One payload, ordinary components.</h1>
        <p class="hero__lede">
            The controller builds nested arrays and lists. SahilPHP serializes them into the
            document; <code>src/main.ts</code> reads the payload and mounts the page with those
            values as props.
        </p>
    </section>

    <section class="section grid grid--two" aria-label="Profile and request data">
        <Card title={profile.name} subtitle={profile.role}>
            <p>{profile.summary}</p>
            <dl class="details">
                <div>
                    <dt>Location</dt>
                    <dd>{profile.location}</dd>
                </div>
                <div>
                    <dt>Skills</dt>
                    <dd>
                        <ul class="tags">
                            {#each profile.skills as skill}
                                <li>{skill}</li>
                            {/each}
                        </ul>
                    </dd>
                </div>
            </dl>
        </Card>

        <Card title="Request metadata" subtitle="Captured by PHP while handling this request">
            <dl class="stats stats--compact">
                <div class="stat">
                    <dt>Method</dt>
                    <dd>{request.method}</dd>
                </div>
                <div class="stat">
                    <dt>Path</dt>
                    <dd>{request.path}</dd>
                </div>
                {#each stats as item}
                    <div class="stat">
                        <dt>{item.label}</dt>
                        <dd>{item.value}</dd>
                    </div>
                {/each}
            </dl>
        </Card>
    </section>

    <section class="section" aria-labelledby="payload-heading">
        <div class="section__heading">
            <h2 id="payload-heading">The payload the component received</h2>
            <p>Inspect the exact shape: strings, objects and lists survive the round trip.</p>
        </div>

        <pre class="code code--tall"><code>{requestJson}</code></pre>
    </section>

    <p class="render-note">Server time: {request.server_time}</p>
</AppLayout>
