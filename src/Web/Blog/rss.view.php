<?php
/** @var \App\Web\Blog\BlogPost[] $posts */
?>

<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
    <id>https://tempestphp.com/rss</id>
    <link rel="self" type="application/atom+xml" href="https://tempestphp.com/rss" />
    <title>Tempest</title>
    <updated>{{ date('c') }}</updated>
    <entry :foreach="$posts as $post">
        <title><![CDATA[ {!! $post->title !!} ]]></title>
        <link rel="alternate" :href="$post->uri"/>
        <id>{{ $post->uri }}</id>
        <category term="PHP"/>
        <author>
            <name>{!! $post->author->getFullName() !!}</name>
            <uri :if="$post->author->getBluesky()">{{ $post->author->getBluesky() }}</uri>
            <uri :else="$post->author->getX()">{{ $post->author->getX() }}</uri>
        </author>
        <summary :if="$post->description" type="html"><![CDATA[ {!! $post->description !!} ]]></summary>
        <content type="html"><![CDATA[ {!! $post->content !!} ]]></content>
        <updated>{{ $post->createdAt->format('c') }}</updated>
        <published>{{ $post->createdAt->format('c') }}</published>
        <media:content url="{{ $post->metaImageUri }}" medium="image"/>
    </entry>
</feed>
