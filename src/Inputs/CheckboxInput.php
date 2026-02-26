<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form\Inputs;

use Joby\HTML\Helpers\BooleanAttribute;
use Joby\HTML\Html5\Forms\LabelTag;
use Joby\HTML\Html5\InlineTextSemantics\SpanTag;
use Joby\Smol\Form\Form;
use Joby\Smol\Form\Inputs\Helpers\HelperCheckbox;

/**
 * Represents a single checkbox input, wrapped in a label with text.
 * 
 * @implements InputInterface<bool>
 */
class CheckboxInput extends LabelTag implements InputInterface, SelfLabeledInput
{

    /** @use InputTrait<bool> */
    use InputTrait;

    protected SpanTag $form_span_tag;

    protected HelperCheckbox $form_checkbox;

    public function __construct(string $name, string $label, bool|null $default = null)
    {
        parent::__construct();
        $this->form_name = $name;
        $this->form_label = $label;
        $this->form_default = $default;
        $this->form_checkbox = new HelperCheckbox($name, !!$default);
        $this->form_span_tag = new SpanTag();
        $this->form_span_tag->addChild($label);
        $this->addChild($this->form_checkbox);
        $this->addChild($this->form_span_tag);
        $this->classes()->add('smol-checkbox');
    }

    /**
     * @inheritDoc
     */
    protected function formSubmittedValue(): mixed
    {
        return $this->parentOfType(Form::class)
                ?->getSubmittedValue($this->form_checkbox->name())
            === 'on';
    }

    /**
     * @inheritDoc
     */
    protected function formSyncRenderSettings(): void
    {
        $this->setID($this->formAnchorId());
        $this->form_checkbox->setRequired($this->formValidator()->required());
        $this->form_checkbox->attributes()['checked'] = $this->formSubmittedValue()
            ? BooleanAttribute::true
            : BooleanAttribute::false;
    }

}
