---
title: A generic tragedy
description: PHP devs talk about generics in real life
tag: thoughts
author: brent
---

PHP isn't getting generics. I guess there's nothing new under the sun, and I probably shouldn't be surprised with the latest RFC vote failing. The main argument for internals to vote "no" is because they hope they can still shove in generic type checking at runtime, even though [previous experiments and testing](https://github.com/PHPGenerics/php-generics-rfc/issues/45) have shown that neither reified nor monomorphized generics would work.

On top of that, the actual target audience for generics — professional developers that rely on static analysis for their day-to-day software development — they have already been using generics via docblocks for a decade. They have proven that statically checked types are a viable approach.

Today I want to give a platform to some of those developers to share their thoughts. These are the people writing PHP day-by-day to build real solutions for real problems. I want their voice to be heard.

## Márk

## Azjezz, the RFC author

I asked Azjezz if he wanted to pitch in, being the author of the RFC. He told me he didn't have time to write an eloquent blog post, but he did want to contribute and allowed me to quote from a [recent Reddit comment](https://www.reddit.com/r/PHP/comments/1u5pr7v/comment/ornvi98/) in which he explains why he opened the vote even though it was likely to fail. Azjezz explains that the discussion changed from the RFC itself (which was about runtime ignored generics), to instead people wanting reified (runtime checked) generics. He explains: 

> The second category is worth being honest about. I'm not against reified generics in principle. If they were viable in PHP today, I'd rather have them. The reason I didn't pivot the RFC to reified is that "design it better" isn't the problem. Rob Landers implemented reified generics on top of this branch in about a week (https://github.com/php/php-src/compare/master...bottledcode:php-src:reify) and the numbers came in at 30-50% slower on generic-heavy code, approaching 2x in the worst case.
> <br/><br/>
> That cost compounds through the dependency graph. If Psl (or Symfony, PHPUnit, Laravel, Doctrine..etc) ships with native generics under a reified model, every downstream application pays the cost, even apps that never declared a generic of their own. Apply that to CI tooling, and a 10-minutes CI run becomes a 15 to 20-minutes CI run across the entire ecosystem. That's not a number people vote yes on either.