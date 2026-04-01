<?php

namespace App\Logic;

use App\DAO\SearchResults;

interface ProcessSerp
{
    public function setQuery(string $query): void;
    public function process(): SearchResults;
}