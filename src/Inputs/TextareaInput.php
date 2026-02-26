<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form\Inputs;

use Joby\HTML\Html5\Forms\TextareaTag;
use Joby\Smol\Form\Form;
use Joby\Smol\Form\Inputs\DisableableInput;
use Joby\Smol\Form\Inputs\InputInterface;

/**
 * @implements InputInterface<string>
 */
class TextareaInput extends TextareaTag implements InputInterface, DisableableInput
{

    /** @use InputTrait<string> */
    use InputTrait;
    use DisableableInputTrait;

    protected string|null $default = null;

    public function __construct(string $name, string $label)
    {
        parent::__construct();
        $this->form_name = $name;
        $this->form_label = $label;
        $this->classes()->add('smol-textarea');
    }

    /**
     * @inheritDoc
     */
    protected function formSubmittedValue(): mixed
    {
        return $this->parentOfType(Form::class)
                ?->getSubmittedValue($this->formName());
    }

    /**
     * @inheritDoc
     */
    protected function formSyncRenderSettings(): void
    {
        $this->setContent($this->formValue() ?? '');
        $this->setName($this->formName());
        $this->setID($this->formInputId());
        $this->setForm($this->parentOfType(Form::class)?->formId());
        $this->setDisabled($this->formDisabled());
        $this->setRequired($this->formValidator()->required());
    }

}
