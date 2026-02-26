<?php

namespace Joby\Smol\Form\Inputs;

use Joby\HTML\NodeInterface;

/**
 * Interface for a managed form input. Intentionally minimal - inputs are
 * responsible only for their identity, their current value, and providing
 * access to their validator. Form state awareness (submission, disabled state)
 * is handled elsewhere.
 *
 * @template InputReturnType of mixed
 */
interface InputInterface extends NodeInterface
{

    /**
     * The name of this input as it appears in form submissions.
     */
    public function formName(): string;

    /**
     * The human-readable label for this input, used in error messages and displayed alongside the input in Field wrappers.
     */
    public function formLabel(): string;

    /**
     * Set the human-readable label for this input.
     */
    public function setFormLabel(string $label): static;

    /**
     * A stable, unique HTML ID that can be used to scroll to this input (i.e. from error messages shown in its ancestor containers). Typically derived from the parent form's ID and this input's name.
     */
    public function formAnchorId(): string;

    /**
     * A stable, unique HTML ID that can be used to focus this input directly, if applicable. Typically the same as formAnchorId, but not always.
     */
    public function formInputId(): string|null;

    /**
     * The current processed value of this input. Returns null if the input has no value. Implementations should return their typed value regardless of form submission state - callers are responsible for deciding whether to use the value based on context.
     *
     * @return InputReturnType|null
     */
    public function formValue(): mixed;

    /**
     * Set the default value to be used in this form if no value is submitted by the user.
     * 
     * @param InputReturnType|null $default
     */
    public function setFormDefault(mixed $default): static;

    /**
     * The default value to be used in this form if no value is submitted by the user.
     * 
     * @return InputReturnType|null
     */
    public function formDefault(): mixed;

    /**
     * The validator for this input. The validator owns all validation state, required checking, and validation rules.
     *
     * @return Validator<InputReturnType>
     */
    public function formValidator(): Validator;

}
