<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileLoginCsrfTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_includes_mobile_csrf_recovery_script(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('id="loginForm"', false)
            ->assertSee('pageshow', false)
            ->assertSee('syncCsrfToken', false);
    }
}
