<?php

declare(strict_types=1);

namespace Tests\Frontend;

use App\Frontend\SveltePage;
use App\Frontend\SveltePageNotFoundException;
use PHPUnit\Framework\TestCase;
use Tests\Support\TempProject;

final class SveltePageTest extends TestCase
{
    private string $base;

    protected function setUp(): void
    {
        $this->base = TempProject::create();
    }

    protected function tearDown(): void
    {
        TempProject::remove($this->base);
    }

    public function test_pages_home_maps_to_pages_home_svelte(): void
    {
        $page = SveltePage::fromName('pages.home', $this->base.'/src/pages');

        self::assertSame('pages.home', $page->name());
        self::assertSame('Pages/Home.svelte', $page->component());
        self::assertSame($this->base.'/src/pages/Home.svelte', $page->sourcePath());
    }

    public function test_nested_page_names_map_to_nested_component_directories(): void
    {
        $page = SveltePage::fromName('pages.dashboard.index', $this->base.'/src/pages');

        self::assertSame('Pages/Dashboard/Index.svelte', $page->component());
        self::assertSame($this->base.'/src/pages/Dashboard/Index.svelte', $page->sourcePath());
    }

    public function test_slashes_and_the_optional_pages_prefix_are_supported(): void
    {
        $page = SveltePage::fromName('dashboard/index', $this->base.'/src/pages');

        self::assertSame('Pages/Dashboard/Index.svelte', $page->component());
    }

    public function test_segments_are_pascal_cased(): void
    {
        TempProject::writePage($this->base, 'Account/ResetPassword');

        $page = SveltePage::fromName('pages.account.reset-password', $this->base.'/src/pages');

        self::assertSame('Pages/Account/ResetPassword.svelte', $page->component());
    }

    public function test_missing_component_names_the_expected_path(): void
    {
        $this->expectException(SveltePageNotFoundException::class);
        $this->expectExceptionMessage('Expected component');
        $this->expectExceptionMessage('src/pages/Missing.svelte');

        SveltePage::fromName('pages.missing', $this->base.'/src/pages');
    }

    public function test_path_traversal_is_rejected(): void
    {
        $this->expectException(SveltePageNotFoundException::class);
        $this->expectExceptionMessage('Invalid Svelte page name');

        SveltePage::fromName('pages../secret', $this->base.'/src/pages');
    }

    public function test_empty_or_malformed_names_are_rejected(): void
    {
        $this->expectException(SveltePageNotFoundException::class);
        $this->expectExceptionMessage('Invalid Svelte page name');

        SveltePage::fromName('pages.', $this->base.'/src/pages');
    }
}
