<?php

use App\Enums\EventType;
use App\Enums\GiftMethodType;
use App\Enums\InvitationStatus;
use App\Models\Customer;
use App\Models\Invitation;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$customer = Customer::create([
    'name' => 'Kiki & Maya',
    'phone' => '08987654321',
]);

$invitation = Invitation::create([
    'customer_id' => $customer->id,
    'title' => 'Pernikahan Kiki & Maya',
    'slug' => 'kiki-maya',
    'event_type' => EventType::WEDDING,
    'template_id' => 'elegant-rose',
    'status' => InvitationStatus::DRAFT,
]);

// 1. Host
$host = $invitation->hosts()->create([
    'role' => 'groom',
    'name' => 'Rizki Pratama (Kiki)',
    'nickname' => 'Kiki',
    'position' => 1,
]);
echo "Host created: {$host->name}\n";

// 2. Event
$event = $invitation->events()->create([
    'label' => 'Akad Nikah',
    'date' => '2026-10-10',
    'start_time' => '08:00',
    'venue_name' => 'Masjid Agung',
    'is_primary' => true,
]);
echo "Event created: {$event->label} on {$event->date->format('Y-m-d')}\n";

// 3. Story
$story = $invitation->stories()->create([
    'date' => '2022',
    'title' => 'Pertemuan di Bangku Kuliah',
    'body' => 'Pertama kali bertemu saat ospek jurusan.',
]);
echo "Story created: {$story->title}\n";

// 4. Media
$media = $invitation->media()->create([
    'type' => 'image',
    'path' => 'invitations/media/photo1.jpg',
    'caption' => 'Prewedding di Bandung',
]);
echo "Media created: {$media->caption}\n";

// 5. Gift Method
$gift = $invitation->giftMethods()->create([
    'type' => GiftMethodType::BANK_TRANSFER,
    'provider' => 'BCA',
    'account_name' => 'Rizki Pratama',
    'account_number' => '1234567890',
]);
echo "Gift Method created: {$gift->provider} - {$gift->account_number}\n";

// 6. Contact
$contact = $invitation->contacts()->create([
    'label' => 'CP WO',
    'name' => 'Mbak Anita',
    'phone' => '081122334455',
]);
echo "Contact created: {$contact->label} ({$contact->name})\n";

// 7. Section
$section = $invitation->sections()->create([
    'key' => 'opening',
    'enabled' => true,
    'position' => 0,
]);
echo "Section created: {$section->key}\n";

// Cleanup
$invitation->forceDelete();
$customer->forceDelete();
echo "Phase 3 test execution completed successfully!\n";
