<?php

require 'Book.php';

class Library
{
    /**
     * @var int
     */
    private $id;

    /** @var int */
    private $bookCount;

    /** @var int */
    private $signupTime;

    /** @var int */
    private $booksPerDay;

    /** @var Book[] */
    private $books;

    /** @var Book[] */
    private $booksByScore;

    private $totalScore;

    private $avgScorePerDay;

    private $booksToShip = [];

    private $isSigned = false;

    /**
     * @param        $id
     * @param        $bookCount
     * @param        $signupTime
     * @param        $booksPerDay
     * @param Book[] $books
     */
    public function __construct($id, $bookCount, $signupTime, $booksPerDay, $books)
    {
        $this->id = $id;
        $this->bookCount = $bookCount;
        $this->signupTime = $signupTime;
        $this->booksPerDay = $booksPerDay;
        $this->books = $books;

        $this->booksByScore = $books;
        usort(
            $this->booksByScore, static function ($a, $b) {
            return $b->getScore() <=> $a->getScore();
        }
        );
        $this->totalScore = $this->sumScore($this->books);
        $this->avgScorePerDay = $this->totalScore / ($this->bookCount / $this->booksPerDay);
    }

    public function isSigned(): bool
    {
        return $this->isSigned;
    }

    public function setIsSigned(bool $isSigned)
    {
        $this->isSigned = $isSigned;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getBookCount(): int
    {
        return $this->bookCount;
    }

    public function setBookCount(int $bookCount)
    {
        $this->bookCount = $bookCount;
    }

    public function getSignupTime(): int
    {
        return $this->signupTime;
    }

    public function setSignupTime(int $signupTime)
    {
        $this->signupTime = $signupTime;
    }

    public function getBooksPerDay(): int
    {
        return $this->booksPerDay;
    }

    public function setBooksPerDay(int $booksPerDay)
    {
        $this->booksPerDay = $booksPerDay;
    }

    public function getBooks(): array
    {
        return $this->books;
    }

    public function setBooks(array $books)
    {
        $this->books = $books;
    }

    public function getBooksByScore(): array
    {
        return $this->booksByScore;
    }

    public function setBooksByScore(array $booksByScore)
    {
        $this->booksByScore = $booksByScore;
    }

    public function getTotalScore()
    {
        return $this->totalScore;
    }

    public function setTotalScore($totalScore)
    {
        $this->totalScore = $totalScore;
    }

    public function getBooksToShip(): array
    {
        return $this->booksToShip;
    }

    public function addBooksToShip(array $booksToShip)
    {
        $this->booksToShip = array_merge($this->booksToShip, $booksToShip);
    }

    public function getAvgScorePerDay()
    {
        return $this->avgScorePerDay;
    }

    public function getDuplicateScore(array $scannedBooksIds)
    {
        $duplicates = array_intersect(array_keys($this->books), $scannedBooksIds);
        $score = 0;
        foreach ($duplicates as $item) {
            $score += $this->books[$item]->getScore();
        }

        return $score;
    }

    /**
     * @param array $books
     * @return int
     */
    protected function sumScore(array $books)
    {
        return array_reduce(
            $books, static function ($score, $book) {
            if (!$book) {
                return $score;
            }

            return $score + $book->getScore();
        }, 0
        );
    }
}
