```php src/Books/FetchBookCommand.php
final readonly class FetchBookCommand
{
    public function __construct(
        private BookRepository $repository,
        private Isbn $isbn,
        private Console $console,
    ) {}
    
    #[ConsoleCommand(description: 'Sync a book by title')]
    public function __invoke(string $title, bool $force = false): void
    {
        $data = $this->isbn->findByTitle($title);

        if (! $data) {
            $this->console->error("No book found matching that title.");
            return;
        }

        $book = map($data)->to(Book::class);

        if ($this->repository->exists($book->isbn13) && ! $force) {
            $this->console->info("Book already exists.");
            return;
        }

        $this->repository->save($book);
        $this->console->success("Synchronized {$book->title}.");
    }
}
```
