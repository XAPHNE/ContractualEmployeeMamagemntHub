<?php

namespace Tests\Feature;

use App\Models\Setting;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_brand_name_defaults_to_app_name(): void
    {
        config(['app.name' => 'Default Portal Name']);
        Setting::where('key', 'app_name')->delete();

        $panel = Filament::getPanel('admin');
        $this->assertEquals('Default Portal Name', $panel->getBrandName());
    }

    public function test_panel_brand_name_uses_system_settings_when_configured(): void
    {
        Setting::set('app_name', 'Custom System Setting Name');

        $panel = Filament::getPanel('admin');
        $this->assertEquals('Custom System Setting Name', $panel->getBrandName());
    }

    public function test_allow_skipping_contribution_months_setting_defaults_false_and_can_be_toggled(): void
    {
        $this->assertFalse((bool) Setting::get('allow_skipping_contribution_months', false));

        Setting::set('allow_skipping_contribution_months', true);
        $this->assertTrue((bool) Setting::get('allow_skipping_contribution_months', false));

        Setting::set('allow_skipping_contribution_months', false);
        $this->assertFalse((bool) Setting::get('allow_skipping_contribution_months', false));
    }
}
