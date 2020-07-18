<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('shuffle', [$this, 'shuffleArray']),
            new TwigFilter('trimLong', [$this, 'trimLongText']),
        ];
    }

    public function shuffleArray($array)
    {
        shuffle($array);
        return $array;
    }

    public function trimLongText($longText, $max = 20)
    {
        return strlen($longText) > $max ? substr($longText, 0, $max) . '...' : $longText;
    }
}
