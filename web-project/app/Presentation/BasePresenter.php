<?php

declare(strict_types=1);

namespace App\Presentation;

use App\Libs\Translator;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;
use Psr\Log\LoggerInterface;
use Tracy\Bridges\Psr\TracyToPsrLoggerAdapter;

abstract class BasePresenter extends Presenter
{
    #[Inject]
    public Translator $Translator;

    #[Inject]
    public LoggerInterface $Logger;

    /**
     * @param array{exception?: \Throwable} $context
     */
    protected function log(mixed $message, ?string $level = null, array $context = []): void
    {
        if (empty($context['exception']) && $message instanceof \Throwable) {
            $context['exception'] = $message;
            $message = $message->getMessage();
        }
        if (!$this->Logger instanceof TracyToPsrLoggerAdapter && !is_string($message)) { //if Logger is accepting strings only
            $message = var_export($message, true);
        }
        $this->Logger->log($level, $message, $context); /** @phpstan-ignore argument.type (message is string or logger is Tracy) */
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->getTemplate()->setTranslator($this->Translator); /** @phpstan-ignore method.notFound (this is a template, not a stdClass) */
    }

}
