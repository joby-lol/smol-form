<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form\Inputs;

use Joby\HTML\Helpers\BooleanAttribute;
use Joby\HTML\Html5\Forms\FieldsetTag;
use Joby\HTML\Html5\Forms\LabelTag;
use Joby\HTML\Html5\Forms\LegendTag;
use Joby\HTML\Html5\InlineTextSemantics\SpanTag;
use Joby\Smol\Form\Form;
use Joby\Smol\Form\Inputs\Helpers\HelperRadio;

/**
 * Represents a group of radio button inputs, allowing the user to select exactly one option.
 *
 * @implements InputInterface<string>
 */
class RadioInput extends FieldsetTag implements InputInterface, SelfLabeledInput
{

    /** @use InputTrait<string> */
    use InputTrait;

    /**
     * @var array<string,string> $form_options
     */
    protected array $form_options = [];

    /**
     * @var array<string,HelperRadio> $form_radios
     */
    protected array $form_radios = [];

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
        $this->classes()->add('smol-radio');
        // need to build radios immediately so they can be read for validation before rendering
        $this->formBuildRadios();
    }

    /**
     * Consumers of InputTrait should implement this method, and it will be used to convert any underlying submitted data into the final output format that this input is designed to return.
     * 
     * @return string|null
     */
    protected function formSubmittedValue(): mixed
    {
        $value = $this->parentOfType(Form::class)?->getSubmittedValue($this->formName());
        if ($value === null)
            return null;
        if (!array_key_exists($value, $this->form_options))
            return null;
        return $value;
    }

    /**
     * RadioInput does not have a single input field that can be focused directly, so this always returns null.
     *
     * @return null
     */
    public function formInputId(): string|null
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    protected function formSyncRenderSettings(): void
    {
        $this->setID($this->formAnchorId());
        // need to rebuild radios right before render so they can have appropriate checked state
        $this->formBuildRadios();
    }

    /**
     * Clear children and build HelperRadio objects for each option.
     *
     * Also saves each radio under $form_radios so we can reference them later.
     */
    protected function formBuildRadios(): void
    {
        $this->clearChildren();
        $form = $this->parentOfType(Form::class);
        $default_checked = $form?->getSubmittedValue($this->formName()) ?? $this->formDefault();
        // add legend tag
        $this->addChild((new LegendTag())->addChild($this->formLabel()));
        // build radios
        $this->form_radios = [];
        foreach ($this->form_options as $key => $label) {
            $wrapper = new LabelTag();
            $wrapper->classes()->add('smol-radio__option');
            $radio = new HelperRadio($this->formName(), $key);
            if ($key == $default_checked)
                $radio->attributes()['checked'] = BooleanAttribute::true;
            $this->form_radios[$key] = $radio;
            $wrapper->addChild($radio);
            $span = new SpanTag();
            $span->addChild($label);
            $wrapper->addChild($span);
            $this->addChild($wrapper);
        }
    }

}
