<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form;

use Joby\HTML\Html5\Forms\ButtonTag;
use Joby\HTML\Html5\Forms\ButtonTag\TypeValue;

class SubmitButton extends ButtonTag
{

    public function __construct()
    {
        parent::__construct();
        $this->setType(TypeValue::submit);
        $this->classes()->add('smol-form__submit-button');
    }

    public function __toString(): string
    {
        $this->setForm($this->parentOfType(Form::class)?->formId());
        return parent::__toString();
    }

}
