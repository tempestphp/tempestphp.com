---
title: A generic tragedy
description: PHP devs talk about generics in real life
tag: thoughts
author: brent
---

PHP isn't getting generics. I guess there's nothing new under the sun, and I probably shouldn't be surprised with the latest RFC vote failing. The main argument for internals to vote "no" is because they hope they can still shove in generic type checking at runtime, even though [previous experiments and testing](https://github.com/PHPGenerics/php-generics-rfc/issues/45) have shown that neither reified nor monomorphized generics would work.

On top of that, the actual target audience for generics — professional developers that rely on static analysis for their day-to-day software development — they have already been using generics via docblocks for a decade. They have proven that statically checked types are a viable approach.

Today I want to give a platform to some of those developers to share their thoughts. These are the people writing PHP day-by-day to build real solutions for real problems. I want their voice to be heard.

---

## From Márk, core Tempest developer and full-time PHP dev

First and foremost, I need to tell you: I will be extremely disappointed if [this RFC](https://wiki.php.net/rfc/bound_erased_generic_types) doesn't pass. But let me start with the least controversial thing I can possibly say. **I've been writing generics in PHP for years, and so have you.**

If you've ever opened a Laravel, Doctrine, Symfony, PHPUnit or PSL class, you've read `@template`. It's [in over 202,000 files on GitHub](https://wiki.php.net/rfc/bound_erased_generic_types#:~:text=Over%20202%2C000%20files%20using%20%40template%2E). It's how every serious collection, repository, and result type in this ecosystem describes itself. We have been shipping erased generics — types that a static analyzer checks and the engine ignores — for about a decade now. So when people talk about this RFC as if it's some exotic new thing, I get a little confused. The only thing the [Bound-Erased Generic Types RFC](https://wiki.php.net/rfc/bound_erased_generic_types) actually proposes is to stop writing those generics inside a comment and pretending it doesn't count.

And the comment tax is real. Think about what a generic Laravel class looks like today. It's written in two languages at once: PHP types in the signatures, PHPDoc types in the docblock right above them. The parser validates one and silently trusts the other. You rename a property in a refactor and the comment quietly goes stale. `ReflectionClass::getName()` hands you `Collection`, never `Collection<{:hl-generic:TKey:}, {:hl-generic:TModel:}>`. PHPStan, Psalm and Mago each read the tricky cases a bit differently, because there's no actual language for any of them to be right about.

This RFC fixes all of that, at exactly zero runtime cost, and at the moment I'm writing this it's losing [4 yes / 12 no / 3 abstain](https://wiki.php.net/rfc/bound_erased_generic_types). I want to talk about why, because the why is the actual tragedy here, and it's more frustrating than "internals hate generics." They don't. The real reasons are worse than that.

### "But native syntax shouldn't lie"

The strongest argument against this RFC isn't stupid, so I'm not going to try to headbutt it. Rowan Tommins put it best on the list:

> But right now, PHP's native syntax does *not* lie - a property marked "private" really is private, a return type marked "int" really is always an integer... This proposal would fundamentally change that - it would introduce syntax which looks like it's part of the standard, enforced, type system; but, in many cases, would do absolutely nothing.

Derick Rethans seconds it from experience, saying users were ["almost exclusively confused when it became clear these types weren't enforced."](https://externals.io/message/130816) Tim Düsterhus sharpens the knife further: [static analyzers "can only prove the presence of errors, but not the absence of them"](https://externals.io/message/130816), and he's right that you cannot fully type-check a PHP program without actually running it.

I sat with that for a while, because it sounds airtight. But... there is always a but. It's an argument about *purity*, and PHP's type system has never once been pure. The runtime already doesn't check the element type of an `{:hl-type:array:}`, the contents of an `{:hl-type:iterable:}`, the parameter or return signature of a `{:hl-type:callable:}`, or anything at all inside `{:hl-type:mixed:}`. Ondřej Mirtes, the author of PHPStan and the person who gave PHP docblock generics in the first place, pointed out [on that very same thread](https://externals.io/message/130816) that there are already corners of shipping PHP where you write native type syntax that is silently never enforced. So the "native syntax must never lie" rule is a rule PHP broke a long time ago, and we all kept happily writing PHP anyway. Attributes are the cleanest example: a docblock-only idea that got real syntax with no runtime effect beyond reflection, and the community [loved them](https://externals.io/message/130816).

The "users will be confused" worry is the one that really doesn't hold, though, because it's had a full decade to come true and it just hasn't. Azjezz's reply is the line I'd put on a poster:

> the core premise is empirically testable, and the test has already run: PHP has had generics in docblocks for a decade, used by every major framework... The failure mode you describe is the one that would occur most under the current system, yet it doesn't.

And the reason it doesn't is almost _boringly_ simple. The people who care enough to write `Collection<{:hl-generic:User:}>` are the same people who run a static analyzer. The overlap between "uses generics" and "runs nothing to check them" is, in practice, basically nobody. Now, [Gina Banyard is right](https://gpb.moe/blog/opinion-bound-erased-generics.html) to push back on the cheerful "90% of projects use static analysis" line, the JetBrains survey puts it closer to 44%, and I'm not going to pretend otherwise. But that 44% is precisely the half that writes generics in the first place. Nobody out there is hand-annotating `<{:hl-generic:TKey:}, {:hl-generic:TModel:}>` across their whole codebase and then running zero tooling against it.

### They're holding out for runtime generics that don't work

So if the objection doesn't actually hold, why are there twelve no votes? Because most of them are still holding out for *reified* generics, types checked at runtime, and they'd rather have nothing at all than accept erasure now.

And here's where I split from most of the room: I don't want runtime-checked generics. Not as a someday-goal, not as the "real" version this one is a placeholder for. Erased is the model I actually want, because it's the one that costs nothing and matches how the ecosystem already works. But set my preference aside, because even on its own terms the holdout makes no sense. PHP has been trying to build runtime generics for ten years and keeps hitting the same wall in the same place. Nikita Popov prototyped them back in 2020 and [laid out in detail why monomorphization "is [not] going to fly"](https://github.com/PHPGenerics/php-generics-rfc/issues/44) in a dynamic engine. The PHP Foundation picked the work back up in 2024 and [ran straight into super-linear type-checking the moment generics met union types](https://thephp.foundation/blog/2024/08/19/state-of-generics-and-collections/). By 2025 the official line had [retreated all the way to "compile-time-only" generics](https://thephp.foundation/blog/2025/08/05/compile-generics/), with `new Repository<{:hl-generic:BlogPost:}>()`, unions and inference all explicitly dropped as "Really Really Hard™, Really Really Slow™, or both." And when Rob Landers actually bolted reification onto *this exact branch* in about a week, the numbers came back [30 to 50% slower on generic-heavy code, pushing toward 2x in the worst case](https://www.reddit.com/r/PHP/comments/1u5pr7v/comment/ornvi98/). That's a tax that compounds through every downstream app that merely depends on a library that happens to use generics.

That's the bird in the bush. And the no votes are letting go of the bird in their hand to keep staring at it. The standard they're really asking for, *prove* reified generics are impossible before we'll even look at erased ones, is one we are [never](https://www.reddit.com/r/PHP/comments/1u5pr7v/comment/ornoin2/) going to meet. You can't prove a negative about an engine that nobody has the time, funding or mandate to rewrite.

### The people who'd actually use it don't get a vote

Here's the moment it stopped feeling like a technical verdict to me and started feeling like a process failure. The discussion was dominated by static-analysis people, who were overwhelmingly in favor. The voters are mostly runtime engine people. As Matthew Brown, the author of Psalm, [put it](https://www.reddit.com/r/PHP/comments/1u5pr7v/comment/orn9v41/), "There is not much overlap between the two." The people who'd use this feature every single day don't get a ballot. The people who'll never write `Collection<{:hl-generic:User:}>` do.

And the no column doesn't even agree with itself. Matteo Beccati, a *current PHP release manager*, voted no for the opposite reason to most of the people next to him in it. His problem with it is that it doesn't go far enough. He argued runtime-checked types made sense as a 2004 call but that ["the decisions made in 2004 should not dominate decision-making today"](https://externals.io/message/130816), and that the proposal's real flaw is that it still keeps some reification in, which "blocks us from having completely erased generics." So the same tally that holds "erasure is a non-starter" also holds "this isn't erased enough." There's no shared position in that column at all. A feature the whole ecosystem has been asking for, for a decade, is dying because twelve people couldn't agree on what they'd rather have instead. Even Larry Garfield, who pushed to hold the vote, did it because, in his words, ["'Internals votes down generics' is the absolute worst outcome, for literally everyone who cares about PHP."](https://externals.io/message/131236) He was right. It will likely happen anyway.

Let me give the other side its due, because I don't think this RFC is flawless. It isn't a perfectly clean erased model. It has real compile-time and link-time enforcement gaps, and the worry that shipping it could turn a future reified design into a nastier breaking change is legitimate, not FUD. But Nicolas Grekas, voting yes, [named the thing that actually matters](https://externals.io/message/130816#130888): docblock generics were adoptable precisely *because* they were invisible to the engine, and native `<{:hl-generic:T:}>` can follow the same gradual path PHP already walked for return types, nullable parameters and everything else.

So no, I'm not surprised the vote is failing. I'm just tired of the shape of it. PHP can have generics. It already runs them, every day, in every framework you depend on. And it's refusing exactly those, to hold out for a version it has spent ten years proving it can't build. That's the tragedy. Not that the bird in the bush is hard to catch. That we keep letting go of the one in our hand just to stand there and stare at it. And honestly, that's just sad.

## From Azjezz, the RFC author

I asked Azjezz if he wanted to pitch in, being the author of the RFC. He told me he didn't have time to write an eloquent blog post, but he did want to contribute and allowed me to quote from a [recent Reddit comment](https://www.reddit.com/r/PHP/comments/1u5pr7v/comment/ornvi98/) in which he explains why he opened the vote even though it was likely to fail. Azjezz explains that the discussion changed from the RFC itself (which was about runtime ignored generics), to instead people wanting reified (runtime checked) generics. He explains: 

> The second category is worth being honest about. I'm not against reified generics in principle. If they were viable in PHP today, I'd rather have them. The reason I didn't pivot the RFC to reified is that "design it better" isn't the problem. Rob Landers implemented reified generics on top of this branch in about a week (https://github.com/php/php-src/compare/master...bottledcode:php-src:reify) and the numbers came in at 30-50% slower on generic-heavy code, approaching 2x in the worst case.
> <br/><br/>
> That cost compounds through the dependency graph. If Psl (or Symfony, PHPUnit, Laravel, Doctrine..etc) ships with native generics under a reified model, every downstream application pays the cost, even apps that never declared a generic of their own. Apply that to CI tooling, and a 10-minutes CI run becomes a 15 to 20-minutes CI run across the entire ecosystem. That's not a number people vote yes on either.

## From Brent — developer advocate for PHP

I've talked about generics in PHP for years. I've made videos, I've written blog posts, I've argued countless times. I write PHP every day. My fellow developers have done a better job in this blog post than I ever could to explaining the technical pros and cons. So let me approach it from another angle.

Whether people want it or not, PHP has become more than just an interpreter. It has become more than "the syntax". The reason PHP is where it is today is not because of how beautiful the language is (I wouldn't say it is particularly beautiful); but because of the richness of its ecosystem. PHP has become more than the language itself. Without the ecosystem of frameworks, packages, and tooling, there would be no PHP anymore.

Meanwhile, there's a group of around 100 people deciding on the future of the language (technically there are around 2000 people eligible to vote, but most don't bother anymore, mind blowing as that is).

I don't feel represented by that group. I know many, _many_ people in the PHP ecosystem feel the same way. Even the PHP Foundation — which is financially backed by the people and companies that make PHP great — even the Foundation is at the mercy of a group of people who apparently have a vision for the language that I don't share.

To me, the generic RFC fail embodies this failure more than anything else.