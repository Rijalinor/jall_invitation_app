<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_and_invitation_admin_pages_render_with_filament_four_actions(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $customer = Customer::create(['name' => 'Pelanggan Uji']);
        $invitation = Invitation::create([
            'customer_id' => $customer->id,
            'title' => 'Undangan Uji',
            'slug' => 'undangan-uji',
            'event_type' => 'wedding',
            'template_id' => 'elegant-rose',
            'status' => 'draft',
        ]);

        $this->actingAs($user)->get('/admin/customers')->assertOk();
        $this->get('/admin/invitations')->assertOk();
        $this->get('/admin/invitations/'.$invitation->id.'/edit')->assertOk();
    }
}
