<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class TemplateRegistry
{
    protected string $templatesPath;

    public function __construct()
    {
        $this->templatesPath = resource_path('invitation-templates');
    }

    /**
     * Get array of template choices for select dropdowns [id => name]
     */
    public function getOptions(?string $eventType = null): array
    {
        $templates = $this->all();

        if ($eventType) {
            $templates = array_filter($templates, function ($t) use ($eventType) {
                return empty($t['event_types']) || in_array($eventType, $t['event_types']);
            });
        }

        $options = [];
        foreach ($templates as $t) {
            $options[$t['id']] = $t['name'].' (v'.($t['version'] ?? '1.0.0').')';
        }

        return $options;
    }

    /**
     * Get all registered templates with their manifests
     */
    public function all(): array
    {
        if (! File::exists($this->templatesPath)) {
            return [];
        }

        $directories = File::directories($this->templatesPath);
        $templates = [];

        foreach ($directories as $dir) {
            $manifestPath = $dir.'/manifest.json';
            if (File::exists($manifestPath)) {
                $manifest = json_decode(File::get($manifestPath), true);
                if ($this->isValid($manifest, basename($dir))) {
                    $templates[$manifest['id']] = $manifest;
                }
            }
        }

        return $templates;
    }

    /**
     * Find template manifest by ID
     */
    public function find(string $id): ?array
    {
        $all = $this->all();

        return $all[$id] ?? null;
    }

    public function previewPath(string $id): ?string
    {
        $manifest = $this->find($id);
        $path = $manifest ? realpath($this->templatesPath.'/'.$id.'/'.basename($manifest['preview'] ?? '')) : false;
        $root = realpath($this->templatesPath);

        return $path && $root && str_starts_with($path, $root.DIRECTORY_SEPARATOR) ? $path : null;
    }

    /**
     * Default section wording declared by a template.
     *
     * Drives the optional label fields in the admin panel: a template that
     * declares none simply never shows them, so no template is forced to
     * support overrides before it is ready.
     *
     * @return array<string, string>
     */
    public function labels(string $id): array
    {
        $labels = $this->find($id)['labels'] ?? [];

        return is_array($labels) ? array_filter($labels, 'is_string') : [];
    }

    /**
     * Declared labels for every template, keyed by template id.
     *
     * The panel builds one field per key any template declares, so reading the
     * manifests once here keeps that from walking the template directory again for
     * every field.
     *
     * @return array<string, array<string, string>>
     */
    public function labelsByTemplate(): array
    {
        $labels = [];

        foreach ($this->all() as $id => $manifest) {
            $declared = $manifest['labels'] ?? [];

            $labels[$id] = is_array($declared) ? array_filter($declared, 'is_string') : [];
        }

        return $labels;
    }

    /**
     * Recommended values for a colour setting, declared by the template.
     *
     * The admin offers these as swatches so an operator can pick a colour that
     * suits the design instead of inventing one. Only plain six digit hex values
     * survive, so a manifest can never inject anything else into the form.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public function colorPresets(string $id, string $key): array
    {
        $presets = $this->find($id)['settings_schema'][$key]['presets'] ?? [];

        if (! is_array($presets)) {
            return [];
        }

        $valid = [];

        foreach ($presets as $preset) {
            if (! is_array($preset)) {
                continue;
            }

            $label = $preset['label'] ?? null;
            $value = $preset['value'] ?? null;

            if (is_string($label) && trim($label) !== '' && is_string($value) && preg_match('/^#[0-9a-f]{6}$/i', $value) === 1) {
                $valid[] = ['label' => trim($label), 'value' => strtolower($value)];
            }
        }

        return $valid;
    }

    /**
     * Whether a template honours the per section height set in the section
     * editor. Declared by the template so the editor never offers a control the
     * chosen template would ignore.
     */
    public function supportsSectionHeight(string $id): bool
    {
        return (bool) ($this->find($id)['section_height'] ?? false);
    }

    private function isValid(mixed $manifest, string $directory): bool
    {
        return is_array($manifest)
            && ($manifest['id'] ?? null) === $directory
            && isset($manifest['name'], $manifest['version'], $manifest['entry_view'], $manifest['sections'])
            && is_array($manifest['sections'])
            && view()->exists($manifest['entry_view']);
    }
}
