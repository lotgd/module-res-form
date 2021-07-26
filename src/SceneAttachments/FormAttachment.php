<?php
declare(strict_types=1);

namespace LotGD\Module\Res\Form\SceneAttachments;

use LotGD\Core\Action;
use LotGD\Core\Attachment;
use Symfony\Component\Form\FormInterface;

class FormAttachment extends Attachment
{
    protected ?FormInterface $form = null;
    protected ?Action $action = null;

    public function setAction(Action $action)
    {
        $this->action = $action;
    }

    public function hasAction(): bool
    {
        return $this->action !== null;
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }

    public function hasForm(): bool
    {
        return $this->form !== null;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function getData(): array
    {
        return [
            "form" => $this->form,
        ];
    }
}