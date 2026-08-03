<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationPreviewReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_invitation_needs_a_host_and_primary_event_before_preview(): void
    {
        $invitation = Invitation::create([
            'customer_id' => Customer::create(['name' => 'Kiki & Maya'])->id,
            'title' => 'Pernikahan Kiki & Maya',
            'slug' => 'kiki-maya',
            'event_type' => 'wedding',
            'template_id' => 'elegant-rose',
            'status' => 'draft',
        ]);

        $this->assertCount(2, $invitation->missingPreviewRequirements());

        $invitation->hosts()->create(['role' => 'groom', 'name' => 'Kiki']);
        $invitation->events()->create([
            'label' => 'Akad Nikah',
            'date' => '2026-10-10',
            'is_primary' => true,
        ]);

        $this->assertSame([], $invitation->missingPreviewRequirements());
    }
}
