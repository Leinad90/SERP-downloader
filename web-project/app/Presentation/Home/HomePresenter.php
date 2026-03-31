<?php declare(strict_types=1);

namespace App\Presentation\Home;

use App\Logic\ProcessSerp;
use Nette;


final class HomePresenter extends Nette\Application\UI\Presenter
{

    public function __construct(
        private readonly ProcessSerp $processSerp,
        private readonly string $fileName
    )
    {
        parent::__construct();
    }
    public function createComponentForm(): Nette\Forms\Form
    {
        $form = new Nette\Application\UI\Form();
        $form->setMethod('GET');
        $form->addText('q','search query');
        $form->addSubmit('send','search');
        $form->onSuccess[] = [$this, 'formSucceeded'];
        return $form;
    }

    public function formSucceeded(Nette\Forms\Form $form, \stdClass $values): never
    {
        $this->processSerp->query = $values->q;
        $result = $this->processSerp->process();
        $response= $this->getHttpResponse();
        $fileName = str_replace(['@query@','@date@','@time@'], [$values->q,date('Y-m-d'),date('H:i:s')], $this->fileName);
        $fileName = Nette\Utils\Strings::webalize($fileName);
        $response->sendAsFile($fileName);
        $this->sendJson($result);
    }
}
