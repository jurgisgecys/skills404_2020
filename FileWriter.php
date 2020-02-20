<?php


class FileWriter
{
    /**
     * @param string    $file
     * @param Library[] $data
     */
    public function write(string $file, array $data)
    {
        $handle = fopen($file, 'wb+');
        $libCount = count($data);
        fwrite($handle, "$libCount\n");
        foreach($data as $library){
            $id = $library->getId();
            $booksToShip = $library->getBooksToShip();
            $count = count($booksToShip);
            fwrite($handle, "$id $count\n");
            fwrite($handle, implode(' ', array_map(static function($book){return $book->getId();},$booksToShip))."\n");
        }
        fclose($handle);
    }
}
