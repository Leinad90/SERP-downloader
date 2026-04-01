<?php

namespace App\Logic;

use App\DAO\SearchResults;

interface ProcessSerp
{
    public function process(): SearchResults;
}