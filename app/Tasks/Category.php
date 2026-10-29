<?php

declare(strict_types=1);

namespace App\Tasks;

final class Category
{
    /** @var array<int, Book> */
    private array $books = [];

    public function __construct(public readonly int $categoryId, public readonly string $name) {}

    public function addBook(Book $book): void
    {
        $this->books[$book->id] = $book;
    }

    public function removeBook(int $id): void
    {
        unset($this->books[$id]);
    }

    /** @return Book[] */
    public function searchBooks(string $title): array
    {
        return array_values(array_filter($this->books, static fn (Book $book): bool => str_contains(strtolower($book->title), strtolower($title))));
    }
}
