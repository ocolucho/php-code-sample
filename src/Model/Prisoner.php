<?php

declare(strict_types=1);

namespace App\Model;

class Prisoner
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $cell;

    /**
     * @var string
     */
    private $block;

    /**
     * @param string $name
     * @param string $cell
     * @param string $block
     */
    public function __construct(string $name, string $cell, string $block)
    {
        $this->name = $name;
        $this->cell = $cell;
        $this->block = $block;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCell(): string
    {
        return $this->cell;
    }

    /**
     * @return string
     */
    public function getBlock(): string
    {
        return $this->block;
    }
}