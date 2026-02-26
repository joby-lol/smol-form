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
 * @implements InputInterface<float>
 */
class NumberInput extends InputTag implements InputInterface, DisableableInput, SelfValidatingInput
{

    /** @use InputTrait<float> */
    use InputTrait;
    use DisableableInputTrait;

    protected float|null $number_min = null;

    protected float|null $number_max = null;

    protected float|null $number_step = null;

    public function __construct(string $name, string $label)
    {
        parent::__construct(TypeValue::number);
        $this->form_name = $name;
        $this->form_label = $label;
        $this->classes()->add('smol-text');
    }

    /**
     * @inheritDoc
     */
    protected function formSubmittedValue(): mixed
    {
        $value = $this->parentOfType(Form::class)
                ?->getSubmittedValue($this->formName());
        if ($value === '')
            return null;
        if ($value !== null)
            return (float) $value;
        else
            return null;
    }

    /**
     * Set the step size that values must increment off default value in. If there is no step size values must be a multiple of this number.
     */
    public function setNumberStep(float|null $step): static
    {
        $this->number_step = $step;
        return $this;
    }

    /**
     * Set the minimum value accepted by this input.
     */
    public function setNumberMin(float|null $min): static
    {
        $this->number_min = $min;
        return $this;
    }

    /**
     * Set the maximum value accepted by this input.
     */
    public function setNumberMax(float|null $max): static
    {
        $this->number_max = $max;
        return $this;
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
        // basic form field stuff
        $this->setID($this->formAnchorId());
        $this->setRequired($this->formValidator()->required());
        $this->setName($this->formName());
        $this->setForm($this->parentOfType(Form::class)?->formId());
        if ($this->formValue() !== null)
            $this->setValue((string) $this->formValue());
        else
            $this->setValue(null);
        if ($this->formDisabled())
            $this->attributes()['disabled'] = BooleanAttribute::true;
        else
            unset($this->attributes()['disable']);
        // minimum attribute
        if ($this->number_min !== null)
            $this->attributes()['min'] = $this->number_min;
        else
            unset($this->attributes()['min']);
        // maximum attribute
        if ($this->number_max !== null)
            $this->attributes()['max'] = $this->number_max;
        else
            unset($this->attributes()['max']);
        // step attribute
        if ($this->number_step !== null)
            $this->attributes()['step'] = $this->number_step;
        else
            unset($this->attributes()['step']);
    }

    /**
     * @inheritDoc
     */
    public function validateSelf(): string|null
    {
        $errors = [];
        // check minimum
        if ($this->number_min !== null && $this->formValue() < $this->number_min)
            $errors[] = sprintf("must not be less than %g", $this->number_min);
        // check maximum
        if ($this->number_max !== null && $this->formValue() > $this->number_max)
            $errors[] = sprintf("must not be higher than %g", $this->number_max);
        // check step distance from default
        if ($this->number_step !== null) {
            $step_base = $this->formDefault() ?? 0;
            $remainder = fmod($this->formValue() - $step_base, $this->number_step);
            if (abs($remainder) > PHP_FLOAT_EPSILON) {
                $step_base_min = abs(fmod($this->formDefault() ?? 0, $this->number_step));
                if ($step_base_min < PHP_FLOAT_EPSILON)
                    $errors[] = sprintf("must be a multiple of %g", $this->number_step);
                else
                    $errors[] = sprintf("must be a multiple of %g away from %g", $this->number_step, $step_base_min);
            }
        }
        // return result
        if ($errors)
            return "Invalid number: " . implode(', ', $errors);
        else
            return null;
    }

}
