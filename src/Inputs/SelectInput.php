<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form\Inputs;

use Joby\HTML\Helpers\BooleanAttribute;
use Joby\HTML\Html5\Forms\OptionTag;
use Joby\HTML\Html5\Forms\SelectTag;
use Joby\Smol\Form\Form;

/**
 * Represents a single-value select input.
 *
 * @implements InputInterface<string>
 */
class SelectInput extends SelectTag implements InputInterface, DisableableInput
{

    /** @use InputTrait<string> */
    use InputTrait;
    use DisableableInputTrait;

    /**
     * @var array<string,string> $form_options
     */
    protected array $form_options = [];

    protected string|null $form_empty_option = null;

    /**
     * @param array<string,string> $options
     * @param string|null $default
     */
    public function __construct(string $name, string $label, array $options, string|null $default = null)
    {
        parent::__construct();
        $this->form_name = $name;
        $this->form_label = $label;
        $this->form_options = $options;
        $this->form_default = $default;
        $this->classes()->add('smol-select');
    }

    /**
     * Set the label for the empty/placeholder option. If null, no empty option is rendered.
     */
    public function setEmptyOption(string|null $label): static
    {
        $this->form_empty_option = $label;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function formSubmittedValue(): mixed
    {
        $value = $this->parentOfType(Form::class)?->getSubmittedValue($this->formName());
        if ($value === null || $value === '')
            return null;
        if (!array_key_exists($value, $this->form_options))
            return null;
        return $value;
    }

    /**
     * @inheritDoc
     */
    protected function formSyncRenderSettings(): void
    {
        $this->setID($this->formAnchorId());
        $this->setRequired($this->formValidator()->required());
        $this->setName($this->formName());
        $this->setForm($this->parentOfType(Form::class)?->formId());
        if ($this->formDisabled())
            $this->attributes()['disabled'] = BooleanAttribute::true;
        else
            unset($this->attributes()['disabled']);
        $this->formBuildOptions();
    }

    /**
     * Clear children and rebuild option elements.
     */
    protected function formBuildOptions(): void
    {
        $this->clearChildren();
        $selected = $this->formValue();
        // empty option
        if ($this->form_empty_option !== null) {
            $option = new OptionTag();
            $option->setValue('');
            $option->addChild($this->form_empty_option);
            if ($selected === null)
                $option->attributes()['selected'] = BooleanAttribute::true;
            $this->addChild($option);
        }
        // regular options
        foreach ($this->form_options as $value => $label) {
            $option = new OptionTag();
            $option->setValue($value);
            $option->addChild($label);
            if ($value === $selected)
                $option->attributes()['selected'] = BooleanAttribute::true;
            $this->addChild($option);
        }
    }

}
