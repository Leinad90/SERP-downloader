<?php

declare(strict_types=1);

namespace App\Logic;

use App\DTO\SearchResults;

interface ProcessSerp
{
    public function setQuery(string $query): void;
    public function process(): SearchResults;
}
