<?php

namespace App\Entity;

use Symfony\Component\Config\Definition\Exception\Exception;

class Product
{
    const FOOD_PRODUCT = 'food';

    public function __construct(private string $name, private string $type, private float $price)
    {}

    public function computeTVA(): float | Exception
    {
        if ($this->price < 0) {
            throw new Exception('The TVA cannot be negative.');
        }

        if (self::FOOD_PRODUCT == $this->type) {
            return $this->price * 0.055;
        }

        return $this->price * 0.196;
    }
}