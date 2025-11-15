<?php

namespace App\Domains\Plugins\Tests\Unit;

use App\Domains\Plugins\Models\Plugin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PluginTest extends TestCase
{
    use RefreshDatabase;

    public function test_plugin_has_type_constants(): void
    {
        $this->assertEquals('frontend', Plugin::TYPE_FRONTEND);
        $this->assertEquals('admin', Plugin::TYPE_ADMIN);
        $this->assertEquals('universal', Plugin::TYPE_UNIVERSAL);
    }

    public function test_frontend_plugin_should_load_in_frontend(): void
    {
        $plugin = Plugin::factory()->create([
            'plugin_type' => Plugin::TYPE_FRONTEND,
        ]);

        $this->assertTrue($plugin->shouldLoadInFrontend());
        $this->assertFalse($plugin->shouldLoadInAdmin());
    }

    public function test_admin_plugin_should_load_in_admin(): void
    {
        $plugin = Plugin::factory()->create([
            'plugin_type' => Plugin::TYPE_ADMIN,
        ]);

        $this->assertFalse($plugin->shouldLoadInFrontend());
        $this->assertTrue($plugin->shouldLoadInAdmin());
    }

    public function test_universal_plugin_should_load_everywhere(): void
    {
        $plugin = Plugin::factory()->create([
            'plugin_type' => Plugin::TYPE_UNIVERSAL,
        ]);

        $this->assertTrue($plugin->shouldLoadInFrontend());
        $this->assertTrue($plugin->shouldLoadInAdmin());
    }

    public function test_plugin_type_check_methods(): void
    {
        $frontendPlugin = Plugin::factory()->create([
            'plugin_type' => Plugin::TYPE_FRONTEND,
        ]);
        $adminPlugin = Plugin::factory()->create([
            'plugin_type' => Plugin::TYPE_ADMIN,
        ]);
        $universalPlugin = Plugin::factory()->create([
            'plugin_type' => Plugin::TYPE_UNIVERSAL,
        ]);

        // Frontend plugin
        $this->assertTrue($frontendPlugin->isFrontendOnly());
        $this->assertFalse($frontendPlugin->isAdminOnly());
        $this->assertFalse($frontendPlugin->isUniversal());

        // Admin plugin
        $this->assertFalse($adminPlugin->isFrontendOnly());
        $this->assertTrue($adminPlugin->isAdminOnly());
        $this->assertFalse($adminPlugin->isUniversal());

        // Universal plugin
        $this->assertFalse($universalPlugin->isFrontendOnly());
        $this->assertFalse($universalPlugin->isAdminOnly());
        $this->assertTrue($universalPlugin->isUniversal());
    }

    public function test_plugin_defaults_to_universal_type(): void
    {
        $plugin = Plugin::factory()->create();

        $this->assertEquals(Plugin::TYPE_UNIVERSAL, $plugin->plugin_type);
        $this->assertTrue($plugin->isUniversal());
    }
}
