<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form\Inputs\Helpers;

use Joby\HTML\Helpers\BooleanAttribute;
use Joby\HTML\Html5\Forms\InputTag;
use Joby\HTML\Html5\Forms\InputTag\TypeValue;
use Joby\Smol\Form\Form;
use RuntimeException;
use Stringable;

/**
 * Radio button helper, designed to render an input tag of type radio in a way that ties back to the Form object, naming-wise, but is not actually an InputInterface so that fancy InputInterface implementations can use it without polluting the Form's list of Inputs.
 */
class HelperRadio extends InputTag
{

    public function __construct(string $name, string $value)
    {
        parent::__construct(TypeValue::radio);
        $this->setName($name);
        $this->setValue($value);
    }

    public function name(): string|Stringable
    {
        return parent::name()
            ?? throw new RuntimeException("HelperRadio objects must have a name");
    }

    public function value(): string|Stringable
    {
        return parent::value()
            ?? throw new RuntimeException("HelperRadio objects must have a value");
    }

    public function __toString(): string
    {
        /** @var Form|null $form */
        $form = $this->parentOfType(Form::class);
        $this->attributes()['form'] = $form?->formId()
            ?? BooleanAttribute::false;
        return parent::__toString();
    }

}
