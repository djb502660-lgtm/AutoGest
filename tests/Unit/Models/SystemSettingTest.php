<?php

namespace Tests\Unit\Models;

use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_value_returns_the_default_when_the_key_is_missing(): void
    {
        $this->assertSame('fallback', SystemSetting::getValue('taller.nombre', 'fallback'));
        $this->assertNull(SystemSetting::getValue('taller.nombre'));
    }

    public function test_set_value_creates_the_setting_with_the_given_group(): void
    {
        $setting = SystemSetting::setValue('taller.nombre', 'AutoGest', 'empresa');

        $this->assertTrue($setting->exists);
        $this->assertSame('empresa', $setting->group);
        $this->assertSame('AutoGest', SystemSetting::getValue('taller.nombre'));
    }

    public function test_set_value_defaults_to_the_general_group(): void
    {
        $this->assertSame('general', SystemSetting::setValue('taller.telefono', '099')->group);
    }

    public function test_set_value_updates_an_existing_key_without_duplicating_it(): void
    {
        SystemSetting::setValue('taller.nombre', 'AutoGest', 'empresa');
        $updated = SystemSetting::setValue('taller.nombre', 'AutoGest Norte', 'sucursal');

        $this->assertSame(1, SystemSetting::where('key', 'taller.nombre')->count());
        $this->assertSame('AutoGest Norte', $updated->value);
        $this->assertSame('sucursal', SystemSetting::first()->group);
    }

    public function test_get_value_falls_back_to_the_default_when_the_stored_value_is_null(): void
    {
        SystemSetting::setValue('taller.logo', null);

        $this->assertSame('sin-logo', SystemSetting::getValue('taller.logo', 'sin-logo'));
    }
}
