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

        // Fallback default starter template if none discovered yet
        if (empty($options)) {
            $options['elegant-rose'] = 'Elegant Rose (v1.0.0)';
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

    private function isValid(mixed $manifest, string $directory): bool
    {
        return is_array($manifest)
            && ($manifest['id'] ?? null) === $directory
            && isset($manifest['name'], $manifest['version'], $manifest['entry_view'], $manifest['sections'])
            && is_array($manifest['sections'])
            && view()->exists($manifest['entry_view']);
    }
}
