<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form;

use Joby\HTML\Html5\Forms\LabelTag;
use Joby\HTML\Html5\TextContentTags\DivTag;
use Joby\Smol\Form\Form;
use Joby\Smol\Form\Inputs\DisableableInput;
use Joby\Smol\Form\Inputs\InputInterface;
use Joby\Smol\Form\Inputs\SelfLabeledInput;
use Joby\Smol\Form\Validation\ValidationAccumulatorTrait;
use Stringable;

/**
 * Wrapper for an Input object, which will pull its label and validation state information from the Input it wraps. Also displays optional help text, and shows validation messages from its Input.
 */
class Field extends DivTag
{

    use ValidationAccumulatorTrait;

    protected LabelTag|null $form_label;

    protected DivTag $form_help_wrapper;

    protected DivTag $form_validation_wrapper;

    /**
     * @template T
     * @param InputInterface<T> $input
     */
    public function __construct(
        protected InputInterface $input,
    )
    {
        parent::__construct();
        $this->classes()->add('smol-field');
        // set up label
        if ($input instanceof SelfLabeledInput) {
            $this->form_label = null;
        }
        else {
            $this->form_label = new LabelTag();
            $this->form_label->classes()->add('smol-field__label');
            $this->addChild($this->form_label);
        }
        // set up input
        $this->addChild($this->input);
        // set up help text container
        $this->form_help_wrapper = new DivTag();
        $this->form_help_wrapper->classes()->add('smol-field__help');
        $this->addChild($this->form_help_wrapper);
        // set up validation message container
        $this->form_validation_wrapper = new DivTag();
        $this->form_validation_wrapper->classes()->add('smol-field__validation');
        $this->addChild($this->form_validation_wrapper);
        // sync label from input
        if ($this->form_label) {
            $this->form_label->setFor($this->input->formName());
            $this->form_label->clearChildren();
            $this->form_label->addChild($this->input->formLabel());
        }
    }

    /**
     * Add the provided text/HTML to the help text container so that it will display alongside the Input (usually below it).
     */
    public function addHelpText(string|Stringable $help): static
    {
        $wrapper = new DivTag();
        $wrapper->classes()->add('smol-field__help__item');
        $wrapper->addChild($help, skip_sanitize: true);
        $this->form_help_wrapper->addChild($wrapper);
        return $this;
    }

    public function __toString(): string
    {
        // sync label from input
        if ($this->form_label) {
            $this->form_label->setFor($this->input->formInputId());
            $this->form_label->clearChildren();
            $this->form_label->addChild($this->input->formLabel());
        }
        // sync disabled class
        if ($this->input instanceof DisableableInput && $this->input->formDisabled())
            $this->classes()->add('smol-field--disabled');
        else
            $this->classes()->remove('smol-field--disabled');
        // sync validation messages
        $this->form_validation_wrapper->clearChildren();
        if ($this->parentOfType(Form::class)?->isFormAttempted()) {
            $messages = $this->accumulatedValidationMessages(force_refresh: true);
            if ($messages)
                $this->classes()->add('smol-field--invalid');
            foreach ($messages as $message) {
                $message_div = new DivTag();
                $message_div->classes()->add('smol-field__validation__message');
                $message_div->addChild($message);
                $this->form_validation_wrapper->addChild($message_div);
            }
        }
        // print output
        return parent::__toString();
    }

}
