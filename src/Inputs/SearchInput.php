<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form\Inputs;

use Joby\HTML\Html5\Forms\InputTag\TypeValue;
use Joby\Smol\Form\Inputs\TextInput;

/**
 * @codeCoverageIgnore nothing worth testing here
 */
class SearchInput extends TextInput
{

    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->classes()->add('smol-search');
        $this->setType(TypeValue::search);
    }

}
