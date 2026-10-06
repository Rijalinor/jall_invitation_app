<?php

namespace Tests\Unit;

use App\Support\TimezoneLabel;
use PHPUnit\Framework\TestCase;

class TimezoneLabelTest extends TestCase
{
    public function test_indonesian_zones_use_their_familiar_short_names(): void
    {
        $this->assertSame('WIB', TimezoneLabel::for('Asia/Jakarta'));
        $this->assertSame('WIB', TimezoneLabel::for('Asia/Pontianak'));
        $this->assertSame('WITA', TimezoneLabel::for('Asia/Makassar'));
        $this->assertSame('WITA', TimezoneLabel::for('asia/ujung_pandang'));
        $this->assertSame('WIT', TimezoneLabel::for('Asia/Jayapura'));
        $this->assertSame('WIB', TimezoneLabel::for('  Asia/Jakarta  '));
    }

    public function test_other_zones_fall_back_to_a_readable_utc_offset(): void
    {
        // Fixed-offset zones only, so the assertion never depends on the season.
        $this->assertSame('UTC+9', TimezoneLabel::for('Asia/Tokyo'));
        $this->assertSame('UTC+5:30', TimezoneLabel::for('Asia/Kolkata'));
        $this->assertSame('UTC+4', TimezoneLabel::for('Asia/Dubai'));
    }

    public function test_blank_and_unknown_zones_do_not_break(): void
    {
        $this->assertSame('', TimezoneLabel::for(''));
        $this->assertSame('', TimezoneLabel::for(null));
        $this->assertSame('Asia/Nowhere', TimezoneLabel::for('Asia/Nowhere'));
    }
}
