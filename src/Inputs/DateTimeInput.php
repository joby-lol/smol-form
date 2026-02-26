<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form\Inputs;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Joby\HTML\Helpers\BooleanAttribute;
use Joby\HTML\Html5\Forms\InputTag;
use Joby\HTML\Html5\Forms\InputTag\TypeValue;
use Joby\Smol\Form\Form;

/**
 * @implements InputInterface<DateTimeImmutable>
 */
class DateTimeInput extends InputTag implements InputInterface, DisableableInput, SelfValidatingInput
{

    /** @use InputTrait<DateTimeImmutable> */
    use InputTrait;
    use DisableableInputTrait;

    protected DateTimeInterface|null $datetime_min = null;

    protected DateTimeInterface|null $datetime_max = null;

    protected int|null $datetime_step = null;

    public function __construct(string $name, string $label)
    {
        parent::__construct(TypeValue::datetimeLocal);
        $this->form_name = $name;
        $this->form_label = $label;
        $this->classes()->add('smol-datetime');
    }

    /**
     * @inheritDoc
     */
    protected function formSubmittedValue(): mixed
    {
        $value = $this->parentOfType(Form::class)
                ?->getSubmittedValue($this->formName());
        if ($value !== null && $value !== '') {
            $parsed = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value)
                ?: DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', $value);
            return $parsed !== false ? $parsed : null;
        }
        return null;
    }

    /**
     * Set the minimum datetime accepted by this input.
     */
    public function setDateTimeMin(DateTimeInterface|null $min): static
    {
        $this->datetime_min = $min;
        return $this;
    }

    /**
     * Set the maximum datetime accepted by this input.
     */
    public function setDateTimeMax(DateTimeInterface|null $max): static
    {
        $this->datetime_max = $max;
        return $this;
    }

    /**
     * Set the step size in seconds, or as a DateInterval.
     *
     * Step validation uses the field's default value as the base. If no default
     * is set, step validation is skipped entirely — there is no implicit base.
     */
    public function setDateTimeStep(int|DateInterval|null $step): static
    {
        if ($step instanceof DateInterval) {
            $this->datetime_step = $this->dateIntervalToSeconds($step);
        }
        else {
            $this->datetime_step = $step;
        }
        return $this;
    }

    /**
     * Convert a DateInterval to a total number of seconds.
     * Note: months and years are not supported as they have variable length.
     */
    protected function dateIntervalToSeconds(DateInterval $interval): int
    {
        return ($interval->d * 86400)
            + ($interval->h * 3600)
            + ($interval->i * 60)
            + $interval->s;
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
            $this->setValue($this->formValue()->format('Y-m-d\TH:i:s'));
        else
            $this->setValue(null);
        if ($this->formDisabled())
            $this->attributes()['disabled'] = BooleanAttribute::true;
        else
            unset($this->attributes()['disabled']);
        // minimum attribute
        if ($this->datetime_min !== null)
            $this->attributes()['min'] = $this->datetime_min->format('Y-m-d\TH:i:s');
        else
            unset($this->attributes()['min']);
        // maximum attribute
        if ($this->datetime_max !== null)
            $this->attributes()['max'] = $this->datetime_max->format('Y-m-d\TH:i:s');
        else
            unset($this->attributes()['max']);
        // step attribute
        if ($this->datetime_step !== null)
            $this->attributes()['step'] = $this->datetime_step;
        else
            unset($this->attributes()['step']);
    }

    /**
     * @inheritDoc
     */
    public function validateSelf(): string|null
    {
        $errors = [];
        $value = $this->formValue();
        // check minimum
        if ($this->datetime_min !== null && $value < $this->datetime_min)
            $errors[] = sprintf("must not be before %s", $this->datetime_min->format('Y-m-d H:i:s'));
        // check maximum
        if ($this->datetime_max !== null && $value > $this->datetime_max)
            $errors[] = sprintf("must not be after %s", $this->datetime_max->format('Y-m-d H:i:s'));
        // check step — only if a default is set to use as base
        if ($this->datetime_step !== null && $this->formDefault() !== null) {
            $base_timestamp = $this->formDefault()->getTimestamp();
            $value_timestamp = $value?->getTimestamp();
            $remainder = ($value_timestamp - $base_timestamp) % $this->datetime_step;
            if ($remainder !== 0)
                $errors[] = sprintf("must be a multiple of %d seconds from %s", $this->datetime_step, $this->formDefault()->format('Y-m-d H:i:s'));
        }
        // return result
        if ($errors)
            return "Invalid datetime: " . implode(', ', $errors);
        else
            return null;
    }

}
