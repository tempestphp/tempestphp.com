<?php

declare(strict_types=1);

namespace App\Web\Meta;

use App\Web\Blog\BlogRepository;
use App\Web\Documentation\ChapterRepository;
use App\Web\Documentation\Version;
use Tempest\Core\Kernel;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\File;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Session\VerifyCsrfMiddleware;
use Tempest\Router\Get;
use Tempest\Router\SetCurrentUrlMiddleware;
use Tempest\Router\Stateless;

use function Tempest\support\path;

final readonly class MetaImageController
{
    public function __construct(
        private Kernel $kernel,
        private MetaImageRenderer $imageRenderer,
    ) {}

    #[Stateless, Get('/meta/blog/{slug}')]
    public function blog(string $slug, Request $request, BlogRepository $repository): Response
    {
        $post = $repository->find($slug);

        if ($post === null) {
            return new NotFound();
        }

        $path = path($this->kernel->root, 'public/meta/meta-blog-' . $slug . '.png')->toString();

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), recursive: true);
        }

        if (! is_file($path) || $request->has('nocache')) {
            $this->imageRenderer->save($post->title, $path);
        }

        return new File($path);
    }

    #[Get('/meta/documentation/{version}/{category}/{slug}', without: [SetCurrentUrlMiddleware::class, VerifyCsrfMiddleware::class])]
    public function documentation(string $version, string $category, string $slug, Request $request, ChapterRepository $repository): Response
    {
        $version = Version::from($version);
        $chapter = $repository->find($version, $category, $slug);

        $path = path($this->kernel->root, "public/meta/meta-documentation-{$version->value}-{$category}-{$slug}.png")->toString();

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), recursive: true);
        }

        if (! is_file($path) || $request->has('nocache')) {
            $this->imageRenderer->save($chapter->title, $path);
        }

        return new File($path);
    }

    #[Get('/meta/{type}', without: [SetCurrentUrlMiddleware::class, VerifyCsrfMiddleware::class])]
    public function default(string $type, Request $request): Response
    {
        $type = MetaType::tryFrom($type) ?? MetaType::HOME;

        $path = path($this->kernel->root, 'public/meta/meta-' . $type->value . '.png')->toString();

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), recursive: true);
        }

        if (! is_file($path) || $request->has('nocache')) {
            $title = (string) (
                $request->get('title') ?? match ($type) {
                    MetaType::BLOG => 'Blog',
                    MetaType::HOME => 'Tempest',
                }
            );

            $this->imageRenderer->save($title, $path);
        }

        return new File($path);
    }
}
