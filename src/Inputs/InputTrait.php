<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form\Inputs;

use Joby\Smol\Form\Form;

/**
 * @template InputReturnType of mixed
 */
trait InputTrait
{

    /**
     * @var Validator<InputReturnType> $form_validator
     */
    protected Validator $form_validator;

    protected string $form_name;

    protected string $form_label;

    /**
     * @var InputReturnType|null $form_default
     */
    protected mixed $form_default = null;

    /**
     * Consumers of InputTrait should use this method as their opportunity to sync any rendering settings from the form methods into their underlying HTML implementation.
     */
    abstract protected function formSyncRenderSettings(): void;

    /**
     * Consumers of InputTrait should implement this method, and it will be used to convert any underlying submitted data into the final output format that this input is designed to return.
     * 
     * Note that implementations must return null ONLY when there is genuinely no submitted value - they should return an empty string rather than null for empty text fields, so that a user deliberately clearing a field is not overridden by the default value.
     * 
     * @return InputReturnType|null
     */
    abstract protected function formSubmittedValue(): mixed;

    /**
     * Override __toString() to hook into it and run formSyncRenderSettings before the parent implementation runs so that this input has a chance to sync its display with this trait's settings.
     * @return string
     */
    public function __toString(): string
    {
        $this->formSyncRenderSettings();
        return parent::__toString();
    }

    /**
     * Set the default value to be used in this form if no value is submitted by the user.
     * 
     * @param InputReturnType|null $default
     */
    public function setFormDefault(mixed $default): static
    {
        $this->form_default = $default;
        return $this;
    }

    /**
     * The default value to be used in this form if no value is submitted by the user.
     * 
     * @return InputReturnType|null
     */
    public function formDefault(): mixed
    {
        return $this->form_default;
    }

    /**
     * The current processed value of this input. Returns null if the input has no value. When no form is present or the form has not been attempted, returns the default value. When the form has been attempted, returns the submitted value if non-null, or the default otherwise.
     *
     * @return InputReturnType|null
     */
    public function formValue(): mixed
    {
        if ($this instanceof DisableableInput && $this->formDisabled())
            return null;
        $form = $this->parentOfType(Form::class);
        if (!$form)
            return $this->formDefault();
        if (!$form->isFormAttempted())
            return $this->formDefault();
        return $this->formSubmittedValue()
            ?? $this->formDefault();
    }

    /**
     * A stable, unique HTML ID that can be used to scroll to this input (i.e. from error messages shown in its ancestor containers). Typically derived from the parent form's ID and this input's name.
     */
    public function formAnchorId(): string
    {
        $id = $this->parentOfType(Form::class)?->formId()
            ?? '_unknown_form_';
        return $id . '_' . $this->formName();
    }

    /**
     * A stable, unique HTML ID that can be used to focus this input directly, if applicable. Typically the same as formAnchorId, but not always.
     */
    public function formInputId(): string|null
    {
        return $this->formAnchorId();
    }

    /**
     * The validator for this input. The validator owns all validation state, required checking, and validation rules.
     *
     * @return Validator<InputReturnType>
     */
    public function formValidator(): Validator
    {
        if (!isset($this->form_validator))
            $this->form_validator = new Validator($this);
        return $this->form_validator;
    }

    /**
     * The name of this input as it appears in form submissions.
     */
    public function formName(): string
    {
        return $this->form_name;
    }

    /**
     * Set the human-readable label for this input.
     */
    public function setFormLabel(string $form_label): static
    {
        $this->form_label = $form_label;
        return $this;
    }

    /**
     * The human-readable label for this input, used in error messages and displayed alongside the input in Field wrappers.
     */
    public function formLabel(): string
    {
        return $this->form_label;
    }

}
