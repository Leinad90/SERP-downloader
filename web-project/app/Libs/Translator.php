<?php

namespace App\Libs;

use Nette\HtmlStringable;

class Translator implements \Nette\Localization\Translator
{

    /**
     * @inheritDoc
     */
    public function translate(\Stringable|string $message, ...$parameters): string|\Stringable
    {
        return $message;
    }
}