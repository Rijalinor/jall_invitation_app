<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SharedInvitationFormContractTest extends TestCase
{
    public function test_shared_forms_use_template_neutral_class_names(): void
    {
        $root = dirname(__DIR__, 2).'/resources/views/invitations/shared';

        foreach (['rsvp.blade.php', 'guestbook.blade.php'] as $view) {
            $contents = (string) file_get_contents($root.'/'.$view);

            $this->assertStringContainsString('invitation-', $contents);
            $this->assertStringNotContainsString('er-', $contents);
        }
    }

    /**
     * The forms submit without a reload through the shared script, so each one
     * has to expose the hooks it reads and writes.
     */
    public function test_the_shared_forms_expose_the_ajax_hooks(): void
    {
        $root = dirname(__DIR__, 2).'/resources/views/invitations/shared';

        foreach (['rsvp.blade.php', 'guestbook.blade.php', 'confirmation.blade.php'] as $view) {
            $contents = (string) file_get_contents($root.'/'.$view);

            foreach (['data-invitation-form', 'data-form-status', 'data-form-errors'] as $hook) {
                $this->assertStringContainsString($hook, $contents, $view.' is missing "'.$hook.'".');
            }
        }
    }

    public function test_every_template_loads_the_ajax_form_script(): void
    {
        $script = dirname(__DIR__, 2).'/resources/js/invitation-forms.js';

        $this->assertFileExists($script);

        foreach (glob(dirname(__DIR__, 2).'/resources/invitation-templates/*', GLOB_ONLYDIR) as $directory) {
            $view = (string) file_get_contents($directory.'/views/index.blade.php');

            $this->assertStringContainsString(
                'resources/js/invitation-forms.js',
                $view,
                'Template "'.basename($directory).'" does not load the shared forms script.',
            );
        }
    }

    /**
     * One gallery lightbox, shared: a guest must be able to move between photos,
     * so the swipe / prev / next behaviour lives in one script every template
     * loads rather than being rewritten (and forgotten) per design.
     */
    public function test_every_template_loads_the_shared_lightbox_script(): void
    {
        $script = dirname(__DIR__, 2).'/resources/js/invitation-lightbox.js';

        $this->assertFileExists($script);

        $contents = (string) file_get_contents($script);

        foreach (['data-lightbox-prev', 'data-lightbox-next', 'touchstart', 'touchend', 'pointerdown', 'ArrowLeft'] as $marker) {
            $this->assertStringContainsString($marker, $contents, 'The shared lightbox is missing "'.$marker.'".');
        }

        foreach (glob(dirname(__DIR__, 2).'/resources/invitation-templates/*', GLOB_ONLYDIR) as $directory) {
            $view = (string) file_get_contents($directory.'/views/index.blade.php');

            $this->assertStringContainsString(
                'resources/js/invitation-lightbox.js',
                $view,
                'Template "'.basename($directory).'" does not load the shared lightbox script.',
            );
        }
    }

    /**
     * Guards the single source of truth: a template must not quietly reintroduce
     * its own one-image-only lightbox wiring.
     */
    public function test_templates_do_not_reimplement_the_lightbox(): void
    {
        foreach (glob(dirname(__DIR__, 2).'/resources/invitation-templates/*', GLOB_ONLYDIR) as $directory) {
            $script = (string) file_get_contents($directory.'/assets/theme.js');

            $this->assertStringNotContainsString(
                'data-lightbox-image',
                $script,
                'Template "'.basename($directory).'" wires its own lightbox again; keep it in the shared script.',
            );
        }
    }
}
