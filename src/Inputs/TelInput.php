<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form\Inputs;

use Joby\HTML\Helpers\BooleanAttribute;
use Joby\HTML\Html5\Forms\InputTag;
use Joby\HTML\Html5\Forms\InputTag\TypeValue;
use Joby\Smol\Form\Form;

/**
 * Note that there is no built-in validation rule on TelInput — phone number formats vary too wildly internationally for FILTER_VALIDATE_* to be useful, and the browser's type="tel" doesn't enforce any format either. If you need format validation you have to add a custom rule.
 * 
 * @implements InputInterface<string>
 */
class TelInput extends InputTag implements InputInterface, DisableableInput
{

    /** @use InputTrait<string> */
    use InputTrait;
    use DisableableInputTrait;

    public function __construct(string $name, string $label)
    {
        parent::__construct(TypeValue::tel);
        $this->form_name = $name;
        $this->form_label = $label;
        $this->classes()->add('smol-tel');
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
     * Set this input's placeholder text. Note: it is bad practice to use this as a label.
     */
    public function setPlaceholder(string|null $placeholder): static
    {
        if ($placeholder)
            $this->attributes()['placeholder'] = $placeholder;
        else
            unset($this->attributes()['placeholder']);
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function formSyncRenderSettings(): void
    {
        $this->setID($this->formAnchorId());
        $this->setRequired($this->formValidator()->required());
        $this->setName($this->formName());
        $this->setForm($this->parentOfType(Form::class)?->formId());
        $this->setValue($this->formValue());
        if ($this->formDisabled())
            $this->attributes()['disabled'] = BooleanAttribute::true;
        else
            unset($this->attributes()['disable']);
    }

}
