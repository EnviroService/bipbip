<?php

namespace App\Entity;

class Search
{
    /**
     * @var string
     */
    private $nameSearch;

    /**
     * @return string
     */
    public function getNameSearch(): string
    {
        return $this->nameSearch;
    }

    /**
     * @param string $nameSearch
     * @return Search
     */
    public function setNameSearch(string $nameSearch): Search
    {
        $this->nameSearch = $nameSearch;
        return $this;
    }
}
