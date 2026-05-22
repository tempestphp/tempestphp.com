<?php

declare(strict_types=1);

namespace App\Web\Documentation;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Router\MatchedRoute;
use Throwable;

final class VersionInitializer implements Initializer
{
    public function initialize(Container $container): Version
    {
        try {
            $version = $container->get(MatchedRoute::class)?->params['version'] ?? null;
        } catch (Throwable) {
            $version = null;
        }

        if (! $version) {
            return Version::default();
        }

        return Version::tryFromString($version) ?? Version::default();
    }
}
