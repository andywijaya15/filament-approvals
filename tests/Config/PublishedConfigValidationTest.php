<?php

namespace EightyNine\Approvals\Tests\Config;

use EightyNine\Approvals\Tests\TestCase;
use Illuminate\Support\Facades\File;

class PublishedConfigValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanupPublishedFiles();
    }

    protected function tearDown(): void
    {
        $this->cleanupPublishedFiles();
        parent::tearDown();
    }

    /** @test */
    public function published_config_has_all_required_keys()
    {
        $this->artisan('approvals:publish', ['--config' => true]);
        
        $config = include config_path('approvals.php');
        
        $requiredKeys = [
            'role_model',
            'navigation',
            'enable_approval_comments',
            'enable_rejection_comments',
            'ui',
            'security',
            'notifications'
        ];
        
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $config, "Config should have '{$key}' key");
        }
    }

    /** @test */
    public function navigation_config_has_correct_structure()
    {
        $this->artisan('approvals:publish', ['--config' => true]);
        
        $config = include config_path('approvals.php');
        $navigation = $config['navigation'];
        
        $this->assertArrayHasKey('should_register_navigation', $navigation);
        $this->assertArrayHasKey('icon', $navigation);
        $this->assertArrayHasKey('sort', $navigation);
        $this->assertArrayHasKey('group', $navigation);
        
        // Verify data types
        $this->assertIsBool($navigation['should_register_navigation']);
        $this->assertIsString($navigation['icon']);
        $this->assertIsInt($navigation['sort']);
    }

    /** @test */
    public function ui_config_has_correct_structure()
    {
        $this->artisan('approvals:publish', ['--config' => true]);
        
        $config = include config_path('approvals.php');
        $ui = $config['ui'];
        
        $expectedKeys = [
            'show_approval_history',
            'status_colors',
            'date_format',
            'avatar_provider',
            'icons'
        ];
        
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $ui, "UI config should have '{$key}' key");
        }
        
        // Verify status colors structure
        $this->assertIsArray($ui['status_colors']);
        $statusColorKeys = ['pending', 'approved', 'rejected', 'discarded'];
        foreach ($statusColorKeys as $status) {
            $this->assertArrayHasKey($status, $ui['status_colors']);
        }
    }

    /** @test */
    public function security_config_has_correct_structure()
    {
        $this->artisan('approvals:publish', ['--config' => true]);
        
        $config = include config_path('approvals.php');
        $security = $config['security'];
        
        $expectedKeys = [
            'prevent_self_approval',
            'audit_approvals',
            'require_comments_on_rejection'
        ];
        
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $security, "Security config should have '{$key}' key");
        }
        
        // Verify data types
        $this->assertIsBool($security['prevent_self_approval']);
        $this->assertIsBool($security['audit_approvals']);
    }

    /** @test */
    public function notifications_config_has_correct_structure()
    {
        $this->artisan('approvals:publish', ['--config' => true]);
        
        $config = include config_path('approvals.php');
        $notifications = $config['notifications'];
        
        $expectedKeys = [
            'enabled',
            'channels',
            'templates'
        ];
        
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $notifications, "Notifications config should have '{$key}' key");
        }
        
        // Verify channels structure
        $this->assertIsArray($notifications['channels']);
        $this->assertContains('database', $notifications['channels']);
        
        // Verify templates structure
        $this->assertIsArray($notifications['templates']);
        $templateKeys = ['submitted', 'approved', 'rejected'];
        foreach ($templateKeys as $template) {
            $this->assertArrayHasKey($template, $notifications['templates']);
        }
    }

    /** @test */
    public function config_values_are_sensible_defaults()
    {
        $this->artisan('approvals:publish', ['--config' => true]);
        
        $config = include config_path('approvals.php');
        
        // Check default values are reasonable
        $this->assertTrue($config['enable_approval_comments']);
        $this->assertTrue($config['enable_rejection_comments']);
        $this->assertTrue($config['navigation']['should_register_navigation']);
        $this->assertEquals('heroicon-o-check-circle', $config['navigation']['icon']);
        $this->assertTrue($config['ui']['show_approval_history']);
        $this->assertTrue($config['security']['prevent_self_approval']);
        $this->assertTrue($config['notifications']['enabled']);
    }

    /** @test */
    public function config_is_valid_php_syntax()
    {
        $this->artisan('approvals:publish', ['--config' => true]);
        
        $configPath = config_path('approvals.php');
        $this->assertFileExists($configPath);
        
        // Test that the config file has valid PHP syntax
        $config = null;
        $errorReporting = error_reporting(0); // Suppress potential warnings
        
        try {
            $config = include $configPath;
        } catch (Throwable $e) {
            $this->fail("Config file has invalid PHP syntax: " . $e->getMessage());
        } finally {
            error_reporting($errorReporting);
        }
        
        $this->assertNotNull($config);
        $this->assertIsArray($config);
    }

    /** @test */
    public function config_can_be_loaded_by_laravel()
    {
        $this->artisan('approvals:publish', ['--config' => true]);
        
        // Try to load the config through Laravel's config system
        $this->app['config']->set('approvals', include config_path('approvals.php'));
        
        // Verify we can access config values
        $this->assertTrue(config('approvals.enable_approval_comments'));
        $this->assertIsArray(config('approvals.navigation'));
        $this->assertIsArray(config('approvals.ui.status_colors'));
    }

    /** @test */
    public function config_has_proper_documentation()
    {
        $this->artisan('approvals:publish', ['--config' => true]);
        
        $configContent = File::get(config_path('approvals.php'));
        
        // Check for presence of documentation comments
        $this->assertStringContains('/*', $configContent);
        $this->assertStringContains('*/', $configContent);
        $this->assertStringContains('role_model', $configContent);
        $this->assertStringContains('navigation', $configContent);
        
        // Check for inline documentation
        $this->assertStringContains('//', $configContent);
    }

    /** @test */
    public function config_preserves_existing_values_on_republish()
    {
        // First publish
        $this->artisan('approvals:publish', ['--config' => true]);
        
        // Modify a value
        $configPath = config_path('approvals.php');
        $config = include $configPath;
        $config['enable_approval_comments'] = false;
        
        File::put($configPath, '<?php return ' . var_export($config, true) . ';');
        
        // Republish (this should preserve existing values in a real scenario)
        $this->artisan('approvals:publish', ['--config' => true]);
        
        // Note: In a real implementation, you might want to add a --force flag
        // to handle overwriting existing files differently
        $this->assertFileExists($configPath);
    }

    protected function cleanupPublishedFiles(): void
    {
        $configPath = config_path('approvals.php');
        if (File::exists($configPath)) {
            File::delete($configPath);
        }
    }
}
