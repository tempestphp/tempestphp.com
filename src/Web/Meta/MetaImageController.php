<?php

declare(strict_types=1);

namespace App\Web\Meta;

use App\Web\Blog\BlogRepository;
use App\Web\Documentation\ChapterRepository;
use App\Web\Documentation\Version;
use GdImage;
use Tempest\Core\Kernel;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\File;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Session\VerifyCsrfMiddleware;
use Tempest\Router\Get;
use Tempest\Router\SetCurrentUrlMiddleware;
use Tempest\Router\Stateless;
use Tempest\View\ViewRenderer;

use function Tempest\support\path;

final readonly class MetaImageController
{
    public function __construct(
        private Kernel $kernel,
        private ViewRenderer $viewRenderer,
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
            $this->saveMetaImage($post->title, $path);
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
            $this->saveMetaImage($chapter->title, $path);
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
            $title = (string) ($request->get('title') ?? match ($type) {
                MetaType::BLOG => 'Blog',
                MetaType::HOME => 'Tempest',
            });

            $this->saveMetaImage($title, $path);
        }

        return new File($path);
    }

    private function saveMetaImage(string $title, string $path): void
    {
        $image = imagecreatefrompng(__DIR__ . '/tempest-meta.png');

        if (! $image instanceof GdImage) {
            return;
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        $fontPath = path($this->kernel->root, 'public/fonts/MonaspaceArgon-Bold.woff')->toString();
        $fontSize = 50;
        $maxWidth = 980;

        do {
            $lines = $this->wrapText($title, $fontPath, $fontSize, $maxWidth);
            $fontSize -= 4;
        } while (count($lines) > 3 && $fontSize >= 44);

        $fontSize += 4;
        $lineHeight = $fontSize + 16;
        $top = (int) round((imagesy($image) - count($lines) * $lineHeight) / 2);
        $color = imagecolorallocate($image, 19, 25, 46);

        foreach ($lines as $index => $line) {
            $box = $this->textBox($line, $fontPath, $fontSize);
            $x = (int) round((imagesx($image) - $box['width']) / 2) - $box['left'];
            $y = $top + $index * $lineHeight + $box['height'];

            imagettftext($image, $fontSize, 0, $x, $y, $color, $fontPath, $line);
        }

        imagepng($image, $path);
    }

    /** @return list<string> */
    private function wrapText(string $text, string $fontPath, int $fontSize, int $maxWidth): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $candidate = $line === '' ? $word : "{$line} {$word}";

            if ($this->textBox($candidate, $fontPath, $fontSize)['width'] <= $maxWidth) {
                $line = $candidate;

                continue;
            }

            if ($line !== '') {
                $lines[] = $line;
            }

            $line = $word;
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }

    /** @return array{width: int, height: int, left: int} */
    private function textBox(string $text, string $fontPath, int $fontSize): array
    {
        $box = imagettfbbox($fontSize, 0, $fontPath, $text);

        return [
            'width' => $box[2] - $box[0],
            'height' => $box[1] - $box[7],
            'left' => $box[0],
        ];
    }
}
