<?php

require 'FileReader.php';
require 'FileWriter.php';

class Main
{
    private $libraries = [];
    private $unusedlibraries = [];

    private $signQueue = 0;
    private $signedLibraries = [];
    private $shippedBooks = [];

    private $days;
    private $currentDay;

    private $scoreSame;

    private $maxWait = 0;

    public function run($argv)
    {
        $inputFile = $argv[1];
        $outputFile = $argv[2];
        $reader = new FileReader();
        $data = $reader->readFile($inputFile);
        $this->scoreSame = $data['scoreSame'];
        $this->days = $data['days'];
        $this->libraries = $data['data'];
        $this->unusedlibraries = $data['data'];

        foreach ($this->libraries as $lib){
            if($this->maxWait < $lib->getSignupTime()){
                $this->maxWait = $lib->getSignupTime();
            }
        }

        for ($this->currentDay = 0; $this->currentDay < $this->days; $this->currentDay++) {
            if($this->currentDay % 100 == 0){
            echo 'Day: ' . $this->currentDay . "\n";
            }
            if($this->signQueue <= $this->currentDay){
                $library = $this->getBestLibrary();
                if($library){
                    $this->signQueue += $library->getSignupTime();
                    $this->signedLibraries[] = $library;
                    $library->setIsSigned(true);
                    unset($this->unusedlibraries[$library->getId()]);



                    $removeDuplicateBooks = $this->removeDuplicateBooks(array_slice($library->getBooksByScore(), 0, ($this->days - $this->currentDay - $library->getSignupTime()) * $library->getBooksPerDay()));

                    $library->addBooksToShip($removeDuplicateBooks);
                    foreach ($removeDuplicateBooks as $book){
                        $bookid = $book->getId();
                        $this->shippedBooks[$bookid] = $bookid;
                    }
                }
            }

        }

        $writer = new FileWriter();
        $writer->write($outputFile, $this->signedLibraries);
    }

    public function getBestLibrary(): ?Library
    {
        $libs = $this->unusedlibraries;

        $score = 0;
        $lib = null;

        foreach ($libs as $a) {
            $adays = ($this->days - $a->getSignupTime() - $this->currentDay);
            if($this->scoreSame){
                $ascore = $adays * $a->getBooksPerDay() * count($this->removeDuplicateBooks($a->getBooksByScore()));
            } else {
                $ascore = $this->getScoreByDays($adays * $a->getBooksPerDay(), $this->removeDuplicateBooks($a->getBooksByScore())) / $a->getSignupTime() * count($this->removeDuplicateBooks($a->getBooksByScore()));
//                $ascore = 1 / $a->getSignupTime();
            }
            if($ascore > $score){
                $score = $ascore;
                $lib = $a;
            }
        }
        return $lib;
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
