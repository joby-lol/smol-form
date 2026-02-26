<?php

namespace Joby\Smol\Form\Inputs\Helpers;

use Joby\Smol\Form\FormTestCase;

class HelperRadioTest extends FormTestCase
{

    // --- rendering ---

    public function test_renders_as_radio_input(): void
    {
        $radio = new HelperRadio('my_field', 'option_a');
        $this->assertStringContainsString('type="radio"', (string) $radio);
    }

    public function test_has_correct_name(): void
    {
        $radio = new HelperRadio('my_field', 'option_a');
        $this->assertStringContainsString('name="my_field"', (string) $radio);
    }

    public function test_has_correct_value(): void
    {
        $radio = new HelperRadio('my_field', 'option_a');
        $this->assertStringContainsString('value="option_a"', (string) $radio);
    }

    // --- accessors ---

    public function test_name_returns_correct_value(): void
    {
        $radio = new HelperRadio('my_field', 'option_a');
        $this->assertEquals('my_field', $radio->name());
    }

    public function test_value_returns_correct_value(): void
    {
        $radio = new HelperRadio('my_field', 'option_a');
        $this->assertEquals('option_a', $radio->value());
    }

    // --- form attribute ---

    public function test_form_attribute_set_when_attached_to_form(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $radio = new HelperRadio('my_field', 'option_a');
        $form->addChild($radio);
        $this->assertStringContainsString('form="test_form"', (string) $radio);
    }

    public function test_form_attribute_absent_without_form(): void
    {
        $radio = new HelperRadio('my_field', 'option_a');
        $this->assertStringNotContainsString('form=', (string) $radio);
    }

}
