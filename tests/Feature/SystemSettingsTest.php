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
}
