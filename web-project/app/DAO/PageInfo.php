<?php

namespace App\DAO;

class PageInfo
{
    public function __construct(
        public string $url,
        public string $title,
        public string $description,
    ) {

    }

    public static function fromArray(array $array): PageInfo
    {
        if (array_key_exists('url', $array) && array_key_exists('title', $array) && array_key_exists('description', $array)) {
            return new static($array['url'], $array['title'], $array['description']);
        }
        throw new \InvalidArgumentException('Invalid array');
    }
}