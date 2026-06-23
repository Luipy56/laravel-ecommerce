<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminLoginPageTest extends TestCase
{
    public function test_spa_shell_injects_admin_auto_login_disabled_by_default(): void
    {
        config(['app.admin_auto_login' => false]);

        $html = $this->get('/admin/login')->assertOk()->getContent();
        $this->assertStringContainsString('window.__LARAVEL_ADMIN_AUTO_LOGIN__ = false', $html);
    }

    public function test_spa_shell_injects_admin_auto_login_when_config_enabled(): void
    {
        config(['app.admin_auto_login' => true]);

        $html = $this->get('/admin/login')->assertOk()->getContent();
        $this->assertStringContainsString('window.__LARAVEL_ADMIN_AUTO_LOGIN__ = true', $html);
    }
}
