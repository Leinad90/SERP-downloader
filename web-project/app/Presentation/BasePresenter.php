<?php

declare(strict_types=1);

namespace App\Presentation;

use App\Libs\Translator;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;
use Tracy\Debugger;

abstract class BasePresenter extends Presenter
{

    #[Inject]
    public Translator $Translator;
    protected function log(mixed $message, string $level): void
    {
        Debugger::log($message, $level);
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->getTemplate()->setTranslator($this->Translator);
    }
}
