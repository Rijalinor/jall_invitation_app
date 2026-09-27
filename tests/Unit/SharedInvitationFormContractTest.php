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
}
