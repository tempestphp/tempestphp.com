<?php

use App\Web\Meta\MetaType;

?>

<html lang="en" class="flex flex-col h-dvh scroll-smooth">
<head>
    <!-- Meta title -->
    <?php $title = match (true) {
        isset($fullTitle) => $fullTitle,
        isset($title) => "{$title} — Tempest",
        default => 'Tempest',
    }; ?>

    <title>{{ $title }}</title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="title" :content="$title">
    <meta name="twitter:title" :content="$title">
    <meta property="og:title" :content="$title">
    <meta itemprop="name" :content="$title">
    <link :if="$meta['canonical'] ?? null" rel="canonical" :href="$meta['canonical']" />

    <!-- Meta description -->
    <?php $description = match (true) {
        isset($description) => $description,
        default => 'Tempest is a modern framework designed to enable developers to write as little framework-specific code as possible, so that they can focus on application code instead.',
    }; ?>

    <meta name="description" :content="$description">
    <meta name="twitter:description" :content="$description">
    <meta property="og:description" :content="$description">
    <meta itemprop="description" :content="$description">
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png"/>
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png"/>
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png"/>
    <link rel="manifest" href="/favicon/site.webmanifest"/>
    <!-- Vite tags -->
    <x-vite-tags/>
    <x-slot name="head"/>
</head>
<body class="relative antialiased flex flex-col grow selection:bg-(--ui-primary)/20 selection:text-(--ui-primary) text-(--ui-text) bg-(--ui-bg) scheme-light dark:scheme-dark !overflow-visible !pr-0">
  <div :if="! ($blank ?? false)" id="background" class="z-[-1] absolute inset-0 w-full h-full overflow-hidden pointer-events-none" />
  <x-slot/>
</body>
</html>
