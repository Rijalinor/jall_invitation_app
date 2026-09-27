<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TemplateClassContractTest extends TestCase
{
    /**
     * Modifier classes that exist in the markup only as documentation anchors
     * and are intentionally styled through a parent selector instead.
     */
    private const VIEW_ONLY_MODIFIERS = ['er-gallery-section'];

    public function test_view_section_and_eyebrow_classes_are_styled_by_the_template_css(): void
    {
        $templates = glob(dirname(__DIR__, 2).'/resources/invitation-templates/*', GLOB_ONLYDIR);

        $this->assertNotSame([], $templates);

        foreach ($templates as $directory) {
            $view = $directory.'/views/index.blade.php';
            $css = $directory.'/assets/theme.css';

            if (! is_file($view) || ! is_file($css)) {
                continue;
            }

            $unstyled = $this->unstyledStructuralClasses(
                (string) file_get_contents($view),
                (string) file_get_contents($css),
            );

            $this->assertSame([], $unstyled, sprintf(
                'Template "%s" renders classes that have no rule in theme.css: %s',
                basename($directory),
                implode(', ', $unstyled),
            ));
        }
    }

    public function test_elegant_rose_reveal_script_selects_classes_present_in_its_view(): void
    {
        $directory = dirname(__DIR__, 2).'/resources/invitation-templates/elegant-rose';
        $view = (string) file_get_contents($directory.'/views/index.blade.php');
        $script = (string) file_get_contents($directory.'/assets/theme.js');

        preg_match_all("/querySelectorAll\\('(?<selector>\\.[a-z0-9-]+)'\\)/", $script, $matches);

        $this->assertNotSame([], $matches['selector']);

        foreach ($matches['selector'] as $selector) {
            $class = substr($selector, 1);

            $this->assertMatchesRegularExpression(
                '/class="[^"]*\b'.preg_quote($class, '/').'\b[^"]*"/',
                $view,
                sprintf('theme.js selects "%s" but the view never renders it.', $selector),
            );
        }
    }

    public function test_section_tint_is_the_last_background_rule_for_the_shared_section(): void
    {
        foreach ($this->templateStylesheets() as $name => $css) {
            $shadowing = $this->backgroundRulesDeclaredAfterTint($css);

            $this->assertSame([], $shadowing, sprintf(
                'Template "%s" paints .invitation-section from a rule declared after the tint, so the tint never applies: %s',
                $name,
                implode(', ', $shadowing),
            ));
        }
    }

    public function test_notice_colour_outranks_paragraph_rules_inside_the_shared_section(): void
    {
        foreach ($this->templateStylesheets() as $name => $css) {
            $beaten = $this->noticeColoursBeatenByParagraphRules($css);

            $this->assertSame([], $beaten, sprintf(
                'Template "%s" lets a generic .invitation-section p rule win .invitation-notice colour: %s',
                $name,
                implode(' | ', $beaten),
            ));
        }
    }

    /**
     * @return array<int, string>
     */
    private function unstyledStructuralClasses(string $view, string $css): array
    {
        preg_match_all('/class="([^"]+)"/', $view, $matches);

        $classes = [];
        foreach ($matches[1] as $attribute) {
            foreach (preg_split('/\s+/', trim($attribute)) as $class) {
                if ($class !== '' && ! in_array($class, self::VIEW_ONLY_MODIFIERS, true)) {
                    $classes[] = $class;
                }
            }
        }

        $unstyled = [];
        foreach (array_unique($classes) as $class) {
            if (! $this->isStructuralClass($class)) {
                continue;
            }

            if (preg_match('/\.'.preg_quote($class, '/').'(?![A-Za-z0-9_-])/', $css) !== 1) {
                $unstyled[] = $class;
            }
        }

        sort($unstyled);

        return $unstyled;
    }

    /**
     * Classes that carry layout, so an absent rule would silently collapse them:
     * the template-neutral `invitation-*` vocabulary shared by every partial,
     * plus any `*-section` / `*-eyebrow` a template names itself.
     */
    private function isStructuralClass(string $class): bool
    {
        if (str_starts_with($class, 'invitation-')) {
            return true;
        }

        return preg_match('/-(section|eyebrow)$/', $class) === 1;
    }

    /**
     * Template name => theme.css, with comments stripped so that offsets and
     * selectors are read exactly as the cascade sees them.
     *
     * @return array<string, string>
     */
    private function templateStylesheets(): array
    {
        $stylesheets = [];

        foreach (glob(dirname(__DIR__, 2).'/resources/invitation-templates/*', GLOB_ONLYDIR) as $directory) {
            $path = $directory.'/assets/theme.css';

            if (is_file($path)) {
                $stylesheets[basename($directory)] = (string) preg_replace(
                    '#/\*.*?\*/#s', '', (string) file_get_contents($path)
                );
            }
        }

        $this->assertNotSame([], $stylesheets);

        return $stylesheets;
    }

    /**
     * Whether a (comma separated) selector's final compound is the section
     * element itself, so the rule paints the band rather than a descendant
     * such as `.invitation-section .invitation-notice`.
     */
    private function targetsSection(string $selector): bool
    {
        foreach (explode(',', $selector) as $part) {
            if (preg_match('/(^|[\s>+~,])\.invitation-section(?![\w-])$/', trim($part)) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Every `selector { body }` pair, flattened through nested at-rules so a
     * rule inside @media is reported with its own selector and absolute offset.
     *
     * @return array<int, array{selector: string, offset: int, body: string}>
     */
    private function ruleBlocks(string $css): array
    {
        $blocks = [];
        $stack = [];
        $selectorStart = 0;

        for ($i = 0, $length = strlen($css); $i < $length; $i++) {
            if ($css[$i] === '{') {
                $stack[] = [
                    'selector' => trim(preg_replace('/\s+/', ' ', substr($css, $selectorStart, $i - $selectorStart))),
                    'offset' => $selectorStart,
                    'bodyStart' => $i + 1,
                ];
                $selectorStart = $i + 1;
            } elseif ($css[$i] === '}' && $stack !== []) {
                $frame = array_pop($stack);
                $frame['body'] = substr($css, $frame['bodyStart'], $i - $frame['bodyStart']);
                unset($frame['bodyStart']);
                $blocks[] = $frame;
                $selectorStart = $i + 1;
            }
        }

        return $blocks;
    }

    /**
     * Rules that repaint .invitation-section after the tint was declared. Equal
     * specificity plus later source order beats the tint, so the tinted band
     * silently reverts to the untinted background.
     *
     * @return array<int, string>
     */
    private function backgroundRulesDeclaredAfterTint(string $css): array
    {
        $tint = strpos($css, '.invitation-section--tint');

        if ($tint === false) {
            return [];
        }

        $shadowing = [];

        foreach ($this->ruleBlocks($css) as $block) {
            if ($block['offset'] <= $tint) {
                continue;
            }

            if (! $this->targetsSection($block['selector'])) {
                continue;
            }

            // The tint itself targets the section too, and must not flag itself.
            if (str_contains($block['selector'], '--tint')) {
                continue;
            }

            if (preg_match('/(?<![-\w])background(?:-[a-z]+)?\s*:/', $block['body']) === 1) {
                $shadowing[] = $block['selector'];
            }
        }

        return array_values(array_unique($shadowing));
    }

    /**
     * .invitation-notice is a <p> inside .invitation-section, so any bare-`p`
     * rule scoped to that section competes for its colour. Both sides are often
     * !important, which leaves specificity as the only tie-break.
     *
     * @return array<int, string>
     */
    private function noticeColoursBeatenByParagraphRules(string $css): array
    {
        $notices = [];
        $paragraphs = [];

        foreach ($this->ruleBlocks($css) as $block) {
            if (preg_match('/(?<![-\w])color\s*:/', $block['body']) !== 1) {
                continue;
            }

            $specificity = $this->specificity($block['selector']);
            $weight = [
                preg_match('/(?<![-\w])color\s*:[^;]*!important/i', $block['body']) === 1 ? 1 : 0,
                $specificity[0],
                $specificity[1],
                $specificity[2],
            ];

            if (str_contains($block['selector'], '.invitation-notice')) {
                $notices[] = ['selector' => $block['selector'], 'weight' => $weight];
            }

            if (preg_match('/(^|[\s>+~,])p$/', $block['selector']) === 1
                && str_contains($block['selector'], 'invitation-section')) {
                $paragraphs[] = ['selector' => $block['selector'], 'weight' => $weight];
            }
        }

        $beaten = [];

        foreach ($notices as $notice) {
            foreach ($paragraphs as $paragraph) {
                if ($this->compareWeights($notice['weight'], $paragraph['weight']) < 0) {
                    $beaten[] = $notice['selector'].' loses to '.$paragraph['selector'];
                }
            }
        }

        return array_values(array_unique($beaten));
    }

    /**
     * @return array{0: int, 1: int, 2: int} ids, classes, elements
     */
    private function specificity(string $selector): array
    {
        $pseudoElements = preg_match_all('/::[A-Za-z0-9_-]+/', $selector);

        $ids = preg_match_all('/#[A-Za-z0-9_-]+/', $selector);
        $classes = preg_match_all('/\.[A-Za-z0-9_-]+/', $selector)
            + preg_match_all('/\[[^\]]*\]/', $selector)
            + max(0, preg_match_all('/(?<!:):[A-Za-z0-9_-]+/', $selector) - $pseudoElements);
        $elements = preg_match_all('/(?:^|[\s>+~,])[a-zA-Z][A-Za-z0-9_-]*/', $selector) + $pseudoElements;

        return [$ids, $classes, $elements];
    }

    /**
     * @param  array<int, int>  $a
     * @param  array<int, int>  $b
     */
    private function compareWeights(array $a, array $b): int
    {
        foreach ($a as $index => $value) {
            if ($value !== $b[$index]) {
                return $value <=> $b[$index];
            }
        }

        return 0;
    }
}
