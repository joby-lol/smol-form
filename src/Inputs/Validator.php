<?php

namespace Joby\Smol\Form\Inputs;

/**
 * Interface for a validator attached to a single InputInterface. The validator owns all validation concerns for its input: required checking, validation rules, and validation state. Rules are callables that receive the input's typed value and return null on pass or a string error message on failure.
 *
 * @template InputReturnType of mixed
 */
class Validator
{

    protected bool $run = false;

    protected bool $required = false;

    protected string|null $required_message = null;

    /**
     * @var array<string> $errors
     */
    protected array $errors = [];

    /**
     * @var array<(callable(InputReturnType):(string|null))> $rules
     */
    protected array $rules = [];

    /**
     * The input this validator is attached to.
     *
     * @param InputInterface<InputReturnType> $input
     */
    public function __construct(protected InputInterface $input) {}

    /**
     * Run validation, optionally forcing it to run again even if it has already been run. Checks required state first and short-circuits if the value is null and required, so that no other error messages appear for empty inputs.
     */
    public function runValidation(bool $force_run = false): static
    {
        // skip if already run
        if ($this->run && !$force_run)
            return $this;
        // reset and run validation
        $this->resetValidation();
        $this->run = true;
        // get value for next steps
        /** @var InputReturnType $value */
        $value = $this->input->formValue();
        // first check required state
        if ($this->required) {
            if (empty($value)) {
                $this->errors[] = $this->required_message
                    ?? "Field is required";
                return $this;
            }
        }
        // short circuit for non-required empty fields
        // unless they return some sort of array value
        if (empty($value) && $value !== [])
            return $this;
        // do self-validation if input supports it
        if ($this->input instanceof SelfValidatingInput) {
            if (($error = $this->input->validateSelf()) !== null) {
                $this->errors[] = $error;
                return $this;
            }
        }
        // run all rules
        foreach ($this->rules as $rule) {
            $result = $rule($value);
            if ($result !== null)
                $this->errors[] = $result;
        }
        // return for chaining
        return $this;
    }

    /**
     * Get all validation errors.
     * 
     * @return string[]
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Reset validation state to unrun with no error messages. Does not remove rules.
     */
    public function resetValidation(): static
    {
        $this->run = false;
        $this->errors = [];
        return $this;
    }

    /**
     * Set whether this input is required. A required input with a null value will fail validation before any rules are run.
     */
    public function setRequired(bool $required): static
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Set the error message to display when a required constraint fails, or reset it to default by passing null.
     */
    public function setRequiredErrorMessage(string|null $error_message): static
    {
        $this->required_message = $error_message;
        return $this;
    }

    /**
     * Whether this input is required.
     */
    public function required(): bool
    {
        return $this->required;
    }

    /**
     * Add a validation rule. The callable receives the input's typed value and should return null if the value is valid, or a string error message if not.
     *
     * @param (callable(InputReturnType):(string|null)) $rule
     */
    public function addRule(callable $rule): static
    {
        $this->rules[] = $rule;
        return $this;
    }

}
