<?php

namespace Database\Seeders;

use App\Enums\EventType;
use App\Enums\GiftMethodType;
use App\Enums\InvitationStatus;
use App\Enums\ModerationStatus;
use App\Enums\RsvpStatus;
use App\Models\Customer;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Builds one complete sample invitation from docs/DEMO_DATA.md.
 *
 * The catalogue renders a single published sample in every template's design, so
 * this seeder fills that one invitation end to end — couple, events, story,
 * gallery, gifts, contacts, guests, RSVP, and guestbook — and generates small
 * placeholder images so the demo has something to show without shipping photos.
 * Re-running it is safe: the invitation and its children are replaced in place.
 */
class DemoInvitationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@jall.com')->first();

        $customer = Customer::updateOrCreate(
            ['email' => 'rendra@example.com'],
            [
                'name' => 'Rendra Adyatma',
                'phone' => '081234567890',
                'address' => 'Tanjung Benoa, Badung, Bali',
                'notes' => 'Pelanggan contoh untuk katalog.',
            ],
        );

        $this->placeholder('invitations/demo/cover.jpg', 'Coastal Vow', 1600, 1000, [43, 122, 120]);
        $this->placeholder('invitations/demo/host-rendra.jpg', 'Rendra', 800, 1000, [47, 111, 159]);
        $this->placeholder('invitations/demo/host-alya.jpg', 'Alya', 800, 1000, [201, 104, 63]);

        $gallery = [
            'Senja di Tanjung Benoa',
            'Tertawa bersama',
            'Ombak pagi',
            'Jejak di pasir',
            'Cakrawala',
            'Malam di dermaga',
        ];

        foreach ($gallery as $index => $caption) {
            $this->placeholder('invitations/demo/gallery-'.($index + 1).'.jpg', $caption, 1200, 900, [20 + $index * 12, 60 + $index * 8, 90 + $index * 6]);
        }

        $invitation = Invitation::updateOrCreate(
            ['slug' => 'rendra-alya'],
            [
                'customer_id' => $customer->id,
                'user_id' => $admin?->id,
                'title' => 'Pernikahan Rendra & Alya',
                'event_type' => EventType::WEDDING,
                'template_id' => 'coastal-vow',
                'template_version' => '1.0.0',
                'status' => InvitationStatus::PUBLISHED,
                'is_catalog_demo' => true,
                'opening_text' => 'Dengan memohon rahmat Tuhan Yang Maha Esa, kami bermaksud menyelenggarakan pernikahan putra-putri kami.',
                'closing_message' => 'Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.',
                'share_message' => 'Kepada Yth. [nama], kami mengundang Anda untuk hadir di pernikahan kami.',
                'livestream_url' => 'https://www.youtube.com/live/contoh-demo',
                'livestream_label' => 'Saksikan Live Streaming',
                'published_at' => now(),
                'settings_json' => [
                    'accent_color' => '#c9683f',
                    'motion' => 'calm',
                    'cover_poster_image' => 'invitations/demo/cover.jpg',
                    'closing_families' => [
                        ['label' => 'Keluarga Mempelai Pria', 'names' => 'Bpk. Hendra Wijaya & Ibu Sri Wahyuni'],
                        ['label' => 'Keluarga Mempelai Wanita', 'names' => 'Bpk. Bambang Sutrisno & Ibu Ratna Dewi'],
                    ],
                    'closing_footer' => "Turut mengundang:\nKeluarga besar kedua mempelai",
                ],
            ],
        );

        $invitation->hosts()->delete();
        $invitation->events()->delete();
        $invitation->sections()->delete();
        $invitation->stories()->delete();
        $invitation->media()->delete();
        $invitation->giftMethods()->delete();
        $invitation->contacts()->delete();
        $invitation->guestbookEntries()->delete();
        $invitation->rsvps()->delete();
        $invitation->guests()->delete();

        $invitation->hosts()->createMany([
            [
                'role' => 'groom',
                'name' => 'Rendra Adyatma',
                'nickname' => 'Rendra',
                'photo_path' => 'invitations/demo/host-rendra.jpg',
                'bio' => 'Pencinta senja, kopi, dan perjalanan.',
                'birth_order' => 'Putra pertama',
                'parent_father' => 'Hendra Wijaya',
                'parent_mother' => 'Sri Wahyuni',
                'social_instagram' => 'rendra.adyatma',
                'position' => 0,
            ],
            [
                'role' => 'bride',
                'name' => 'Alya Prameswari',
                'nickname' => 'Alya',
                'photo_path' => 'invitations/demo/host-alya.jpg',
                'bio' => 'Suka buku puisi dan hari-hari yang tenang.',
                'birth_order' => 'Putri kedua',
                'parent_father' => 'Bambang Sutrisno',
                'parent_mother' => 'Ratna Dewi',
                'social_instagram' => 'alya.prameswari',
                'position' => 1,
            ],
        ]);

        $invitation->events()->createMany([
            [
                'label' => 'Akad Nikah',
                'date' => '2027-06-12',
                'start_time' => '08:00',
                'end_time' => '10:00',
                'timezone' => 'Asia/Makassar',
                'venue_name' => 'Pendopo Pantai Indah',
                'address' => 'Jl. Pantai Cendana No. 12, Tanjung Benoa, Bali',
                'map_url' => 'https://maps.app.goo.gl/contoh-demo',
                'latitude' => -8.7400,
                'longitude' => 115.2200,
                'parking_notes' => 'Parkir di area timur, ikuti papan petunjuk.',
                'landmark_notes' => 'Sebelah kanan gerbang utama pantai.',
                'dress_code' => 'Batik / Formal Putih',
                'is_primary' => true,
                'position' => 0,
            ],
            [
                'label' => 'Resepsi',
                'date' => '2027-06-12',
                'start_time' => '11:00',
                'end_time' => '14:00',
                'timezone' => 'Asia/Makassar',
                'venue_name' => 'Ocean Ballroom, Pantai Indah Resort',
                'address' => 'Jl. Pantai Cendana No. 12, Tanjung Benoa, Bali',
                'map_url' => 'https://maps.app.goo.gl/contoh-demo',
                'latitude' => -8.7400,
                'longitude' => 115.2200,
                'dress_code' => 'Batik / Formal Putih',
                'is_primary' => false,
                'position' => 1,
            ],
        ]);

        foreach (Invitation::defaultSectionKeys() as $index => $key) {
            $invitation->sections()->create(['key' => $key, 'enabled' => true, 'position' => $index]);
        }

        $invitation->stories()->createMany([
            [
                'date' => '2019',
                'title' => 'Pertemuan Pertama',
                'body' => 'Kami bertemu di sebuah kedai kopi dekat pantai. Obrolan singkat itu ternyata berlanjut sampai sekarang.',
                'position' => 0,
            ],
            [
                'date' => '2023',
                'title' => 'Mulai Serius',
                'body' => 'Setelah empat tahun, kami memutuskan untuk saling menjaga dan menumbuhkan mimpi bersama.',
                'position' => 1,
            ],
            [
                'date' => '2026',
                'title' => 'Lamaran',
                'body' => 'Di bawah langit senja, Rendra melamar Alya. Tanpa ragu, Alya mengucapkan iya.',
                'position' => 2,
            ],
        ]);

        foreach ($gallery as $index => $caption) {
            $invitation->media()->create([
                'type' => 'image',
                'path' => 'invitations/demo/gallery-'.($index + 1).'.jpg',
                'alt_text' => $caption,
                'caption' => $caption,
                'position' => $index,
            ]);
        }

        $invitation->giftMethods()->createMany([
            [
                'type' => GiftMethodType::BANK_TRANSFER,
                'provider' => 'Bank Mandiri',
                'account_name' => 'Alya Prameswari',
                'account_number' => '1234567890',
                'notes' => 'Mohon konfirmasi setelah transfer. Terima kasih.',
                'position' => 0,
            ],
            [
                'type' => GiftMethodType::EWALLET,
                'provider' => 'GoPay',
                'account_name' => 'Rendra Adyatma',
                'account_number' => '081234567890',
                'position' => 1,
            ],
        ]);

        $invitation->contacts()->createMany([
            ['label' => 'Keluarga Mempelai Wanita', 'name' => 'Dewi Anggraini', 'phone' => '081234567891', 'position' => 0],
            ['label' => 'Keluarga Mempelai Pria', 'name' => 'Andi Pratama', 'phone' => '081234567892', 'position' => 1],
        ]);

        $budi = $invitation->guests()->create(['display_name' => 'Budi Santoso', 'group' => 'Keluarga', 'phone' => '081234567890', 'invitation_limit' => 4]);
        $siti = $invitation->guests()->create(['display_name' => 'Siti Aminah', 'group' => 'Teman', 'phone' => '081298765432', 'invitation_limit' => 2]);
        $andi = $invitation->guests()->create(['display_name' => 'Andi Pratama', 'group' => 'Keluarga', 'phone' => '081234567892', 'invitation_limit' => 2]);

        $invitation->rsvps()->createMany([
            ['guest_id' => $budi->id, 'name' => 'Budi Santoso', 'status' => RsvpStatus::ATTENDING, 'party_size' => 3, 'note' => 'Insya Allah hadir sekeluarga.'],
            ['guest_id' => $siti->id, 'name' => 'Siti Aminah', 'status' => RsvpStatus::TENTATIVE, 'party_size' => 1],
            ['guest_id' => $andi->id, 'name' => 'Andi Pratama', 'status' => RsvpStatus::NOT_ATTENDING, 'party_size' => 0, 'note' => 'Mohon maaf belum bisa hadir, doa terbaik menyertai.'],
        ]);

        $invitation->guestbookEntries()->createMany([
            ['guest_id' => $budi->id, 'name' => 'Budi Santoso', 'message' => "Barakallahu lakuma wa baraka 'alaikuma. Selamat menempuh hidup baru!", 'moderation_status' => ModerationStatus::APPROVED],
            ['guest_id' => $siti->id, 'name' => 'Siti Aminah', 'message' => 'Semoga menjadi keluarga yang sakinah, mawaddah, warahmah.', 'moderation_status' => ModerationStatus::APPROVED],
            ['name' => 'Keluarga Besar', 'message' => 'Turut berbahagia atas hari istimewa kalian berdua!', 'moderation_status' => ModerationStatus::PENDING],
        ]);
    }

    /**
     * Writes a small solid-colour JPEG with a centred label so the demo gallery,
     * cover, and couple portraits have something real to render.
     *
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    private function placeholder(string $path, string $label, int $width, int $height, array $rgb): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            return;
        }

        $full = storage_path('app/public/'.$path);
        File::ensureDirectoryExists(dirname($full));

        $image = imagecreatetruecolor($width, $height);
        $background = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
        imagefilledrectangle($image, 0, 0, $width, $height, $background);

        $band = imagecolorallocate($image, (int) ($rgb[0] * 0.75), (int) ($rgb[1] * 0.75), (int) ($rgb[2] * 0.75));
        imagefilledrectangle($image, 0, (int) ($height * 0.68), $width, $height, $band);

        $white = imagecolorallocate($image, 255, 255, 255);
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($label);
        $textHeight = imagefontheight($font);
        imagestring($image, $font, (int) (($width - $textWidth) / 2), (int) (($height - $textHeight) / 2), $label, $white);

        imagejpeg($image, $full, 85);
        imagedestroy($image);
    }
}
