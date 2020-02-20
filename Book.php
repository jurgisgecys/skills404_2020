<?php


class Book
{
    private $id;
    private $score;

    /**
     * @param $id
     * @param $score
     */
    public function __construct($id, $score)
    {
        $this->id = $id;
        $this->score = $score;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getScore()
    {
        return $this->score;
    }

    public function setScore($score)
    {
        $this->score = $score;
    }
}
