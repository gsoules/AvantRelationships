<?php

class RelatedItemsAncestry
{
    protected $ancestorsName;
    protected $descendantsName;
    protected $level;
    protected $parts;
    protected $siblingsName;

    public function __construct($ancestryData)
    {
        $parts = json_decode($ancestryData, true);
        $this->siblingsName = isset($parts['siblings']) ? $parts['siblings'] : 'INVALID SIBLINGS SYNTAX, INVALID SIBLING SYNTAX';
        $this->ancestorsName = isset($parts['ancestors']) ? $parts['ancestors'] : '';
        $this->descendantsName = isset($parts['descendants']) ? $parts['descendants'] : '';

        if (!is_array($this->ancestorsName))
            $this->ancestorsName = array('INVALID ANCESTORS SYNTAX,INVALID ANCESTOR SYNTAX');

        if (!is_array($this->descendantsName))
            $this->descendantsName = array('INVALID DESCENDANTS SYNTAX,INVALID DESCENDANT SYNTAX');
    }

    public function getAncestorsName($level)
    {
        if ($level < 1)
            return ("ANCESTORS");

        $index = min($level, count($this->ancestorsName));
        $name = $this->ancestorsName[$index - 1];

        if (strpos($name, '*') !== false)
        {
            $descendantsName = $this->getAncestorsName($level - 1);
            $name = $this->getGrandName($level, $descendantsName, $name);
        }
        return $name;
    }

    public function getDescendantsNames($level)
    {
        if ($level < 1)
            return ("DESCENDANTS");

        $index = min($level, count($this->descendantsName));
        $name = $this->descendantsName[$index - 1];

        if (strpos($name, '*') !== false)
        {
            $ancestorName = $this->getDescendantsNames($level - 1);
            $name = $this->getGrandName($level, $ancestorName, $name);
        }
        return $name;
    }

    protected function getGrandName($level, $name, $wildcard)
    {
        $names = explode(',', $name);
        $namePlural = $names[0];
        $nameSingular = $names[1];
        $grandNamePlural = str_replace('*', $namePlural, $wildcard);
        $grandNameSingular = str_replace('*', $nameSingular, $wildcard);
        $grandName = "$grandNamePlural,$grandNameSingular";
        return $grandName;
    }

    public function getLevel()
    {
        return $this->level;
    }

    protected function getPart($index, $default)
    {
        return isset($this->parts[$index]) ? trim($this->parts[$index]) : $default;
    }

    public function getSiblingsName()
    {
        return $this->siblingsName;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }
}