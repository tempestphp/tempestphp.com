<?php

declare(strict_types=1);

namespace App\Web\Meta;

use App\Web\Blog\BlogRepository;
use App\Web\Documentation\ChapterRepository;
use App\Web\Documentation\Version;
use Spatie\Browsershot\Browsershot;
use Tempest\Container\Tag;
use Tempest\Core\Kernel;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\File;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Session\VerifyCsrfMiddleware;
use Tempest\Router\Get;
use Tempest\Router\SetCurrentUrlMiddleware;
use Tempest\Router\Stateless;
use Tempest\View\ViewRenderer;

use function Tempest\Router\uri;
use function Tempest\support\path;
use function Tempest\View\view;

final readonly class MetaImageController
{
    public function __construct(
        private Kernel $kernel,
        private ViewRenderer $viewRenderer,
        #[Tag('meta')]
        private Browsershot $browsershot,
    ) {}

    #[Stateless, Get('/meta/blog/{slug}')]
    public function blog(string $slug, Request $request, BlogRepository $repository): Response
    {
        $post = $repository->find($slug);

        if ($request->has('html')) {
            $html = $this->viewRenderer->render(view(__DIR__ . '/views/blog.view.php', post: $post));

            return new Ok($html);
        }

        $path = path($this->kernel->root, 'public/meta/meta-blog-' . $slug . '.png')->toString();

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), recursive: true);
        }

        if (! is_file($path) || $request->has('nocache')) {
            $this->browsershot
                ->windowSize(1200, 628)
                ->setUrl(uri([self::class, 'blog'], slug: $slug, html: true))
                ->save($path);
        }

        return new File($path);
    }

    #[Get('/meta/documentation/{version}/{category}/{slug}', without: [SetCurrentUrlMiddleware::class, VerifyCsrfMiddleware::class])]
    public function documentation(string $version, string $category, string $slug, Request $request, ChapterRepository $repository): Response
    {
        $version = Version::from($version);
        $chapter = $repository->find($version, $category, $slug);

        if ($request->has('html')) {
            $html = $this->viewRenderer->render(view(__DIR__ . '/views/documentation.view.php', chapter: $chapter));

            return new Ok($html);
        }

        $path = path($this->kernel->root, "public/meta/meta-documentation-{$version->value}-{$category}-{$slug}.png")->toString();

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), recursive: true);
        }

        if (! is_file($path) || $request->has('nocache')) {
            $this->browsershot
                ->windowSize(1200, 628)
                ->setUrl(uri([self::class, 'documentation'], version: $version->value, category: $category, slug: $slug, html: true))
                ->save($path);
        }

        return new File($path);
    }

    #[Get('/meta/{type}', without: [SetCurrentUrlMiddleware::class, VerifyCsrfMiddleware::class])]
    public function default(string $type, Request $request): Response
    {
        $type = MetaType::tryFrom($type) ?? MetaType::HOME;

        if ($request->has('html')) {
            $html = $this->viewRenderer->render(view($type->getViewPath(), title: $request->get('title'), subtitle: $request->get('subtitle')));

            return new Ok($html);
        }

        $path = path($this->kernel->root, 'public/meta/meta-' . $type->value . '.png')->toString();

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), recursive: true);
        }

        if (! is_file($path) || $request->has('nocache')) {
            $this->browsershot
                ->setUrl(uri([self::class, 'default'], type: $type->value, html: true))
                ->save($path);
        }

        return new File($path);
    }
}
