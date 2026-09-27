<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TemplateLabelContractTest extends TestCase
{
    /**
     * Section wording lives in two places: the manifest declares the default and
     * the view reads it back. A typo on either side ships wrong wording or an
     * undefined index, and neither would fail any other test, so both sides are
     * held to the same list for every template that declares labels.
     */
    public function test_a_template_view_and_its_manifest_declare_the_same_labels(): void
    {
        foreach ($this->templates() as $name => [$manifest, $view]) {
            $labels = $manifest['labels'] ?? null;

            if (! is_array($labels)) {
                continue;
            }

            preg_match_all('/\$labels\[\'(?<key>[a-z0-9_]+)\'\]/', $view, $matches);
            $read = array_values(array_unique($matches['key']));

            $this->assertNotSame([], $read, sprintf(
                'Template "%s" declares labels but its view reads none.', $name,
            ));

            $missing = array_values(array_diff($read, array_keys($labels)));

            $this->assertSame([], $missing, sprintf(
                'Template "%s" reads labels its manifest does not declare: %s. The view would render nothing.',
                $name,
                implode(', ', $missing),
            ));

            $unused = array_values(array_diff(array_keys($labels), $read));

            $this->assertSame([], $unused, sprintf(
                'Template "%s" declares labels its view never reads: %s. Editing them in the admin would do nothing.',
                $name,
                implode(', ', $unused),
            ));
        }
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string}>
     */
    private function templates(): array
    {
        $templates = [];

        // The shared partials are part of every template's output, so a label read
        // there counts as read by the template too.
        $shared = '';

        foreach (glob(dirname(__DIR__, 2).'/resources/views/invitations/shared/*.blade.php') ?: [] as $partial) {
            $shared .= (string) file_get_contents($partial);
        }

        foreach (glob(dirname(__DIR__, 2).'/resources/invitation-templates/*', GLOB_ONLYDIR) as $directory) {
            $manifestPath = $directory.'/manifest.json';
            $viewPath = $directory.'/views/index.blade.php';

            if (! is_file($manifestPath) || ! is_file($viewPath)) {
                continue;
            }

            $templates[basename($directory)] = [
                (array) json_decode((string) file_get_contents($manifestPath), true),
                (string) file_get_contents($viewPath).$shared,
            ];
        }

        $this->assertNotSame([], $templates);

        return $templates;
    }
}
