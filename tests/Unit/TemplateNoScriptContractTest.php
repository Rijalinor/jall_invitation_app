<?php

namespace Tests\Unit;

use App\Services\TemplateRegistry;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Guards the promise that an invitation still opens and reads when its script
 * never arrives: a stale build, a blocked request, or a browser without
 * scripting must not leave a guest stuck behind the cover.
 */
class TemplateNoScriptContractTest extends TestCase
{
    /**
     * Selectors that mark content the template hides until it is revealed.
     */
    private const REVEAL_MARKERS = ['data-reveal', 'data-pop', 'bn-observe'];

    /**
     * Classes a template script adds once it can actually animate.
     */
    private const ARMED_MARKERS = ['-armed', 'js-ready', 'bn-gsap'];

    public function test_every_template_opens_its_cover_without_scripting(): void
    {
        foreach ($this->templates() as $id => $view) {
            $this->assertDoesNotMatchRegularExpression(
                '/<button[^>]*data-open-invitation/',
                $view,
                $template = 'Template "'.$id.'" opens its cover with a button, which does nothing when the script never loads.',
            );

            $this->assertMatchesRegularExpression(
                '/<a\b[^>]*data-open-invitation/',
                $view,
                'Template "'.$id.'" must offer the cover control as a real anchor.',
            );

            // The anchor has to point at the invitation root for :has(main:target) to fire.
            $mainId = $this->mainId($view);
            $this->assertNotNull($mainId, 'Template "'.$id.'" has no identifiable <main>.');

            $this->assertMatchesRegularExpression(
                '/<a\b[^>]*href="#'.preg_quote($mainId, '/').'"[^>]*data-open-invitation/',
                $view,
                'Template "'.$id.'" must link the cover control to #'.$mainId.'.',
            );
        }
    }

    public function test_every_template_provides_a_css_only_cover_escape(): void
    {
        foreach ($this->templates() as $id => $view) {
            $css = $this->css($id);

            $this->assertStringContainsString(
                'body:has(main:target)',
                $css,
                'Template "'.$id.'" needs a CSS-only way out of the cover.',
            );

            $this->assertStringContainsString(
                '.js-ready [data-gate]',
                $css,
                'Template "'.$id.'" must keep its gated content out of the tab order only while scripting is available.',
            );

            $this->assertStringContainsString(
                'js-ready',
                $view,
                'Template "'.$id.'" must mark the document as scripted before the first paint.',
            );
        }
    }

    /**
     * A classless anchor carries no button shape of its own, so whatever the theme
     * used to apply through a `button` selector stops matching the moment the
     * control becomes an anchor: it collapses to a bare inline link barely 20px
     * tall. Since this is the only way into the invitation, checking that the
     * attribute merely appears somewhere in the stylesheet is not enough — the
     * control needs a real touch target.
     */
    public function test_a_classless_anchor_cover_control_keeps_a_real_touch_target(): void
    {
        foreach ($this->templates() as $id => $view) {
            preg_match('/<a\b[^>]*data-open-invitation[^>]*>/', $view, $match);
            $control = $match[0] ?? '';

            // A control with its own class keeps its styling either way.
            if (preg_match('/class="[^"]+"/', $control) === 1) {
                continue;
            }

            $bodies = $this->ruleBodiesMatching($this->css($id), '[data-open-invitation]');

            $this->assertNotSame([], $bodies, sprintf(
                'Template "%s" opens with a classless anchor, so every `button` selector that used to style it '
                .'silently stops matching. Target [data-open-invitation] as well.',
                $id,
            ));

            $hasTouchTarget = false;

            foreach ($bodies as $body) {
                if (preg_match('/min-height\s*:/', $body) === 1) {
                    $hasTouchTarget = true;

                    break;
                }
            }

            $this->assertTrue($hasTouchTarget, sprintf(
                'Template "%s" styles its cover anchor but never gives it a minimum height, so the only way into '
                .'the invitation renders as a bare ~20px inline link.',
                $id,
            ));
        }
    }

    public function test_no_template_traps_its_content_behind_an_inert_attribute(): void
    {
        foreach ($this->templates() as $id => $view) {
            $this->assertStringNotContainsString(
                'inert',
                $view,
                'Template "'.$id.'" renders inert in its markup, so a page without scripting is unusable.',
            );
        }
    }

    public function test_hidden_content_waits_for_a_class_the_script_adds(): void
    {
        foreach ($this->templates() as $id => $view) {
            foreach ($this->rulesSettingOpacityZero($this->css($id)) as $selector => $rule) {
                if (! $this->looksLikeAReveal($selector)) {
                    continue;
                }

                $this->assertTrue(
                    $this->isArmed($selector),
                    'Template "'.$id.'" hides content in "'.$selector.'" without waiting for a class its script adds, '
                    .'so a failed script leaves a blank invitation.',
                );

                $this->assertStringNotContainsString(
                    '[data-motion]',
                    $selector,
                    'Template "'.$id.'" fades content in "'.$selector.'" based on a server-rendered attribute, '
                    .'which is present even when the script never runs.',
                );
            }
        }
    }

    /**
     * @return array<string, string> Template id to entry view source.
     */
    private function templates(): array
    {
        $views = [];

        foreach (app(TemplateRegistry::class)->all() as $id => $manifest) {
            $views[$id] = File::get(app('view')->getFinder()->find($manifest['entry_view']));
        }

        return $views;
    }

    private function css(string $id): string
    {
        $path = resource_path('invitation-templates/'.$id.'/assets/theme.css');

        return File::exists($path) ? File::get($path) : '';
    }

    private function mainId(string $view): ?string
    {
        return preg_match('/<main\b[^>]*\bid="([^"]+)"/', $view, $matches) === 1 ? $matches[1] : null;
    }

    /**
     * The declaration blocks of every rule whose selector mentions the fragment.
     *
     * @return array<int, string>
     */
    private function ruleBodiesMatching(string $css, string $fragment): array
    {
        $bodies = [];

        foreach (explode('}', $css) as $block) {
            if (! str_contains($block, '{')) {
                continue;
            }

            [$selector, $body] = explode('{', $block, 2);

            if (str_contains($selector, $fragment)) {
                $bodies[] = $body;
            }
        }

        return $bodies;
    }

    /**
     * @return array<string, string>
     */
    private function rulesSettingOpacityZero(string $css): array
    {
        $rules = [];

        foreach (explode('}', $css) as $block) {
            if (! str_contains($block, '{')) {
                continue;
            }

            [$selector, $body] = explode('{', $block, 2);

            if (preg_match('/opacity:\s*0(\.0+)?\s*[;!]/', $body) === 1) {
                $rules[trim($selector)] = trim($body);
            }
        }

        return $rules;
    }

    private function looksLikeAReveal(string $selector): bool
    {
        foreach (self::REVEAL_MARKERS as $marker) {
            if (str_contains($selector, $marker)) {
                return true;
            }
        }

        return false;
    }

    private function isArmed(string $selector): bool
    {
        foreach (self::ARMED_MARKERS as $marker) {
            if (str_contains($selector, $marker)) {
                return true;
            }
        }

        return false;
    }
}
