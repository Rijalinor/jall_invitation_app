<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PaperLanternManifestTest extends TestCase
{
    public function test_manifest_declares_stable_identity_sections_and_settings_defaults(): void
    {
        $path = dirname(__DIR__, 2).'/resources/invitation-templates/paper-lantern/manifest.json';
        $manifest = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('paper-lantern', $manifest['id']);
        $this->assertSame('1.1.0', $manifest['version']);
        $this->assertSame('paper-lantern.views.index', $manifest['entry_view']);
        $this->assertContains('sharing', $manifest['sections']);
        $this->assertContains('closing', $manifest['sections']);
        $this->assertSame('#d68b62', $manifest['settings_schema']['accent_color']['default']);
        $this->assertSame('calm', $manifest['settings_schema']['motion']['default']);
        $this->assertContains('off', $manifest['settings_schema']['motion']['options']);
    }

    public function test_entry_view_and_preview_assets_exist(): void
    {
        $base = dirname(__DIR__, 2).'/resources/invitation-templates/paper-lantern';

        $this->assertFileExists($base.'/views/index.blade.php');
        $this->assertFileExists($base.'/preview.svg');
        $this->assertFileExists($base.'/assets/theme.css');
        $this->assertFileExists($base.'/assets/theme.js');
    }
}
