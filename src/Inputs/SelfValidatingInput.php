<?php

namespace Joby\Smol\Form\Inputs;

/**
 * Interface for InputInterfaces that can self-validate. Validator objects should check if their Input is one of these, and if so allow it to validate itself after required checks, but before outside rules, and short-circuit if this step fails.
 */
interface SelfValidatingInput
{

    /**
     * Run internal self-validation checks and return null for no error or a string error message if something is wrong.
     */
    public function validateSelf(): string|null;

}
