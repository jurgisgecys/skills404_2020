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
        $data = array_filter($data, static function($row){return count($row->getBooksToShip()) > 0;});
        $libCount = count($data);
        fwrite($handle, "$libCount\n");
        foreach($data as $library){
            $id = $library->getId();
            $booksToShip = $library->getBooksToShip();
            $count = count($booksToShip);
            if($count > 0){
                fwrite($handle, "$id $count\n");
                fwrite($handle, implode(' ', array_map(static function($book){return $book->getId();},$booksToShip))."\n");
            }
        }
        fclose($handle);
    }
}
