<?php

namespace Joby\Smol\Form\Inputs;

use Joby\Smol\Form\FormTestCase;

class TextInputTest extends FormTestCase
{

    public function test_renders_as_text_input(): void
    {
        $input = new TextInput('my_field', 'My field');
        $this->assertStringContainsString('type="text"', (string) $input);
    }

    public function test_has_correct_name(): void
    {
        $input = new TextInput('my_field', 'My field');
        $this->assertStringContainsString('name="my_field"', (string) $input);
    }

    public function test_has_smol_input_class(): void
    {
        $input = new TextInput('my_field', 'My field');
        $this->assertStringContainsString('smol-text', (string) $input);
    }

    public function test_label_is_set(): void
    {
        $input = new TextInput('my_field', 'My Label');
        $this->assertEquals('My Label', $input->formLabel());
    }

    public function test_input_value_returns_null_without_submission(): void
    {
        $input = new TextInput('my_field', 'My field');
        $this->assertNull($input->formValue());
    }

    public function test_input_value_returns_default_without_submission(): void
    {
        $input = new TextInput('my_field', 'My field');
        $input->setFormDefault('default value');
        $this->assertEquals('default value', $input->formValue());
    }

    public function test_input_value_returns_submitted_value(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'submitted value']);
        $input = new TextInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertEquals('submitted value', $input->formValue());
    }

    public function test_submitted_value_renders_in_output(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'submitted value']);
        $input = new TextInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertStringContainsString('value="submitted value"', (string) $input);
    }

    public function test_input_value_returns_null_when_disabled(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'submitted value']);
        $input = new TextInput('my_field', 'My field');
        $form->addChild($input);
        $input->setFormDisabled(true);
        $this->assertNull($input->formValue());
    }

    public function test_placeholder_not_overridden_by_default_when_already_set(): void
    {
        $input = new TextInput('my_field', 'My field');
        $input->setPlaceholder('custom placeholder');
        $input->setFormDefault('default value');
        $this->assertStringContainsString('placeholder="custom placeholder"', (string) $input);
    }

    public function test_set_placeholder_is_fluent(): void
    {
        $input = new TextInput('my_field', 'My field');
        $this->assertSame($input, $input->setPlaceholder('placeholder'));
    }

}
