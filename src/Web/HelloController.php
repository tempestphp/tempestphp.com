<?php

namespace App\Web;

use Tempest\Router\Get;
use Tempest\Router\StaticPage;
use Tempest\View\View;

use function Tempest\View\view;

final class HelloController
{
    #[StaticPage, Get('/hello-tempest'), Get('/hello')]
    public function __invoke(): View
    {
        return view(
            './hello.view.php',
            content: file_get_contents(__DIR__ . '/hello.md'),
        );
    }
}
