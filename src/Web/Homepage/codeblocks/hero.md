```php src/Books/BookController.php
final readonly class BookController
{
    #[Get('/books/{book}')]
    public function show(Book $book): View
    {
        return view('book-show.view.php', book: $book);
    }

    #[Post('/books')]
    public function create(CreateBookRequest $request): Response
    {
        $book = map($request)->to(Book::class)->save();

        return new Redirect(
            uri([self::class, 'show'], book: $book),
        );
    }
}
```
