<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form\Inputs;

use Joby\HTML\Html5\Forms\FieldsetTag;
use Joby\HTML\Html5\Forms\LabelTag;
use Joby\HTML\Html5\Forms\LegendTag;
use Joby\HTML\Html5\InlineTextSemantics\SpanTag;
use Joby\Smol\Form\Form;
use Joby\Smol\Form\Inputs\Helpers\HelperCheckbox;

/**
 * Represents a list of checkbox inputs, allowing the user to select any number of them.
 * 
 * @implements InputInterface<array<string>>
 */
class MultiCheckboxInput extends FieldsetTag implements InputInterface, SelfLabeledInput, SelfValidatingInput
{

    /** @use InputTrait<array<string>> */
    use InputTrait;

    /**
     * @var array<string,string> $form_options
     */
    protected array $form_options = [];

    /**
     * @var array<string,HelperCheckbox> $form_checkboxes
     */
    protected array $form_checkboxes = [];

    protected int|null $form_min = null;

    protected int|null $form_max = null;

    /**
     * @param array<string,string> $options $options
     * @param array<string>|null $default
     */
    public function __construct(string $name, string $label, array $options, array|null $default = null)
    {
        parent::__construct();
        $this->form_name = $name;
        $this->form_label = $label;
        $this->form_options = $options;
        $this->form_default = $default;
        $this->classes()->add('smol-multicheckbox');
        // need to build checkboxes immediately so they can be read for validation before rendering
        $this->formBuildCheckboxes();
    }

    /**
     * Require that a minimum number of items be selected.
     */
    public function setFormMinSelections(int $form_min): static
    {
        $this->form_min = $form_min;
        return $this;
    }

    /**
     * Require that a maximum number of options be selected.
     */
    public function setFormMaxSelections(int $form_max): static
    {
        $this->form_max = $form_max;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function formSubmittedValue(): mixed
    {
        $form = $this->parentOfType(Form::class);
        if (!$form)
            return null;
        if (!$form->isFormAttempted())
            return null;
        // loop through child HelperCheckbox objects and build value
        // note that when it returns no values it does so as null, so that isRequired() works right on validator
        $result = array_keys(array_filter(
            $this->form_checkboxes,
            fn(HelperCheckbox $c) => $form->getSubmittedValue($c->name()) == 'on'
        ));
        return $result;
    }

    /**
     * MultiCheckboxInput does not have a single input field that can be focused directly, so this always returns null.
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
        // need to rebuild checkboxes right before render so they can have appropriate checked state based on actual get/post data
        $this->formBuildCheckboxes();
    }

    /**
     * clear children and build HelperCheckbox objects for each value
     * 
     * also save each actual checkbox under $form_checkboxes so we can find them later for getting the value
     */
    protected function formBuildCheckboxes(): void
    {
        $form = $this->parentOfType(Form::class);
        $this->clearChildren();
        // add legend tag
        $this->addChild((new LegendTag())->addChild($this->formLabel()));
        // build checkboxes
        $this->form_checkboxes = [];
        foreach ($this->form_options as $key => $label) {
            $wrapper = new LabelTag();
            $wrapper->classes()->add('smol-multicheckbox__option');
            $checkbox = new HelperCheckbox('opt_' . md5($this->formName() . $key));
            $checkbox->setDefault($form?->getSubmittedValue($checkbox->name()) == 'on');
            $this->form_checkboxes[$key] = $checkbox;
            $wrapper->addChild($checkbox);
            $span = new SpanTag();
            $span->addChild($label);
            $wrapper->addChild($span);
            $this->addChild($wrapper);
        }
    }

    /**
     * @inheritDoc
     */
    public function validateSelf(): string|null
    {
        $errors = [];
        $count = count($this->formValue() ?? []);
        // check minimum
        if ($this->form_min !== null && $count < $this->form_min)
            $errors[] = sprintf("must select at least %g", $this->form_min);
        // check maximum
        if ($this->form_max !== null && $count > $this->form_max)
            $errors[] = sprintf("must not select more than %g", $this->form_max);
        // return result
        if ($errors)
            return implode(', ', $errors);
        else
            return null;
    }
}
