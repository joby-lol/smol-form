<?php

namespace Joby\Smol\Form;

use Joby\Smol\Form\FormTestCase;
use Joby\Smol\Form\Inputs\MultiCheckboxInput;
use Joby\Smol\Form\Inputs\TextInput;

class FieldTest extends FormTestCase
{

    protected function make_field(string $label = 'Test Field', string $name = 'test'): Field
    {
        return new Field(new TextInput($name, $label));
    }

    public function test_has_smol_field_class(): void
    {
        $field = $this->make_field();
        $this->assertStringContainsString('smol-field', (string) $field);
    }

    public function test_renders_label(): void
    {
        $field = $this->make_field('My Label', 'my_input');
        $this->assertStringContainsString('My Label', (string) $field);
    }

    public function test_label_for_matches_input_id(): void
    {
        $input = new TextInput('my_input', 'My Label');
        $field = new Field($input);
        $this->assertEquals('_unknown_form__my_input', $input->formInputId());
        $this->assertStringContainsString('for="' . $input->formInputId() . '"', (string) $field);
    }

    public function test_renders_help_text_container(): void
    {
        $field = $this->make_field();
        $this->assertStringContainsString('smol-field__help', (string) $field);
    }

    public function test_renders_validation_container(): void
    {
        $field = $this->make_field();
        $this->assertStringContainsString('smol-field__validation', (string) $field);
    }

    public function test_add_help_text_renders_in_output(): void
    {
        $field = $this->make_field();
        $field->addHelpText('This is some help text');
        $this->assertStringContainsString('This is some help text', (string) $field);
    }

    public function test_add_help_text_is_fluent(): void
    {
        $field = $this->make_field();
        $this->assertSame($field, $field->addHelpText('help'));
    }

    public function test_no_invalid_class_without_validation_errors(): void
    {
        $field = $this->make_field();
        $this->assertStringNotContainsString('smol-field--invalid', (string) $field);
    }

    public function test_invalid_class_added_when_validation_errors_present(): void
    {
        $input = new TextInput('required_field', 'Required Field');
        $input->formValidator()->setRequired(true);
        $field = new Field($input);
        $form = $this->make_submitted_form('test_form', ['_smol' => 'test_form']);
        $form->addChild($field);
        $input->formValidator()->runValidation(force_run: true);
        $this->assertStringContainsString('smol-field--invalid', (string) $field);
    }

    public function test_validation_messages_rendered_when_present(): void
    {
        $input = new TextInput('validated_field', 'Validated Field');
        $input->formValidator()->addRule(fn($i) => 'Always fails');
        $field = new Field($input);
        $form = $this->make_submitted_form('test_form', ['_smol' => 'test_form', 'validated_field' => 'something']);
        $form->addChild($field);
        $input->formValidator()->runValidation(force_run: true);
        $this->assertStringContainsString('Always fails', (string) $field);
    }

    public function test_disabled_class_added_when_input_is_disabled(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $input = new TextInput('my_field', 'My field');
        $input->setFormDisabled(true);
        $field = new Field($input);
        $form->addChild($field);
        $this->assertStringContainsString('smol-field--disabled', (string) $field);
    }

    public function test_disabled_class_absent_when_input_is_not_disabled(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $input = new TextInput('my_field', 'My field');
        $field = new Field($input);
        $form->addChild($field);
        $this->assertStringNotContainsString('smol-field--disabled', (string) $field);
    }

    public function test_disabled_class_removed_when_input_is_reenabled(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $input = new TextInput('my_field', 'My field');
        $input->setFormDisabled(true);
        $field = new Field($input);
        $form->addChild($field);
        // confirm it's there first
        $this->assertStringContainsString('smol-field--disabled', (string) $field);
        // re-enable and confirm it's gone
        $input->setFormDisabled(false);
        $this->assertStringNotContainsString('smol-field--disabled', (string) $field);
    }

    public function test_non_disableable_input_never_gets_disabled_class(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        // MultiCheckboxInput does not implement DisableableInputInterface
        $input = new MultiCheckboxInput('my_field', 'My field', ['a' => 'Option A']);
        $field = new Field($input);
        $form->addChild($field);
        $this->assertStringNotContainsString('smol-field--disabled', (string) $field);
    }

}
