<?php

namespace Joby\Smol\Form\Inputs;

use Joby\Smol\Form\FormTestCase;

class RadioInputTest extends FormTestCase
{

    protected function options(): array
    {
        return [
            'red'   => 'Red',
            'green' => 'Green',
            'blue'  => 'Blue',
        ];
    }

    // --- rendering ---

    public function test_renders_radio_inputs(): void
    {
        $input = new RadioInput('my_field', 'My field', $this->options());
        $this->assertStringContainsString('type="radio"', (string) $input);
    }

    public function test_has_smol_radio_class(): void
    {
        $input = new RadioInput('my_field', 'My field', $this->options());
        $this->assertStringContainsString('smol-radio', (string) $input);
    }

    public function test_label_is_set(): void
    {
        $input = new RadioInput('my_field', 'My Label', $this->options());
        $this->assertEquals('My Label', $input->formLabel());
    }

    public function test_legend_appears_in_output(): void
    {
        $input = new RadioInput('my_field', 'My field', $this->options());
        $this->assertStringContainsString('My field', (string) $input);
    }

    public function test_option_labels_appear_in_output(): void
    {
        $input = new RadioInput('my_field', 'My field', $this->options());
        $output = (string) $input;
        $this->assertStringContainsString('Red', $output);
        $this->assertStringContainsString('Green', $output);
        $this->assertStringContainsString('Blue', $output);
    }

    public function test_renders_correct_number_of_radios(): void
    {
        $input = new RadioInput('my_field', 'My field', $this->options());
        $this->assertEquals(3, substr_count((string) $input, 'type="radio"'));
    }

    public function test_all_radios_share_field_name(): void
    {
        $input = new RadioInput('my_field', 'My field', $this->options());
        $this->assertEquals(3, substr_count((string) $input, 'name="my_field"'));
    }

    public function test_renders_with_empty_options(): void
    {
        $input = new RadioInput('my_field', 'My field', []);
        $this->assertStringNotContainsString('type="radio"', (string) $input);
    }

    // --- formInputId ---

    public function test_form_input_id_returns_null(): void
    {
        $input = new RadioInput('my_field', 'My field', $this->options());
        $this->assertNull($input->formInputId());
    }

    // --- formValue without a form ---

    public function test_form_value_returns_null_without_form(): void
    {
        $input = new RadioInput('my_field', 'My field', $this->options());
        $this->assertNull($input->formValue());
    }

    public function test_form_value_returns_default_without_form(): void
    {
        $input = new RadioInput('my_field', 'My field', $this->options(), 'green');
        $this->assertEquals('green', $input->formValue());
    }

    // --- formValue with unsubmitted form ---

    public function test_form_value_returns_null_on_unsubmitted_form(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $input = new RadioInput('my_field', 'My field', $this->options());
        $form->addChild($input);
        $this->assertNull($input->formValue());
    }

    public function test_form_value_returns_default_on_unsubmitted_form(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $input = new RadioInput('my_field', 'My field', $this->options(), 'blue');
        $form->addChild($input);
        $this->assertEquals('blue', $input->formValue());
    }

    // --- formValue with submitted form ---

    public function test_form_value_returns_submitted_option(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'green']);
        $input = new RadioInput('my_field', 'My field', $this->options());
        $form->addChild($input);
        $this->assertEquals('green', $input->formValue());
    }

    public function test_form_value_returns_null_when_nothing_submitted(): void
    {
        $form = $this->make_submitted_form('test_form', []);
        $input = new RadioInput('my_field', 'My field', $this->options());
        $form->addChild($input);
        $this->assertNull($input->formValue());
    }

    public function test_form_value_returns_null_for_invalid_submitted_value(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'purple']);
        $input = new RadioInput('my_field', 'My field', $this->options());
        $form->addChild($input);
        $this->assertNull($input->formValue());
    }

    // --- checked state renders correctly ---

    public function test_selected_option_renders_as_checked_after_submission(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'red']);
        $input = new RadioInput('my_field', 'My field', $this->options());
        $form->addChild($input);
        $this->assertEquals(1, substr_count((string) $input, 'checked'));
    }

    public function test_no_option_renders_checked_when_nothing_submitted(): void
    {
        $form = $this->make_submitted_form('test_form', []);
        $input = new RadioInput('my_field', 'My field', $this->options());
        $form->addChild($input);
        $this->assertStringNotContainsString('checked', (string) $input);
    }

    public function test_default_option_renders_checked_without_form(): void
    {
        $input = new RadioInput('my_field', 'My field', $this->options(), 'blue');
        $this->assertEquals(1, substr_count((string) $input, 'checked'));
    }

}
