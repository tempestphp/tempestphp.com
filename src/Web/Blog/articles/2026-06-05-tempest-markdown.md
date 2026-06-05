---
title: A new Markdown parser
description: Introducing tempest/markdown, its design goals, and how it works
tag: thoughts
author: brent
---

What started as a performance experiment ended as a new package: `tempest/markdown`. I read [this post on Reddit](https://www.reddit.com/r/PHP/comments/1tac5j9/mdparser_030_native_php_commonmark_gfm_parser/) about how someone built a Markdown parser as a PHP extension. They mentioned how much faster it was compared to `league/commonmark`, which was the biggest selling point. 

Now, I do a lot with Markdown: from blogs to docs, from mails to books, most of the things I do online involve parsing Markdown in some way. And for as long as I can remember, I've used `league/commonmark` to do so. Indeed, it's not the fastest thing out there — but it's manageable. However, with the [100-million-row challenge](/challenges/parsing-100m-lines) still fresh on my mind, I wondered if we really needed an _extension_ to get better Markdown performance. Having used League's implementation for years, I know they heavily rely on regex; which I learned with the 100-million-row challenge, was never the most performant solution for parsing big blobs of text.

So I set up a naive test: a very basic Markdown parser that doesn't rely on regex but instead does a single pass over the text input, translates Markdown into tokens, which are then rendered to HTML. It's not a full-fledged lexer/parser that builds an AST, but instead directly goes from tokens to HTML. After a couple of hours, I got a working prototype. Then I set up [phpbench](https://github.com/phpbench/phpbench) to compare my implementation with league's. 


| Package                | Memory   | Time to parse |
|------------------------|----------|---------------|
| tempest/markdown       | 5.944mb  | 6.281ms       |
| league/commonmark      | 21.114mb | 56.993ms      |

Of course, my implementation was far from feature-complete, so I figured these numbers weren't accurate yet. However, the difference did show that there might be something to improve, and that a non-regex approach may indeed be faster.

I did wonder whether I missed something obvious, though. The difference in performance was pretty big, and I hadn't even tried that hard. So I did the most productive thing I could think of to verify whether an idea has merit: [I asked /r/php to roast my code](https://www.reddit.com/r/PHP/comments/1tbyepk/roast_my_code_im_building_a_markdown_parser/). The feedback was very valuable, but what stood out most was someone sending a PR to the repo with ["some performance improvements"](https://github.com/tempestphp/markdown/pull/3):

| Package               | Time to parse |
|-----------------------|---------------|
| tempest/markdown      | 6.281ms       |
| tempest/markdown (PR) | 0.723ms       |
| league/commonmark     | 56.993ms      |

Well that, I did not expect. 0.723ms to parse the Tempest docs in PHP compared to 56.993ms with `league/commonmark`. That's an 80x improvement — give or take; all with PHP. There was a catch, though: the PR did two things: it merged the tokenization and parsing steps into one; but it also removed all tokenizer rule classes (each class representing a specific Markdown token); and merged them into inline functions.

The inline function approach worked, but it made it virtually impossible to add extension points, something I was considering whether it would be worth adding. See, having worked on this code for a couple of days by now, I wondered whether it could actually benefit me for real. Better performance is always good, but we're talking only about a tens of milliseconds difference. `league/commonmark` can definitely feel sluggish at times, but in production these rendered Markdown files are always cached anyway, so it's definitely not the end of the world.

What bothered me more with `league/commonmark` is the fact that it's so bare-bones. Every project I start I have to copy over configuration to support frontmatter, code highlighting, responsive images, tables, external hyperlinks, and what not. There are solutions for all these problems, but `league/commonmark` was designed to be extended, so it takes some setting up and tweaking before I can use it for my use cases.

If I had this Markdown parser that 5-10x faster, with all these features built-in; maybe that wouldn't be so bad? 

I so I did exactly that; I continued to add the base Markdown features, and then I added support for all the things _I_ would find useful: frontmatter, code highlighting, responsive images, tables, external hyperlinks, divs, and strikethrough formatting. In the end, the benchmarks showed these results:


| Package                | Memory   | Time to parse |
|------------------------|----------|---------------|
| tempest/markdown       | 6.664mb  | 10.906ms      |
| league/commonmark      | 21.114mb | 56.993ms      |

As expected, performance had decreased a bit, but `tempest/markdown` was still 5x faster than `league/commonmark`. I actually suspect there are some big gains to be made still by combining the parsing and HTML rendering in one loop instead of two (TBD).

On top of that, I did add extension points so that external projects could completely change the parser's working to their needs.

So that's where I'm at today. Once again I wonder: what's the next step? And once again, I think it's time to ask /r/php and other places to take another look at what's here. I'm now using the parser myself for my blog and this website. It works very well, it has simplified a lot of code, and I'm happy with it. But is there really something here? I hope others can help me figure that out. 

So if you're curious, head over to [the docs](/docs/packages/markdown) and take a look. I'm very open for feedback! (The best place for that feedback would be on [GitHub](https://github.com/tempestphp/markdown), by the way.)

## Why not … ?

As a closing remark: I am anticipating people asking why I don't contribute to `league/commonmark` instead; why I have to write something new.

Well the two obvious reasons are that `league/commonmark` is a regex-based parser by design, and that's not something you just _change_; also it seems to be designed to only follow the official spec, and leave extension points to the community. The two design goals of `tempest/markdown` seem to be diametrically opposed to `league/commonmark`. That's not to say there's anything wrong with one approach or the other, but they are so different that I don't see any way of them working together.

## In closing

Let me know your thoughts! Either on [GitHub](https://github.com/tempestphp/markdown) or on [the Tempest Discord](/discord), or whever you're reading this. I'm looking forward to it!