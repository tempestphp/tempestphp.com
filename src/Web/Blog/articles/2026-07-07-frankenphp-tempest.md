---
title: FrankenPHP worker mode in Tempest
description: We're adding support for FrankenPHP worker mode in Tempest
tag: release
author: brent
---

We just tagged Tempest 3.14.0, which contains preliminary support for FrankenPHP's worker mode. If you don't know what that's about: worker mode allows you to keep a PHP process alive across requests, meaning you only have to boot the framework once, which might lead to increased performance (although it depends on a number of factors).

You can imagine how going from PHP's default "reboot from scratch for every request" approach to "keep as much as possible alive across requests" introduces a bunch of tiny gotchas the framework needs to take care of. Luckily, everything in Tempest is orchestrated through the container, so we already have a central point to manage all of these.

At the heart of worker mode support is the `Container::reset()` method. This method takes care of resetting the application to its original state, ready to handle a new request. We then introduced a `Resettable` interface, so that we can keep reset logic small and contained. As with everything in Tempest, `Resettable` implementations are discovered, so third party packages or projects can hook into the same reset flow without any overhead.

Here's one example of a `Resettable` implementation that comes with Tempest:

```php
use Tempest\Container\Container;
use Tempest\Container\Resettable;
use Tempest\Http\Cookie\CookieManager;

final readonly class CookieReset implements Resettable
{
    public function __construct(
        private Container $container,
    ) {}

    public function reset(): void
    {
        $this->container->unregister(CookieManager::class);
    }
}
```

Pretty simple, right? Resetting the cookie manager means removing the current singleton instance (if there is any) from the container. When the next request comes in, it will be initialized again by its initializer:

```php
final readonly class CookieManagerInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): CookieManager
    {
        return new CookieManager(
            appConfig: $container->get(AppConfig::class),
            clock: $container->get(Clock::class),
        );
    }
}
```

There are some more complex `Resettable` implementations like, for example, for database connections where we need to ensure that all transactions are closed before resetting:

```php

final readonly class ConnectionReset implements Resettable
{
    public function __construct(
        private Container $container,
    ) {}

    public function reset(): void
    {
        // Manually looping over the connection singletons so that we can check whether they still have an active transaction
        if ($this->container instanceof GenericContainer) {
            $connections = $this->container->getSingletons(Connection::class);

            foreach ($connections as $connection) {
                if (! $connection instanceof Connection) {
                    continue;
                }

                if ($connection->inTransaction()) {
                    throw new CouldNotResetConnection("There's still an active transaction, make sure to close it before ending the request");
                }
            }
        }

        $this->container->unregister(Connection::class, tagged: true);
    }
}
```

Apart from resets, we also had to make some changes to the database initializer, for example, because we want to be able to re-use connections across requests. This is actually one of the changes with the biggest performance impact, since establishing a database connection is a fairly expensive operation.


```php
final class DatabaseInitializer implements DynamicInitializer
{
    /** @var Connection[] */
    private static array $connections = [];
    
    /* … */
    
    #[Singleton]
    public function initialize(ClassReflector $class, string|UnitEnum|null $tag, Container $container): Database
    {
        $config = $container->get(DatabaseConfig::class, $tag);
        $connectionKey = $this->getConnectionKey($config);

        $connection = $config->usePersistentConnection
            ? self::$connections[$connectionKey] ?? null
            : null;

        if (! $connection) {
            $connection = new PDOConnection($config);
            $connection->connect();
            self::$connections[$connectionKey] = $connection;
        } elseif ($connection->ping() === false) {
            $connection->reconnect();
        }

        /* … */
    }
}
```

Note that persistent connections are disabled in config by default, so you'd have to enable them:

```php database.config.php
use Tempest\Database\Config\MysqlConfig;

return new MysqlConfig(
    host: env('DATABASE_HOST', default: 'localhost'),
    port: env('DATABASE_PORT', default: '3306'),
    username: env('DATABASE_USERNAME', default: 'root'),
    password: env('DATABASE_PASSWORD', default: ''),
    database: env('DATABASE_DATABASE', default: 'app'),
    persistent: true,
);
```

Finally, there's a new `WorkerModeApplication` that will take care of calling `Container::reset()` instead of exiting the application. Using it in a FrankenPHP worker file would look something like this:

```php public/worker.php
declare(strict_types=1);

use Tempest\Router\WorkerModeApplication;

ignore_user_abort(enable: true);

require_once __DIR__ . '/../vendor/autoload.php';

$app = WorkerModeApplication::boot(root: __DIR__ . '/..');

$handler = static function () use ($app): void {
    $app->run();
};

$maxRequests = (int) ($_SERVER['MAX_REQUESTS'] ?? 0);

for ($n = 0; $maxRequests === 0 || $n < $maxRequests; $n++) {
    $keepRunning = frankenphp_handle_request(callback: $handler);

    gc_collect_cycles();

    if (! $keepRunning) {
        break;
    }
}
```

## Work in progress!

It's crucial to mention that this release lays a foundation in place to build upon. I think it's likely we missed some edge cases, so what's important now is for people to try it out. 

In the long run, we also want to provide a docker setup so that using Tempest with FrankenPHP's worker mode is as easy as running something like `tempest serve --worker`. That's still work in progress though.

## Breaking changes

Finally, we had to make some small changes to a couple of framework-level interfaces so that worker mode could fit in nicely. We don't think these impact anyone; nevertheless, we're committed to ship automated upgrades with every breaking change, so that your codebase can automatically be upgraded for you. You can find the [full list of breaking changes in the PR](https://github.com/tempestphp/tempest-framework/pull/2172).

Start by installing Rector if you haven't yet:

```
composer require rector/rector --dev 
vendor/bin/rector
```

Next, update Tempest; it's important to add the `--no-scripts` flag to prevent any errors from being thrown during the update.

```sh
composer require tempest/framework:^3.14 --no-scripts
```

Then configure Rector to upgrade to Tempest 3.14:

```php
// rector.php

use \Tempest\Upgrade\Set\TempestSetList;

return RectorConfig::configure()
    // …
    ->withSets([TempestSetList::TEMPEST_314]);
```

Next, run Rector:

```
vendor/bin/rector
```

Finally: clear config and discovery caches, and regenerate discovery:

```
rm -r .tempest/cache/config
rm -r .tempest/cache/discovery
./tempest discovery:generate
```

---

I'm sure there's a lot of fine-tuning to do to get worker mode support fully operational, so if you're able to test it, that would be incredibly appreciated. You can always reach out to us via [Discord](/discord) to share your feedback and questions. Thanks!