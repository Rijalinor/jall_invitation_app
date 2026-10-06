<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Every template keeps its host cards compact and offers the same shared dialog
 * for the full profile. The dialog is wired by resources/js/invitation-hosts.js,
 * and the inline details are only collapsed once that script has armed them, so
 * a blocked script leaves the details visible.
 */
class HostDialogContractTest extends TestCase
{
    public function test_the_shared_dialog_partial_exposes_the_hooks_the_script_needs(): void
    {
        $partial = dirname(__DIR__, 2).'/resources/views/invitations/shared/host-dialog.blade.php';

        $this->assertFileExists($partial);

        $markup = (string) file_get_contents($partial);

        foreach (['data-host-dialog', 'data-host-dialog-close', 'data-host-dialog-role', 'data-host-dialog-name', 'data-host-dialog-body'] as $hook) {
            $this->assertStringContainsString($hook, $markup, 'The shared dialog is missing "'.$hook.'".');
        }
    }

    public function test_every_template_renders_a_host_trigger_details_and_the_dialog(): void
    {
        $templates = glob(dirname(__DIR__, 2).'/resources/invitation-templates/*', GLOB_ONLYDIR);

        $this->assertNotSame([], $templates);

        foreach ($templates as $directory) {
            $name = basename($directory);
            $view = (string) file_get_contents($directory.'/views/index.blade.php');

            $this->assertStringContainsString('data-host-open', $view, 'Template "'.$name.'" has no host trigger.');
            $this->assertStringContainsString('data-host-details', $view, 'Template "'.$name.'" has no host details container.');
            $this->assertStringContainsString('data-host-role', $view, 'Template "'.$name.'" does not pass the host role to the dialog.');
            $this->assertStringContainsString("@include('invitations.shared.host-dialog')", $view, 'Template "'.$name.'" does not render the shared dialog.');
        }
    }

    public function test_details_are_collapsed_only_after_the_script_arms_them(): void
    {
        $script = (string) file_get_contents(dirname(__DIR__, 2).'/resources/js/invitation-hosts.js');
        $css = (string) file_get_contents(dirname(__DIR__, 2).'/resources/css/invitations.css');

        $this->assertStringContainsString("classList.add('hosts-armed')", $script);
        $this->assertStringContainsString('.hosts-armed [data-host] .host-card__details', $css);
    }
}
