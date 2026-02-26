<?php

namespace Joby\Smol\Form\Inputs;

use Joby\Smol\Form\FormTestCase;

class CheckboxInputTest extends FormTestCase
{

    // --- rendering ---

    public function test_renders_as_checkbox_input(): void
    {
        $input = new CheckboxInput('my_field', 'My field');
        $this->assertStringContainsString('type="checkbox"', (string) $input);
    }

    public function test_has_correct_name(): void
    {
        $input = new CheckboxInput('my_field', 'My field');
        $this->assertStringContainsString('name="my_field"', (string) $input);
    }

    public function test_has_smol_checkbox_class(): void
    {
        $input = new CheckboxInput('my_field', 'My field');
        $this->assertStringContainsString('smol-checkbox', (string) $input);
    }

    public function test_label_text_is_present_in_output(): void
    {
        $input = new CheckboxInput('my_field', 'My Label');
        $this->assertStringContainsString('My Label', (string) $input);
    }

    public function test_label_is_set(): void
    {
        $input = new CheckboxInput('my_field', 'My Label');
        $this->assertEquals('My Label', $input->formLabel());
    }

    // --- default value ---

    public function test_default_true_checks_checkbox(): void
    {
        $input = new CheckboxInput('my_field', 'My field', true);
        $this->assertStringContainsString('checked', (string) $input);
    }

    public function test_default_false_does_not_check_checkbox(): void
    {
        $input = new CheckboxInput('my_field', 'My field', false);
        $this->assertStringNotContainsString('checked="checked"', (string) $input);
    }

    public function test_default_null_does_not_check_checkbox(): void
    {
        $input = new CheckboxInput('my_field', 'My field', null);
        $this->assertStringNotContainsString('checked="checked"', (string) $input);
    }

    // --- formValue without a form ---

    public function test_form_value_returns_null_without_form(): void
    {
        $input = new CheckboxInput('my_field', 'My field');
        $this->assertNull($input->formValue());
    }

    public function test_form_value_returns_default_without_form_when_default_set(): void
    {
        $input = new CheckboxInput('my_field', 'My field', true);
        $this->assertTrue($input->formValue());
    }

    // --- formValue with unsubmitted form ---

    public function test_form_value_returns_null_on_unsubmitted_form(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $input = new CheckboxInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertNull($input->formValue());
    }

    public function test_form_value_returns_default_on_unsubmitted_form_when_default_set(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $input = new CheckboxInput('my_field', 'My field', true);
        $form->addChild($input);
        $this->assertTrue($input->formValue());
    }

    // --- formValue with submitted form ---

    public function test_form_value_returns_true_when_checked_on_submission(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'on']);
        $input = new CheckboxInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertTrue($input->formValue());
    }

    public function test_form_value_returns_false_when_unchecked_on_submission(): void
    {
        // Unchecked checkboxes are not included in POST data
        $form = $this->make_submitted_form('test_form', []);
        $input = new CheckboxInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertFalse($input->formValue());
    }

    // --- render state after submission ---

    public function test_checked_renders_after_checked_submission(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'on']);
        $input = new CheckboxInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertStringContainsString('checked', (string) $input);
    }

    public function test_not_checked_renders_after_unchecked_submission(): void
    {
        $form = $this->make_submitted_form('test_form', []);
        $input = new CheckboxInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertStringNotContainsString('checked="checked"', (string) $input);
    }

}
