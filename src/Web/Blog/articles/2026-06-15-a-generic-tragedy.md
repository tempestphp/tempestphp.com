---
title: A generic tragedy
description: PHP devs talk about generics in real life
tag: thoughts
author: brent
---

PHP isn't getting generics. I guess there's nothing new under the sun, and I probably shouldn't be surprised with the latest RFC vote failing. The main argument for internals to vote "no" is because they hope they can still shove in generic type checking at runtime, even though [previous experiments and testing](https://github.com/PHPGenerics/php-generics-rfc/issues/45) have shown that neither reified nor monomorphized generics would work.

On top of that, the actual target audience for generics — professional developers that rely on static analysis for their day-to-day software development — they have already been using generics via docblocks for a decade. They have proven that statically checked types are a viable approach.

Today I want to give a platform to some of those developers to share their thoughts. These are the people writing PHP day-by-day to build real solutions for real problems.

## Márk