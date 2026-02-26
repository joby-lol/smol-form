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
 * @implements InputInterface<string>
 */
class PasswordInput extends InputTag implements InputInterface, DisableableInput
{

    /** @use InputTrait<string> */
    use InputTrait;
    use DisableableInputTrait;

    protected string|null $default = null;

    public function __construct(string $name, string $label)
    {
        parent::__construct(TypeValue::password);
        $this->form_name = $name;
        $this->form_label = $label;
        $this->classes()->add('smol-password');
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
        $this->setID($this->formAnchorId());
        $this->setRequired($this->formValidator()->required());
        $this->setName($this->formName());
        $this->setForm($this->parentOfType(Form::class)?->formId());
        if ($this->formDisabled())
            $this->attributes()['disabled'] = BooleanAttribute::true;
        else
            unset($this->attributes()['disable']);
    }

}
