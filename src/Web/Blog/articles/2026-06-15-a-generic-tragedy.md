---
title: A generic tragedy
description: PHP devs talk about generics in real life
tag: thoughts
author: brent
---

Most likely, PHP isn't getting generics. There's nothing new under the sun. Some internals have written very elaborate blog posts on why runtime-erased generics are a bad idea, and why they are [voting no on the current RFC](https://wiki.php.net/rfc/bound_erased_generic_types#vote).

However, there's another side of the PHP community that are very much in favor of this RFC, who also wants to voice their opinion. Some of them don't have the platform to do so themselves, which is why I want to give the other side of the story a voice, here on this page. These are the stories and comments from PHP developers who use generics in real life and have embraced static analysis as a core part of PHP. They'd like to share their thoughts.

- [Nicolas Grekas](#from-nicolas,-core-maintainer-of-symfony)
- [Márk Magyar](#from-márk,-tempest-core-developer-and-full-time-php-dev)
- [Azjezz](#from-azjezz,-the-rfc-author)
- [Nuno Maduro](#from-nuno,-staff-software-engineer-at-laravel)
- [Brent Roose](#from-brent,-developer-advocate-for-php-at-jetbrains)
- [You?](#and-how-about-you?)

---

## From Nicolas, core maintainer of Symfony

There's extensively-documented prior research on generics in PHP. The conclusion is bold: the PHP engine can't efficiently implement generics (challenges are inference to not ruin DX, performance to not ruin costs and many others). The only practical solution is an ahead of time static analyser. Dreaming of one built into PHP itself, likely written in C, is fine but not realistic: PHP static analyzers were developed in PHP for a reason. This allowed fast iterations, by people focused on their proficiency language. Moving this experience to the engine-side would eject the very people that made SA in PHP a thing. That'd be a too big loss for the community. 

Erased generics as proposed are a direct continuation on this path, which is already proved viable and useful. Thanks to IDEs, SA in PHP has a much wider user-base than just the active users of phpstan, psalm, et al. With LLMs generating code, it's even more critical for PHP to have a stronger verification step. SA tools are a mandatory part of the loop nowadays. We did already wait and dream for years about babysteps towards generics. Even generic arrays has been ruled out as too hard. We waited long enough to draw the conclusion: erased-generics are the only realistic step forward. I'd be happy to be wrong. Let's see in some years (if people/LLMs still write PHP then).

---

## From Márk, Tempest core developer and full-time PHP dev

First and foremost, I need to tell you: I will be extremely disappointed if [this RFC](https://wiki.php.net/rfc/bound_erased_generic_types) doesn't pass. But let me start with the least controversial thing I can possibly say. **I've been writing generics in PHP for years, and so have you.**

If you've ever opened a Laravel, Doctrine, Symfony, PHPUnit or PSL class, you've read `@template`. It's [in over 202,000 files on GitHub](https://wiki.php.net/rfc/bound_erased_generic_types#:~:text=Over%20202%2C000%20files%20using%20%40template%2E). It's how every serious collection, repository, and result type in this ecosystem describes itself. We have been shipping erased generics — types that a static analyzer checks and the engine ignores — for about a decade now. So when people talk about this RFC as if it's some exotic new thing, I get a little confused. The only thing the [Bound-Erased Generic Types RFC](https://wiki.php.net/rfc/bound_erased_generic_types) actually proposes is to stop writing those generics inside a comment and pretending it doesn't count.

And the comment tax is real. Think about what a generic Laravel class looks like today. It's written in two languages at once: PHP types in the signatures, PHPDoc types in the docblock right above them. The parser validates one and silently trusts the other. You rename a property in a refactor and the comment quietly goes stale. `ReflectionClass::getName()` hands you `Collection`, never `Collection<{:hl-generic:TKey:}, {:hl-generic:TModel:}>`. PHPStan, Psalm and Mago each read the tricky cases a bit differently, because there's no actual language for any of them to be right about.

This RFC fixes all of that, at exactly zero runtime cost, and at the moment I'm writing this it's losing [4 yes / 12 no / 3 abstain](https://wiki.php.net/rfc/bound_erased_generic_types). I want to talk about why, because the why is the actual tragedy here, and it's more frustrating than "internals hate generics." They don't. The real reasons are worse than that.

### "But native syntax shouldn't lie"

The strongest argument against this RFC isn't stupid, so I'm not going to try to headbutt it. Rowan Tommins put it best on the list:

> But right now, PHP's native syntax does *not* lie - a property marked "private" really is private, a return type marked "int" really is always an integer... This proposal would fundamentally change that - it would introduce syntax which looks like it's part of the standard, enforced, type system; but, in many cases, would do absolutely nothing.

Derick Rethans seconds it from experience, saying users were ["almost exclusively confused when it became clear these types weren't enforced."](https://externals.io/message/130816#131010) Tim Düsterhus sharpens the knife further: [static analyzers "can only prove the presence of errors, but not the absence of them"](https://externals.io/message/130816#130883), and he's right that you cannot fully type-check a PHP program without actually running it.

I sat with that for a while, because it sounds airtight. But... there is always a but. It's an argument about *purity*, and PHP's type system has never once been pure. The runtime already doesn't check the element type of an `{:hl-type:array:}`, the contents of an `{:hl-type:iterable:}`, the parameter or return signature of a `{:hl-type:callable:}`, or anything at all inside `{:hl-type:mixed:}`. Ondřej Mirtes, the author of PHPStan and the person who gave PHP docblock generics in the first place, pointed out [on that very same thread](https://externals.io/message/130816#131131) that there are already corners of shipping PHP where you write native type syntax that is silently never enforced. So the "native syntax must never lie" rule is a rule PHP broke a long time ago, and we all kept happily writing PHP anyway. Attributes are the cleanest example: a docblock-only idea that got real syntax with no runtime effect beyond reflection, and the community [loved them](https://externals.io/message/130816#130825).

The "users will be confused" worry is the one that really doesn't hold, though, because it's had a full decade to come true and it just hasn't. Azjezz's reply is the line I'd put on a poster:

> the core premise is empirically testable, and the test has already run: PHP has had generics in docblocks for a decade, used by every major framework... The failure mode you describe is the one that would occur most under the current system, yet it doesn't.

And the reason it doesn't is almost _boringly_ simple. The people who care enough to write `Collection<{:hl-generic:User:}>` are the same people who run a static analyzer. The overlap between "uses generics" and "runs nothing to check them" is, in practice, basically nobody. Now, [Gina Banyard is right](https://gpb.moe/blog/opinion-bound-erased-generics.html) to push back on the cheerful "90% of projects use static analysis" line, the JetBrains survey puts it closer to 44%, and I'm not going to pretend otherwise. But that 44% is precisely the half that writes generics in the first place. Nobody out there is hand-annotating `<{:hl-generic:TKey:}, {:hl-generic:TModel:}>` across their whole codebase and then running zero tooling against it.

### They're holding out for runtime generics that don't work

So if the objection doesn't actually hold, why are there twelve no votes? Because most of them are still holding out for *reified* generics, types checked at runtime, and they'd rather have nothing at all than accept erasure now.

And here's where I split from most of the room: I don't want runtime-checked generics. Not as a someday-goal, not as the "real" version this one is a placeholder for. Erased is the model I actually want, because it's the one that costs nothing and matches how the ecosystem already works. But set my preference aside, because even on its own terms the holdout makes no sense. PHP has been trying to build runtime generics for ten years and keeps hitting the same wall in the same place. Nikita Popov prototyped them back in 2020 and [laid out in detail why monomorphization "is [not] going to fly"](https://github.com/PHPGenerics/php-generics-rfc/issues/44) in a dynamic engine. The PHP Foundation picked the work back up in 2024 and [ran straight into super-linear type-checking the moment generics met union types](https://thephp.foundation/blog/2024/08/19/state-of-generics-and-collections/). By 2025 the official line had [retreated all the way to "compile-time-only" generics](https://thephp.foundation/blog/2025/08/05/compile-generics/), with `new Repository<{:hl-generic:BlogPost:}>()`, unions and inference all explicitly dropped as "Really Really Hard™, Really Really Slow™, or both." And when Rob Landers actually bolted reification onto *this exact branch* in about a week, the numbers came back [30 to 50% slower on generic-heavy code, pushing toward 2x in the worst case](https://www.reddit.com/r/PHP/comments/1u5pr7v/comment/ornvi98/). That's a tax that compounds through every downstream app that merely depends on a library that happens to use generics.

That's the bird in the bush. And the no votes are letting go of the bird in their hand to keep staring at it. The standard they're really asking for, *prove* reified generics are impossible before we'll even look at erased ones, is one we are [never](https://www.reddit.com/r/PHP/comments/1u5pr7v/comment/ornoin2/) going to meet. You can't prove a negative about an engine that nobody has the time, funding or mandate to rewrite.

### The people who'd actually use it don't get a vote

Here's the moment it stopped feeling like a technical verdict to me and started feeling like a process failure. The discussion was dominated by static-analysis people, who were overwhelmingly in favor. The voters are mostly runtime engine people. As Matthew Brown, the author of Psalm, [put it](https://www.reddit.com/r/PHP/comments/1u5pr7v/comment/orn9v41/), "There is not much overlap between the two." The people who'd use this feature every single day don't get a ballot. The people who'll never write `Collection<{:hl-generic:User:}>` do.

And Brown didn't only say it on Reddit. In the internals thread itself he told them ["the decisions made in 2004 should not dominate decision-making today"](https://externals.io/message/130816#131014): runtime checks made sense back when there was nothing better, but static analysis now finds more bugs, earlier and faster, than the runtime ever could. The man wrote one of the two tools the whole ecosystem leans on, and his name isn't in the tally. A feature people have asked for, for a decade, is dying because the ones who'd use it aren't the ones deciding. Even Larry Garfield, who pushed azjezz to hold off on the vote, did it because, in his words, ["'Internals votes down generics' is the absolute worst outcome, for literally everyone who cares about PHP."](https://externals.io/message/131236#131244) He was right. It will likely happen anyway.

Let me give the other side its due, because I don't think this RFC is flawless. It isn't a perfectly clean erased model. It has real compile-time and link-time enforcement gaps, and the worry that shipping it could turn a future reified design into a nastier breaking change is legitimate, not FUD. But Nicolas Grekas, voting yes, [named the thing that actually matters](https://externals.io/message/130816#130888): docblock generics were adoptable precisely *because* they were invisible to the engine, and native `<{:hl-generic:T:}>` can follow the same gradual path PHP already walked for return types, nullable parameters and everything else.

So no, I'm not surprised the vote is failing. I'm just tired of the shape of it. PHP can have generics. It already runs them, every day, in every framework you depend on. And it's refusing exactly those, to hold out for a version it has spent ten years proving it can't build. That's the tragedy. Not that the bird in the bush is hard to catch. That we keep letting go of the one in our hand just to stand there and stare at it. And honestly, that's just sad.

---

## From Azjezz, the RFC author

I asked Azjezz if he wanted to pitch in, being the author of the RFC. He told me he didn't have time to write an eloquent blog post, but he did want to contribute and allowed me to quote from a [recent Reddit comment](https://www.reddit.com/r/PHP/comments/1u5pr7v/comment/ornvi98/) in which he explains why he opened the vote even though it was likely to fail. Azjezz explains that the discussion changed from the RFC itself (which was about runtime ignored generics), to instead people wanting reified (runtime checked) generics. He explains: 

> The second category is worth being honest about. I'm not against reified generics in principle. If they were viable in PHP today, I'd rather have them. The reason I didn't pivot the RFC to reified is that "design it better" isn't the problem. Rob Landers implemented reified generics on top of this branch in about a week (https://github.com/php/php-src/compare/master...bottledcode:php-src:reify) and the numbers came in at 30-50% slower on generic-heavy code, approaching 2x in the worst case.
> <br/><br/>
> That cost compounds through the dependency graph. If Psl (or Symfony, PHPUnit, Laravel, Doctrine..etc) ships with native generics under a reified model, every downstream application pays the cost, even apps that never declared a generic of their own. Apply that to CI tooling, and a 10-minutes CI run becomes a 15 to 20-minutes CI run across the entire ecosystem. That's not a number people vote yes on either.

On the question of why Azjezz opened voting on his RFC early without further exploring the option of adding reidied generics on top of it, he says this:

> And honestly: I'm not going to spend my time on reified generics, because the way I see it, they're not going to happen. Not because I don't want them, I do. Making them performant enough to actually ship in PHP requires structural engine changes that I have neither the time, the freedom, nor the mandate to do. I'm one person working on this in my own time, between other commitments. Asking me to rewrite the PHP engine to make reified generics viable, and to break things in the process, isn't a realistic ask. And shipping a reified RFC without that rewrite gets us exactly where Rob's branch already is: a working implementation with a perf profile the community won't accept.
> <br><br>
> …
> <br><br>
> If reified generics ever become viable in PHP, it'll be because someone with the time, the resources, and the engine-level mandate makes that path exist. I'd happily support a follow-up RFC the moment that's the case. Until then, "is the static-analysis convergence worth shipping bound-erased generics?" is the actual question on the ballot.

---

## From Nuno, staff software engineer at Laravel

I’ve been using generics through PHPStan in pretty much all my code for as long as PHPStan has been around. At this point, it feels like a must-have for any modern language. If PHP wants to keep moving in that direction, I think it needs generics.

---

## From Brent, developer advocate for PHP at JetBrains

Believe it or not, but I'm not super bothered that the latest RFC is failing. I would have liked it to pass, but generics won't make a difference in the big scheme of things. That "big scheme of things" is much more important, and if anything, the generics RFC put a spotlights on how PHP is failing in this regard.

Whether people want it or not, PHP is more than just an interpreter, it's more than its syntax. The reason PHP is where it is today is not because of how beautiful or not the language is; but because of the richness of its ecosystem. PHP is more than a programming language, and without its ecosystem of frameworks, packages, and tooling, I doubt it would still be around.

Meanwhile, there's a group of around 100 people deciding on the future of the language (technically there are around 2000 people eligible to vote, but most don't bother anymore, mind-blowing as that is). There's no leader or entity setting out a vision, and the group themselves is heavily divided; for example spending weeks debating whether a link to X should or shouldn't be removed from their website. 

Some say the lack of a unified vision and direction for PHP is what makes it great, but I say it's holding PHP back significantly. Which company that isn't already using PHP would choose a language whose design isn't owned by anyone? Where the only paid entity can be blocked of progress at any time when a small group of people decides against it? A group that has barely any representation from the biggest ecosystems that actually drive PHP like Laravel, Symfony, WordPress, or Packagist?  

To me, this is the failure highlighted by the generics RFC, and by so many RFCs besides it. Some people have tried to change the system in the past, to no avail. The committee seems fine where it is and doesn't want the process to change. 

I'm hopeful, though. PHP has gone through several phases in the past where it had equally little direction or vision. Then there were also phases where the language took leaps forward. I'm thinking about the very early Zend era; then the PHP 7.0 rewrite with Hack was breathing down PHP's neck; and then Nikita who pushed the language forward during the late 7.x and early 8.x years. Recently it feels like we've lost that direction once again, but I'm also hopeful that the right person or entity will come forward eventually.

If that means we can't have generics for the time being, then no worries. Awesome developers will continue to use PHP to build awesome stuff without them.

---

## And how about you?

Would you like to add your point of view here? Feel free to let me know via [email](brendt@stitcher.io) or [Discord](/discord).