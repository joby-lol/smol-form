<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form\Inputs;

trait DisableableInputTrait
{

    protected bool $form_disabled = false;

    /**
     * Whether this input is currently disabled. Disabled inputs should not
     * contribute their value to form processing.
     */
    public function formDisabled(): bool
    {
        return $this->form_disabled;
    }

    /**
     * Set whether this input is disabled. Implementations should update their
     * markup appropriately (e.g. setting the disabled HTML attribute).
     */
    public function setFormDisabled(bool $disabled): static
    {
        $this->form_disabled = $disabled;
        return $this;
    }

}
