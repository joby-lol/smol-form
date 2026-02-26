<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form;

use Joby\HTML\Html5\Forms\InputTag;
use Joby\HTML\Html5\Forms\InputTag\TypeValue;
use RuntimeException;

/**
 * Special input tag for setting a field named "_smol" with the name of the form being submitted, so that its parent form can know when it has been submitted.
 * 
 * @internal
 * 
 * @method string name()
 */
class SubmissionInput extends InputTag
{

    public function __construct(
    )
    {
        parent::__construct(TypeValue::hidden);
        $this->setName('_smol');
    }

    public function __toString(): string
    {
        $this->setName('_smol');
        $this->setValue($this->parentOfType(Form::class)?->formId());
        return parent::__toString();
    }

}
