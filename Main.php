<?php

require 'FileReader.php';
require 'FileWriter.php';

class Main
{
    private $libraries = [];

    private $signQueue = 0;
    private $signedLibraries = [];
    private $shippedBooks = [];

    private $days;
    private $currentDay;

    public function run($argv)
    {
        $inputFile = $argv[1];
        $outputFile = $argv[2];
        $reader = new FileReader();
        $data = $reader->readFile($inputFile);
        $this->days = $data['days'];
        $this->libraries = $data['data'];

        for ($this->currentDay = 0; $this->currentDay < $this->days && count($this->libraries) !== count($this->signedLibraries); $this->currentDay++) {
            echo 'Day: ' . $this->currentDay . "\n";
            if($this->signQueue <= $this->currentDay){
                $library = $this->getBestLibrary();
                $this->signQueue += $library->getSignupTime();
                $this->signedLibraries[] = $library;
                $library->setIsSigned(true);
            }

            foreach ($this->signedLibraries as $library){
                if($this->currentDay + $library->getSignupTime() < $this->days){
                    $removeDuplicateBooks = $this->removeDuplicateBooks($library->getBooksByScore());
                    $booksToShip = array_slice($removeDuplicateBooks, 0, $library->getBooksPerDay());

                    $library->addBooksToShip($booksToShip);
                    foreach ($booksToShip as $book){
                        $this->shippedBooks[$book->getId()] = $book->getId();
                    }
                }
            }

        }

        $writer = new FileWriter();
        $writer->write($outputFile, $this->signedLibraries);
    }

    public function getBestLibrary(): Library
    {
        $libs = $this->removeDuplicateLibraries($this->libraries);

        $that = $this;
        usort($libs, static function($a, $b) use ($that) {
            $adays = ($that->days - $a->getSignupTime() - $that->currentDay);
            $ascore = $that->getScoreByDays($adays * $a->getBooksPerDay(), $that->removeDuplicateBooks($a->getBooksByScore()));

            $bdays = ($that->days - $b->getSignupTime() - $that->currentDay);
            $bscore = $that->getScoreByDays($bdays * $b->getBooksPerDay(), $that->removeDuplicateBooks($b->getBooksByScore()));

            return $bscore <=> $ascore;
        });
        return $libs[0];
    }

    public function getScoreByDays($days, $books)
    {
        $slice = array_slice($books, 0, $days);

        return $days > 0 ? $this->sumScore($slice) / $days : 0;
    }

    public function getDaysToShip(Library $library)
    {
        $books = $library->getBooks();
        $withoutDuplicates = $this->removeDuplicateBooks($books);

        return ceil(count($withoutDuplicates) / $library->getBooksPerDay());
    }

    /**
     * @param array $books
     * @return array
     */
    protected function removeDuplicateLibraries(array $libaries): array
    {
        $withoutDuplicates = [];
        foreach ($libaries as $id => $libary) {
            if (!$libary->isSigned()) {
                $withoutDuplicates[$libary->getId()] = $libary;
            }
        }

        return $withoutDuplicates;
    }
    /**
     * @param array $books
     * @return array
     */
    protected function removeDuplicateBooks(array $books): array
    {
        $withoutDuplicates = [];
        foreach ($books as $id => $book) {
            if (!isset($this->shippedBooks[$book->getId()])) {
                $withoutDuplicates[$book->getId()] = $book;
            }
        }

        return $withoutDuplicates;
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
