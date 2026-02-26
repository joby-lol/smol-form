<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form\Validation;

use Joby\HTML\Html5\InlineTextSemantics\ATag;
use Joby\Smol\Form\Inputs\InputInterface;
use Stringable;

/**
 * Trait to add validation wrapper tools to an object. Validation accumulators have tools for accumulating all validation messages from within their children, including optionally prepending anchor links to them, so that they can display error messages and/or change their own state based on child error state.
 */
trait ValidationAccumulatorTrait
{

    /**
     * @var array<string|Stringable>|null $accumulated_validation_messages
     */
    protected array|null $accumulated_validation_messages = null;

    /**
     * A list of all validation messages from all InputInterface children of this object.
     * @return array<string|Stringable>
     */
    public function accumulatedValidationMessages(bool $prepend_label = false, bool $force_refresh = false): array
    {
        if (!is_array($this->accumulated_validation_messages) || $force_refresh) {
            $messages = [];
            foreach ($this->walk(InputInterface::class) as $input) {
                $input->formValidator()->runValidation($force_refresh);
                foreach ($input->formValidator()->errors() as $message) {
                    if ($prepend_label) {
                        $link = new ATag();
                        $link->classes()->add('smol-form__validation-link');
                        $link->setHref('#' . $input->formAnchorId());
                        $link->addChild($input->formLabel() . ':');
                        $message = "<strong>$link</strong> $message";
                    }
                    $messages[] = $message;
                }
            }
            $this->accumulated_validation_messages = $messages;
        }
        return $this->accumulated_validation_messages;
    }

}
