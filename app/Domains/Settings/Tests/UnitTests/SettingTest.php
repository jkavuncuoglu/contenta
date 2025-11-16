<?php

declare(strict_types=1);

namespace App\Domains\Settings\Tests\UnitTests;

use App\Domains\Settings\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_setting_has_fillable_attributes(): void
    {
        // Act
        $setting = Setting::create([
            'group' => 'test',
            'key' => 'test_key',
            'value' => 'test_value',
            'type' => 'string',
            'description' => 'Test description',
            'autoload' => true,
        ]);

        // Assert
        $this->assertEquals('test', $setting->group);
        $this->assertEquals('test_key', $setting->key);
        $this->assertEquals('test_value', $setting->value);
        $this->assertEquals('string', $setting->type);
        $this->assertEquals('Test description', $setting->description);
        $this->assertTrue($setting->autoload);
    }

    public function test_value_attribute_casts_boolean(): void
    {
        // Arrange
        $setting = Setting::create([
            'group' => 'test',
            'key' => 'bool_key',
            'value' => '1',
            'type' => 'boolean',
        ]);

        // Act
        $value = $setting->value;

        // Assert
        $this->assertTrue($value);
    }

    public function test_value_attribute_casts_integer(): void
    {
        // Arrange
        $setting = Setting::create([
            'group' => 'test',
            'key' => 'int_key',
            'value' => '123',
            'type' => 'integer',
        ]);

        // Act
        $value = $setting->value;

        // Assert
        $this->assertSame(123, $value);
    }

    public function test_value_attribute_casts_float(): void
    {
        // Arrange
        $setting = Setting::create([
            'group' => 'test',
            'key' => 'float_key',
            'value' => '12.34',
            'type' => 'float',
        ]);

        // Act
        $value = $setting->value;

        // Assert
        $this->assertSame(12.34, $value);
    }

    public function test_value_attribute_casts_json(): void
    {
        // Arrange
        $setting = Setting::create([
            'group' => 'test',
            'key' => 'json_key',
            'value' => '{"key":"value"}',
            'type' => 'json',
        ]);

        // Act
        $value = $setting->value;

        // Assert
        $this->assertIsArray($value);
        $this->assertEquals(['key' => 'value'], $value);
    }

    public function test_value_attribute_sets_boolean(): void
    {
        // Arrange
        $setting = new Setting([
            'group' => 'test',
            'key' => 'bool_key',
            'type' => 'boolean',
        ]);

        // Act
        $setting->value = true;
        $setting->save();

        // Assert
        $this->assertDatabaseHas('settings', [
            'key' => 'bool_key',
            'value' => '1',
        ]);
    }

    public function test_value_attribute_sets_integer(): void
    {
        // Arrange
        $setting = new Setting([
            'group' => 'test',
            'key' => 'int_key',
            'type' => 'integer',
        ]);

        // Act
        $setting->value = 456;
        $setting->save();

        // Assert
        $this->assertDatabaseHas('settings', [
            'key' => 'int_key',
            'value' => '456',
        ]);
    }

    public function test_value_attribute_sets_json(): void
    {
        // Arrange
        $setting = new Setting([
            'group' => 'test',
            'key' => 'json_key',
            'type' => 'json',
        ]);

        // Act
        $setting->value = ['foo' => 'bar'];
        $setting->save();

        // Assert
        $this->assertDatabaseHas('settings', [
            'key' => 'json_key',
            'value' => '{"foo":"bar"}',
        ]);
    }

    public function test_get_retrieves_setting_value(): void
    {
        // Arrange
        Setting::create([
            'group' => 'test',
            'key' => 'test_key',
            'value' => 'test_value',
            'type' => 'string',
        ]);

        // Act
        $value = Setting::get('test', 'test_key');

        // Assert
        $this->assertEquals('test_value', $value);
    }

    public function test_get_returns_default_when_not_found(): void
    {
        // Act
        $value = Setting::get('test', 'nonexistent', 'default');

        // Assert
        $this->assertEquals('default', $value);
    }

    public function test_get_uses_cache(): void
    {
        // Arrange
        Setting::create([
            'group' => 'test',
            'key' => 'cached_key',
            'value' => 'cached_value',
            'type' => 'string',
        ]);

        // Act
        $value1 = Setting::get('test', 'cached_key');

        // Delete from database
        Setting::where('key', 'cached_key')->delete();

        // Should still get cached value
        $value2 = Setting::get('test', 'cached_key');

        // Assert
        $this->assertEquals('cached_value', $value1);
        $this->assertEquals('cached_value', $value2);
    }

    public function test_set_creates_or_updates_setting(): void
    {
        // Act
        Setting::set('test', 'new_key', 'new_value', 'string', 'Description');

        // Assert
        $this->assertDatabaseHas('settings', [
            'group' => 'test',
            'key' => 'new_key',
            'value' => 'new_value',
            'type' => 'string',
            'description' => 'Description',
        ]);
    }

    public function test_set_updates_existing_setting(): void
    {
        // Arrange
        Setting::create([
            'group' => 'test',
            'key' => 'existing_key',
            'value' => 'old_value',
            'type' => 'string',
        ]);

        // Act
        Setting::set('test', 'existing_key', 'new_value');

        // Assert
        $this->assertDatabaseHas('settings', [
            'key' => 'existing_key',
            'value' => 'new_value',
        ]);
        // Should only have one setting with this key
        $this->assertEquals(1, Setting::where('key', 'existing_key')->count());
    }

    public function test_set_clears_cache(): void
    {
        // Arrange
        Setting::create([
            'group' => 'test',
            'key' => 'cache_test',
            'value' => 'old_value',
            'type' => 'string',
        ]);

        // Prime cache
        Setting::get('test', 'cache_test');

        // Act
        Setting::set('test', 'cache_test', 'new_value');

        // Get fresh value
        $value = Setting::get('test', 'cache_test');

        // Assert
        $this->assertEquals('new_value', $value);
    }

    public function test_get_group_returns_all_settings_in_group(): void
    {
        // Arrange
        Setting::create(['group' => 'test', 'key' => 'key1', 'value' => 'value1', 'type' => 'string']);
        Setting::create(['group' => 'test', 'key' => 'key2', 'value' => 'value2', 'type' => 'string']);
        Setting::create(['group' => 'other', 'key' => 'key3', 'value' => 'value3', 'type' => 'string']);

        // Act
        $settings = Setting::getGroup('test');

        // Assert
        $this->assertCount(2, $settings);
        $this->assertArrayHasKey('key1', $settings);
        $this->assertArrayHasKey('key2', $settings);
        $this->assertEquals('value1', $settings['key1']['value']);
    }

    public function test_get_group_caches_results(): void
    {
        // Arrange
        Setting::create(['group' => 'test', 'key' => 'key1', 'value' => 'value1', 'type' => 'string']);

        // Act
        $settings1 = Setting::getGroup('test');
        Setting::where('key', 'key1')->delete();
        $settings2 = Setting::getGroup('test');

        // Assert
        $this->assertEquals($settings1, $settings2);
    }

    public function test_get_multiple_retrieves_multiple_settings(): void
    {
        // Arrange
        Setting::create(['group' => 'site', 'key' => 'name', 'value' => 'Site Name', 'type' => 'string']);
        Setting::create(['group' => 'site', 'key' => 'email', 'value' => 'site@example.com', 'type' => 'string']);
        Setting::create(['group' => 'app', 'key' => 'debug', 'value' => '1', 'type' => 'boolean']);

        // Act
        $settings = Setting::getMultiple([
            'site' => ['name', 'email'],
            'app' => 'debug',
        ]);

        // Assert
        $this->assertArrayHasKey('site', $settings);
        $this->assertArrayHasKey('app', $settings);
        $this->assertEquals('Site Name', $settings['site']['name']);
        $this->assertEquals('site@example.com', $settings['site']['email']);
    }

    public function test_saved_event_clears_cache(): void
    {
        // Arrange
        $setting = Setting::create([
            'group' => 'test',
            'key' => 'event_test',
            'value' => 'old_value',
            'type' => 'string',
        ]);

        // Prime cache
        Setting::get('test', 'event_test');

        // Act
        $setting->value = 'new_value';
        $setting->save();

        $value = Setting::get('test', 'event_test');

        // Assert
        $this->assertEquals('new_value', $value);
    }

    public function test_deleted_event_clears_cache(): void
    {
        // Arrange
        $setting = Setting::create([
            'group' => 'test',
            'key' => 'delete_test',
            'value' => 'value',
            'type' => 'string',
        ]);

        // Prime cache
        Setting::get('test', 'delete_test');

        // Act
        $setting->delete();
        $value = Setting::get('test', 'delete_test', 'default');

        // Assert
        $this->assertEquals('default', $value);
    }
}
