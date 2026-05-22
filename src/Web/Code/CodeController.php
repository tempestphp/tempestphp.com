<?php

declare(strict_types=1);

namespace App\Web\Code;

use Tempest\Container\Tag;
use Tempest\Highlight\Highlighter;
use Tempest\Http\Request;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\View\View;

use function Tempest\Router\uri;
use function Tempest\View\view;

final readonly class CodeController
{
    #[Get('/code')]
    public function paste(Request $request): View
    {
        $language = $request->get('lang') ?? 'php';
        $code = $request->get('code') ?? '';

        if ($code) {
            $code = urldecode(base64_decode($code, strict: true));
        }

        return \Tempest\View\view(__DIR__ . '/code.view.php', code: $code, language: $language);
    }

    #[Post('/code/submit')]
    public function submit(Request $request): Redirect
    {
        $code = $request->get('code');

        $language = $request->get('lang', 'php');

        $code = urlencode(base64_encode($code));

        return new Redirect(uri([self::class, 'preview']) . '?lang=' . $language . '&code=' . $code);
    }

    #[Get('/code/preview')]
    public function preview(
        Request $request,
        #[Tag('project')]
        Highlighter $highlighter,
    ): View {
        $code = $request->get('code') ?? urlencode(base64_encode('// Hello world'));

        $language = $request->get('lang') ?? 'php';

        $highlightedCode = $highlighter->parse(urldecode(base64_decode($code, strict: true)), $language);

        $center = $request->has('center');

        $clean = $request->has('clean');

        $editUrl = uri([self::class, 'paste'], lang: $language, code: $code);
        $cleanUrl = uri([self::class, 'preview'], lang: $language, code: $code, center: $center, clean: true);

        return \Tempest\View\view(__DIR__ . '/code_preview.view.php')
            ->data(
                code: $highlightedCode,
                editUrl: $editUrl,
                language: $language,
                center: $center,
                clean: $clean,
                cleanUrl: $cleanUrl,
            );
    }
}
