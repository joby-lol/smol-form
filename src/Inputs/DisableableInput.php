<?php

namespace Joby\Smol\Form\Inputs;

/**
 * Optional interface for inputs that support being disabled. Not all input
 * types necessarily support disabling, so this is separate from InputInterface.
 */
interface DisableableInput
{

    /**
     * Whether this input is currently disabled. Disabled inputs should not
     * contribute their value to form processing.
     */
    public function formDisabled(): bool;

    /**
     * Set whether this input is disabled. Implementations should update their
     * markup appropriately (e.g. setting the disabled HTML attribute).
     */
    public function setFormDisabled(bool $disabled): static;

}
