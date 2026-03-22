<?php

namespace App\Logic;

use App\DAO\SearchResults;

interface ProcessSerp
{
    public string $query {
        set;
    }

    public function process(): SearchResults;
}