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
 * Checkbox helper, designed to render an input tag of type checkbox in a way that ties back to the Form object, naming-wise, but is not actually an InputInterface so that fancy InputInterface implementations can use it without polluting the Form's list of Inputs.
 */
class HelperCheckbox extends InputTag
{

    protected bool $default = false;

    public function __construct(string $name, bool $default = false)
    {
        parent::__construct(TypeValue::checkbox);
        $this->setName($name);
        $this->setDefault($default);
    }

    public function name(): string|Stringable
    {
        return parent::name()
            ?? throw new RuntimeException("HelperCheckbox objects must have a name");
    }

    public function submittedValue(): bool|null
    {
        /** @var Form|null $form */
        $form = $this->parentOfType(Form::class);
        if (!$form)
            return null;
        if (!$form->isFormAttempted())
            return null;
        return $form->getSubmittedValue($this->name())
            == 'on';

    }

    public function setDefault(bool $default): static
    {
        $this->default = $default;
        return $this;
    }

    public function default(): bool
    {
        return $this->default;
    }

    public function __toString(): string
    {
        $this->attributes()['form'] = $this->parentOfType(Form::class)?->formId()
            ?? BooleanAttribute::false;
        if ($this->submittedValue() ?? $this->default)
            $this->attributes()['checked'] = BooleanAttribute::true;
        else
            unset($this->attributes()['checked']);
        return parent::__toString();
    }

}
