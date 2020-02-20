<?php

require 'Library.php';

class FileReader
{
    /**
     * @return Library[]
     */
    public function readFile($file): array
    {
        $handle = fopen($file, 'rb');
        if(!$handle){
            throw new RuntimeException('Failed to read file');
        }
        $lengths = fgetcsv($handle, 0, ' ');
        $scores = fgetcsv($handle, 0, ' ');
        $data = [];
        for($i = 0; $i < $lengths[1]; $i++) {
            $first = fgetcsv($handle, 0, ' ');
            $second = fgetcsv($handle, 0, ' ');
            $second = array_combine($second, $second);
            $books = array_map(static function($bookId) use ($scores) {return new Book($bookId, $scores[$bookId]);}, $second);
            $data[] = new Library(
                $i,
                $first[0],
                $first[1],
                $first[2],
                $books
            );
        }
        fclose($handle);
        return [
            'bookCount' => $lengths[0],
            'libraryCount' => $lengths[1],
            'days' => $lengths[2],
            'data' => $data
        ];
    }
}
