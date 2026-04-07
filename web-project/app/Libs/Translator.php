<?php

namespace App\Libs;

use Nette\HtmlStringable;

class Translator implements \Nette\Localization\Translator
{

    /**
     * @inheritDoc
     */
    public function translate(\Stringable|string $message, mixed ...$parameters): string|\Stringable
    {
        return $message;
    }
}