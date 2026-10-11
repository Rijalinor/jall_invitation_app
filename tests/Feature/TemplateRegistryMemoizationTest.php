<?php

namespace Tests\Feature;

use App\Services\TemplateRegistry;
use Tests\TestCase;

/**
 * Manifests are read from disk once and reused. The registry is a singleton so
 * a controller, the renderer and the admin form share the same resolved
 * manifests instead of each walking the template directory again.
 */
class TemplateRegistryMemoizationTest extends TestCase
{
    public function test_the_registry_is_a_singleton(): void
    {
        $this->assertSame(
            $this->app->make(TemplateRegistry::class),
            $this->app->make(TemplateRegistry::class),
        );
    }

    public function test_repeated_lookups_return_the_same_manifests(): void
    {
        $registry = $this->app->make(TemplateRegistry::class);

        $first = $registry->all();

        $this->assertNotEmpty($first);
        $this->assertSame($first, $registry->all());
        $this->assertSame($first['elegant-rose'], $registry->find('elegant-rose'));
        $this->assertNotNull($registry->previewPath('elegant-rose'));
    }
}
