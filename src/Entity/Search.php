<?php
namespace App\Entity;

class Search
{
    /**
     * @var string
     */
    private $name;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Search
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}
