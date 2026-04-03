<?php

declare(strict_types=1);

namespace App\Presentation\Home;

use App\Exception\DownloadException;
use App\Exception\ProcessSerpException;
use App\Logic\ProcessSerp;
use App\Presentation\BasePresenter;
use Nette;
use Tracy\ILogger;
use Nette\Http\IResponse;

final class HomePresenter extends BasePresenter
{
    public function __construct(
        private readonly ProcessSerp $processSerp,
        private readonly string $fileName
    ) {
        parent::__construct();
    }
    public function createComponentForm(): Nette\Forms\Form
    {
        $form = new Nette\Application\UI\Form();
        $form->setTranslator($this->Translator);
        $form->setMethod('GET');
        $form->addText('q', 'search query');
        $form->addSubmit('send', 'search');
        $form->onSuccess[] = [$this, 'formSucceeded']; /** @phpstan-ignore assign.propertyType (nette magic) */
        return $form;
    }

    public function formSucceeded(Nette\Forms\Form $form, Nette\Utils\ArrayHash $values): never
    {
        $this->processSerp->setQuery($values->q);
        $response = $this->getHttpResponse();
        try {
            $result = $this->processSerp->process();
        } catch (ProcessSerpException $e) {
            $this->log($e, ILogger::EXCEPTION);
            $response->setCode(IResponse::S500_InternalServerError);
            $this->sendJson(['error' => 'An error occurred while processing the search results.']);
        } catch (DownloadException $e) {
            $this->log($e, ILogger::WARNING);
            $response->setCode(IResponse::S502_BadGateway);
            $this->sendJson(['error' => 'An error occurred while downloading the search results, please try again later.']);
        }
        $fileName = str_replace(['@query@','@date@','@time@'], [$values->q,date('Y-m-d'),date('H:i:s')], $this->fileName);
        $fileName = Nette\Utils\Strings::webalize($fileName);
        if (method_exists($response, 'sendAsFile')) {
            $response->sendAsFile($fileName);
        }
        $this->sendJson($result);
    }
}
