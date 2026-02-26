<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form\Inputs;

use DateTimeImmutable;
use DateTimeInterface;
use Joby\HTML\Helpers\BooleanAttribute;
use Joby\HTML\Html5\Forms\InputTag;
use Joby\HTML\Html5\Forms\InputTag\TypeValue;
use Joby\Smol\Form\Form;

/**
 * @implements InputInterface<DateTimeImmutable>
 */
class DateInput extends InputTag implements InputInterface, DisableableInput, SelfValidatingInput
{

    /** @use InputTrait<DateTimeImmutable> */
    use InputTrait;
    use DisableableInputTrait;

    protected DateTimeInterface|null $date_min = null;

    protected DateTimeInterface|null $date_max = null;

    public function __construct(string $name, string $label)
    {
        parent::__construct(TypeValue::date);
        $this->form_name = $name;
        $this->form_label = $label;
        $this->classes()->add('smol-date');
    }

    /**
     * @inheritDoc
     */
    protected function formSubmittedValue(): mixed
    {
        $value = $this->parentOfType(Form::class)
                ?->getSubmittedValue($this->formName());
        if ($value !== null && $value !== '') {
            $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $value);
            return $parsed !== false ? $parsed->setTime(0, 0, 0) : null;
        }
        return null;
    }

    /**
     * Set the minimum date accepted by this input.
     */
    public function setDateMin(DateTimeInterface|null $min): static
    {
        $this->date_min = $min;
        return $this;
    }

    /**
     * Set the maximum date accepted by this input.
     */
    public function setDateMax(DateTimeInterface|null $max): static
    {
        $this->date_max = $max;
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
            $this->setValue($this->formValue()->format('Y-m-d'));
        else
            $this->setValue(null);
        if ($this->formDisabled())
            $this->attributes()['disabled'] = BooleanAttribute::true;
        else
            unset($this->attributes()['disabled']);
        // minimum attribute
        if ($this->date_min !== null)
            $this->attributes()['min'] = $this->date_min->format('Y-m-d');
        else
            unset($this->attributes()['min']);
        // maximum attribute
        if ($this->date_max !== null)
            $this->attributes()['max'] = $this->date_max->format('Y-m-d');
        else
            unset($this->attributes()['max']);
    }

    /**
     * @inheritDoc
     */
    public function validateSelf(): string|null
    {
        $errors = [];
        $value = $this->formValue();
        // check minimum
        if ($this->date_min !== null && $value < $this->date_min)
            $errors[] = sprintf("must not be before %s", $this->date_min->format('Y-m-d'));
        // check maximum
        if ($this->date_max !== null && $value > $this->date_max)
            $errors[] = sprintf("must not be after %s", $this->date_max->format('Y-m-d'));
        // return result
        if ($errors)
            return "Invalid date: " . implode(', ', $errors);
        else
            return null;
    }

}
