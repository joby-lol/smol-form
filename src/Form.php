<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form;

use Generator;
use Joby\HTML\Html5\Forms\FormTag;
use Joby\HTML\Html5\TextContentTags\DivTag;
use Joby\Smol\Form\Inputs\InputInterface;
use Joby\Smol\Form\Validation\ValidationAccumulatorTrait;
use Joby\Smol\Request\Request;
use RuntimeException;
use Stringable;

/**
 * @method string|Stringable id()
 */
class Form extends DivTag
{

    use ValidationAccumulatorTrait;

    /**
     * Request data to be used for getting submitted information and determining submission status.
     */
    protected Request $request;

    /**
     * Internal object for managing the actual on-page <form> tag
     */
    protected FormTag $form;

    /**
     * Internal object for setting a special CSRF protection input
     */
    protected CsrfInput|null $csrf = null;

    /**
     * Internal object for setting a special input to tell if the form is submitted
     */
    protected SubmissionInput $submission;

    /**
     * Array of callbacks, each of which will be passed this object on successful form submission and validation
     * @var array<callable(Form):mixed> $submit_callbacks
     */
    protected array $submit_callbacks = [];

    /**
     * Whether the process() method has been called yet to finalize and validate this form, run its callbacks, and prepare it for final printing.
     * @var bool
     */
    protected bool $processed = false;

    protected DivTag $validation_message_container;

    protected DivTag $submit_button_container;

    public function __construct(
        string $id,
        Request|null $request = null,
        CsrfInput|null $csrf = new CsrfInput,
    )
    {
        parent::__construct();
        $this->classes()->add('smol-form');
        $this->request = $request ?? Request::current();
        // set up actual form tag
        $this->form = new FormTag();
        $this->form->setID($id);
        $this->form->setMethod("POST");
        $this->form->setEnctype("application/x-www-form-urlencoded");
        $this->form->setAction($this->request->url);
        $this->addChild($this->form);
        $this->form->styles()['display'] = 'none';
        $this->form->styles()['visibility'] = 'hidden';
        // set up validation error display block
        $this->validation_message_container = new DivTag();
        $this->validation_message_container->classes()->add('smol-form__validation');
        $this->addChild($this->validation_message_container);
        // set up CSRF token input
        if ($csrf instanceof CsrfInput) {
            $this->csrf = $csrf;
            $this->form->addChild($csrf);
        }
        // set up submission flag input
        $this->submission = new SubmissionInput();
        $this->form->addChild($this->submission);
        // set up submit button container
        $this->submit_button_container = new DivTag();
        $this->submit_button_container->classes()->add('smol-form__submit');
        $this->addChild($this->submit_button_container);
        $button = new SubmitButton();
        $button->addChild('Submit');
        $this->submit_button_container->addChild($button);
    }

    /**
     * Add a callback that will be executed upon successful and fully-validated submission of this form.
     * @param callable(Form):mixed $callback
     */
    public function addCallback(callable $callback): static
    {
        $this->submit_callbacks[] = $callback;
        return $this;
    }

    public function setMethodGet(): static
    {
        $this->form->setMethod('GET');
        return $this;
    }

    public function setMethodPost(): static
    {
        $this->form->setMethod('POST');
        return $this;
    }

    public function __toString(): string
    {
        $this->removeChild($this->submit_button_container)
            ->addChild($this->submit_button_container);
        return parent::__toString();
    }

    /**
     * Finalize and validate this form, run its callbacks, and prepare it for final printing.
     */
    public function finalize(): static
    {
        // only do this once
        if ($this->processed)
            return $this;
        $this->processed = true;
        // check state
        // end immediately if not submitted
        if (!$this->isFormAttempted())
            return $this;
        // end immediately if CSRF is invalid
        if (!$this->isCsrfValid())
            return $this;
        // we made it this far, so it should show as submitted
        $this->classes()->add('smol-form--submitted');
        // if there are validation messages display them and end
        $messages = $this->accumulatedValidationMessages(true, true);
        if ($messages) {
            $this->classes()->add('smol-form--invalid');
            foreach ($messages as $message) {
                $message_div = new DivTag();
                $message_div->classes()->add('smol-form__validation__message');
                $message_div->addChild($message, skip_sanitize: true);
                $this->validation_message_container->addChild($message_div);
            }
            return $this;
        }
        // if we made it this far it's submitted and valid, rotate csrf and run callbacks
        $this->csrf?->rotateToken();
        foreach ($this->submit_callbacks as $callback) {
            $callback($this);
        }
        // return for fluent chaining
        return $this;
    }

    /**
     * Determine whether the form has been submitted and its CSRF token has been validated if applicable.
     */
    public function isFormSubmitted(): bool
    {
        return $this->isFormAttempted()
            && $this->isCsrfValid();
    }

    public function isFormAttempted(): bool
    {
        return $this->form->id() === $this->getSubmittedValue($this->submission->name());
    }

    /**
     * Get the ID of the internal <form> tag that actually does the submitting of this form.
     */
    public function formId(): string
    {
        return $this->form->id()
            ?? throw new RuntimeException("FormTag is missing it's ID");
    }

    /**
     * Get a specific input by name.
     * 
     * @return InputInterface<mixed>
     */
    public function field(string $name): InputInterface|null
    {
        foreach ($this->inputs() as $field) {
            if ($field->formName() === $name)
                return $field;
        }
        return null;
    }

    /**
     * Walk the form to find all its inputs.
     * 
     * @return Generator<InputInterface<mixed>>
     */
    public function inputs(): Generator
    {
        // @phpstan-ignore-next-line the default type of InputInterface is in fact mixed
        return $this->walk(InputInterface::class, [InputInterface::class]);
    }

    /**
     * Determine whether the currently-submitted CSRF token is valid, if applicable.
     */
    protected function isCsrfValid(): bool
    {
        return $this->csrf?->validateToken($this->getSubmittedValue($this->csrf->name()))
            ?? true;
    }

    /**
     * Get a string value from either GET or POST, depending on this form's method.
     */
    public function getSubmittedValue(string|null $name): string|null
    {
        if ($name === null)
            return null;
        if ($this->form->method() === 'POST')
            return $this->request->post->getString($name);
        else
            return $this->request->url->query?->getString($name);
    }

}
