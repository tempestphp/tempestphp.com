<?php

declare(strict_types=1);

namespace App\Web\Documentation;

use Override;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Router\MatchedRoute;
use Tempest\Router\Router;
use Tempest\Support\Priority;

use function Tempest\Router\uri;
use function Tempest\Support\Arr\get_by_key;
use function Tempest\Support\Regex\matches;
use function Tempest\Support\str;

#[Priority(Priority::HIGHEST)]
final readonly class RedirectMiddleware implements HttpMiddleware
{
    public function __construct(
        private Router $router,
        private MatchedRoute $route,
    ) {}

    #[Override]
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $path = str($request->path);
        $response = $next($request);
        $version = Version::tryFromString(get_by_key($this->route->params, 'version'));

        // If not a docs page, let's just continue normal flow
        if ($this->route->route->uri !== '/{version}/{category}/{slug}') {
            return $response;
        }

        // Redirect to slugs without numbers
        if (matches($this->route->params['category'], '/^\d+-/') || matches($this->route->params['slug'], '/^\d+-/')) {
            return new Redirect($path->replaceRegex('/\/\d+-/', '/')->toString());
        }

        // If no version found, 404
        if ($version === null) {
            return new Redirect(uri([DocumentationController::class, 'index']));
        }

        // Redirect to actual version
        if ($version->getUrlSegment() !== $this->route->params['version']) {
            return new Redirect($path->replace("/{$this->route->params['version']}/", "/{$version->getUrlSegment()}/")->toString());
        }

        return $response;
    }
}
