<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Class SitemapTest.
 *
 * @author annejan@badge.team
 */
class SitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (file_exists(public_path('sitemap.xml'))) {
            rename(public_path('sitemap.xml'), sys_get_temp_dir().'/hatchery_sitemap.tmp');
        }
    }

    /**
     * @throws \Throwable
     */
    protected function tearDown(): void
    {
        if (file_exists(sys_get_temp_dir().'/hatchery_sitemap.tmp')) {
            rename(sys_get_temp_dir().'/hatchery_sitemap.tmp', public_path('sitemap.xml'));
        }
        parent::tearDown();
    }

    /**
     * Make sure we have homepage and /projects.
     */
    public function testSitemapEmpty(): void
    {
        Artisan::call('sitemap:generate');
        $response = Artisan::output();
        $this->assertEquals('', $response);
        $dom = new \DOMDocument();
        $dom->load(public_path('sitemap.xml'));
        $this->assertCount(3, $dom->getElementsByTagName('url'));
    }

    /**
     * Make sure we have projects added.
     */
    public function testSitemapProject(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        factory(Project::class)->create();
        Artisan::call('sitemap:generate');
        $dom = new \DOMDocument();
        $dom->load(public_path('sitemap.xml'));
        $this->assertCount(4, $dom->getElementsByTagName('url'));
    }

    /**
     * Make sure we have badges added.
     */
    public function testSitemapBadge(): void
    {
        $user = factory(User::class)->create();
        $this->be($user);
        factory(Badge::class)->create();
        Artisan::call('sitemap:generate');
        $dom = new \DOMDocument();
        $dom->load(public_path('sitemap.xml'));
        $this->assertCount(4, $dom->getElementsByTagName('url'));
    }
}
