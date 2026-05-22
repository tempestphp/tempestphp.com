<?php

declare(strict_types=1);

namespace App\Markdown;

use Tempest\Support\Arr;

use function Tempest\Support\Arr\sort_by_callback;
use function Tempest\Support\Str\strip_tags;

final class SubChapterExtractor
{
    /**
     * @return array<string, array{title: string, children: array<string, string>}>
     */
    public static function extract(string $content): array
    {
        preg_match_all('/<h2.*>.*<a.*href="(?<uri>.*?)".*?<\/span>(?<title>.*)<\/a><\/h2>/', $content, $h2_matches, flags: PREG_OFFSET_CAPTURE);
        preg_match_all('/<h3.*>.*<a.*href="(?<h3uri>.*?)".*?<\/span>(?<h3title>.*)<\/a><\/h3>/', $content, $h3_matches, flags: PREG_OFFSET_CAPTURE);

        /** @var array<int, array{level: int, uri: string, title: string, position: int}> */
        $headings = [];

        foreach ($h2_matches['uri'] as $key => [$uri, $position]) {
            $headings[] = [
                'level' => 2,
                'uri' => $uri,
                'title' => strip_tags($h2_matches['title'][$key][0]),
                'position' => $position,
            ];
        }

        foreach ($h3_matches['h3uri'] as $key => [$uri, $position]) {
            $headings[] = [
                'level' => 3,
                'uri' => $uri,
                'title' => strip_tags($h3_matches['h3title'][$key][0]),
                'position' => $position,
            ];
        }

        return self::buildHierarchy(
            headings: sort_by_callback($headings, static fn (array $a, array $b) => $a['position'] <=> $b['position']),
        );
    }

    /**
     * @param array<int, array{level: int, uri: string, title: string, position: int}> $headings
     * @return array<string, array{title: string, children: array<string, string>}>
     */
    private static function buildHierarchy(array $headings): array
    {
        $chapters = [];
        $current_h2_uri = null;

        foreach ($headings as $heading) {
            if ($heading['level'] === 2) {
                $current_h2_uri = $heading['uri'];
                $chapters[$heading['uri']] = [
                    'title' => $heading['title'],
                    'children' => [],
                ];
                continue;
            }

            if ($current_h2_uri !== null) {
                $chapters[$current_h2_uri]['children'][$heading['uri']] = $heading['title'];
                continue;
            }

            $chapters[$heading['uri']] = [
                'title' => $heading['title'],
                'children' => [],
            ];
        }

        return $chapters;
    }
}
