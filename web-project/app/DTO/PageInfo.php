<?php

declare(strict_types=1);

namespace App\DTO;

class PageInfo
{
    final public function __construct(
        public string $url,
        public string $title,
        public string $description,
    ) {}

    /**
     * @param array{url?: string, title?: string, description?: string}|mixed[] $array
     * @return PageInfo
     * @throws \InvalidArgumentException
     */
    public static function fromArray(array $array): PageInfo
    {
        if (array_key_exists('url', $array) && array_key_exists('title', $array) && array_key_exists('description', $array)) {
            return new static($array['url'], $array['title'], $array['description']);
        }
        throw new \InvalidArgumentException('Invalid array');
    }
}
