<?php

declare(strict_types=1);

namespace App\Presentation;

use App\Libs\Translator;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;
use Psr\Log\LoggerInterface;

abstract class BasePresenter extends Presenter
{

    #[Inject]
    public Translator $Translator;

    #[Inject]
    public LoggerInterface $Logger;

    protected function log(mixed $message, ?string $level=null): void
    {
        $this->Logger->log($level, $message);
    }

    public function beforeRender(): void {
        parent::beforeRender();
        $this->template->setTranslator($this->Translator);
    }

}
