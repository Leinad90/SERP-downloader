<?php

declare(strict_types=1);

namespace App\Logic;

use App\DTO\SearchResults;
use App\Exception\DownloadException;
use App\Exception\ProcessSerpException;

interface ProcessSerp
{
    public function setQuery(string $query): void;

    /**
     * @throws ProcessSerpException
     * @throws DownloadException
     */
    public function process(): SearchResults;
}
