```php src/Books/BookController.php
final readonly class BookController
{
    #[Get('/books')]
    public function index(): View
    {
        return view('./index.view.php');
    }
}
```
