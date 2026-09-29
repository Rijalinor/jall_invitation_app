<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invitation;
use App\Services\TemplateRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TemplateMultiLineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Templates that already keep the line breaks an operator typed into a
     * textarea. HTML collapses those newlines, so a template missing from this
     * list renders the same paragraph as one endless line.
     *
     * @var array<int, string>
     */
    private const CONVERTED = ['elegant-rose', 'fun-storybook', 'midnight-ledger'];

    /**
     * The rest. Converting a template moves it from here to the list above, and
     * the test below fails until it does, so the gap cannot quietly disappear.
     *
     * @var array<int, string>
     */
    private const PENDING = [];

    #[DataProvider('convertedTemplates')]
    public function test_multi_line_text_keeps_its_line_breaks(string $template): void
    {
        $invitation = $this->invitation($template);
        $invitation->stories()->create([
            'date' => '2021', 'title' => 'Pertemuan',
            'body' => "Baris satu\nBaris dua", 'position' => 1,
        ]);

        $this->get('/undangan-'.$template)
            ->assertOk()
            ->assertSee("Baris satu<br />\nBaris dua", false)
            ->assertSee("Penutup satu<br />\nPenutup dua", false);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function convertedTemplates(): array
    {
        return collect(self::CONVERTED)->mapWithKeys(fn (string $id): array => [$id => [$id]])->all();
    }

    public function test_the_unconverted_templates_are_exactly_the_ones_still_pending(): void
    {
        $unconverted = array_values(array_diff(array_keys(app(TemplateRegistry::class)->all()), self::CONVERTED));

        $this->assertSame(self::PENDING, $unconverted, 'A template was converted, renamed or added: move it between the two lists.');
    }

    private function invitation(string $template): Invitation
    {
        return Invitation::create([
            'customer_id' => Customer::create(['name' => 'Pelanggan'])->id,
            'title' => 'Undangan Baris',
            'slug' => 'undangan-'.$template,
            'event_type' => 'wedding',
            'template_id' => $template,
            'status' => 'published',
            'closing_message' => "Penutup satu\nPenutup dua",
        ]);
    }
}
