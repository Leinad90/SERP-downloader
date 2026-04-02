<?php

declare(strict_types=1);

namespace App\Presentation;

use Nette\Application\UI\Presenter;
use Tracy\Debugger;

abstract class BasePresenter extends Presenter
{

    protected function log(mixed $message, string $level): void
    {
        Debugger::log($message, $level);
    }
}