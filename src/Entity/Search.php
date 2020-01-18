<?php


namespace App\Entity;

class Search
{


    /**
     * @var string | null
     */
    private $nameSearch;

    /**
     * @return string|null
     */
    public function getNameSearch(): ?string
    {
        return $this->nameSearch;
    }

    /**
     * @param string|null $nameSearch
     * @return Search
     */
    public function setNameSearch(?string $nameSearch): Search
    {
        $this->nameSearch = $nameSearch;
        return $this;
    }
}
