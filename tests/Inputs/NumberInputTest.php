<?php

namespace Joby\Smol\Form\Inputs;

use Joby\Smol\Form\FormTestCase;

class NumberInputTest extends FormTestCase
{

    // --- rendering ---

    public function test_renders_as_number_input(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $this->assertStringContainsString('type="number"', (string) $input);
    }

    public function test_has_correct_name(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $this->assertStringContainsString('name="my_field"', (string) $input);
    }

    public function test_has_smol_text_class(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $this->assertStringContainsString('smol-text', (string) $input);
    }

    public function test_label_is_set(): void
    {
        $input = new NumberInput('my_field', 'My Label');
        $this->assertEquals('My Label', $input->formLabel());
    }

    // --- formValue ---

    public function test_form_value_returns_null_without_form(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $this->assertNull($input->formValue());
    }

    public function test_form_value_returns_default_without_form(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $input->setFormDefault(42.0);
        $this->assertEquals(42.0, $input->formValue());
    }

    public function test_form_value_returns_submitted_value(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '42']);
        $input = new NumberInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertEquals(42.0, $input->formValue());
    }

    public function test_form_value_returns_null_on_unsubmitted_form(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $input = new NumberInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertNull($input->formValue());
    }

    public function test_form_value_is_cast_to_float(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '3.14']);
        $input = new NumberInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertIsFloat($input->formValue());
        $this->assertEquals(3.14, $input->formValue());
    }

    // --- min/max/step attributes ---

    public function test_min_attribute_renders_when_set(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberMin(5.0);
        $this->assertStringContainsString('min="5"', (string) $input);
    }

    public function test_min_attribute_absent_when_not_set(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $this->assertStringNotContainsString('min=', (string) $input);
    }

    public function test_min_attribute_removed_when_set_to_null(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberMin(5.0);
        $input->setNumberMin(null);
        $this->assertStringNotContainsString('min=', (string) $input);
    }

    public function test_max_attribute_renders_when_set(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberMax(100.0);
        $this->assertStringContainsString('max="100"', (string) $input);
    }

    public function test_max_attribute_absent_when_not_set(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $this->assertStringNotContainsString('max=', (string) $input);
    }

    public function test_step_attribute_renders_when_set(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberStep(5.0);
        $this->assertStringContainsString('step="5"', (string) $input);
    }

    public function test_step_attribute_absent_when_not_set(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $this->assertStringNotContainsString('step=', (string) $input);
    }

    public function test_float_step_renders_correctly(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberStep(0.5);
        $this->assertStringContainsString('step="0.5"', (string) $input);
    }

    // --- fluency ---

    public function test_set_number_min_is_fluent(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $this->assertSame($input, $input->setNumberMin(0.0));
    }

    public function test_set_number_max_is_fluent(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $this->assertSame($input, $input->setNumberMax(100.0));
    }

    public function test_set_number_step_is_fluent(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $this->assertSame($input, $input->setNumberStep(5.0));
    }

    // --- validation: valid values ---

    public function test_valid_value_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '50']);
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberMin(0.0)->setNumberMax(100.0);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_empty_non_required_field_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', []);
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberMin(0.0)->setNumberMax(100.0);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_empty_required_field_fails_validation(): void
    {
        $form = $this->make_submitted_form('test_form', []);
        $input = new NumberInput('my_field', 'My field');
        $input->formValidator()->setRequired(true);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    // --- validation: min ---

    public function test_value_below_min_fails_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '-1']);
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberMin(0.0);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    public function test_value_at_min_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '0']);
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberMin(0.0);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_min_error_message_mentions_value(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '-1']);
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberMin(10.0);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertStringContainsString('10', $input->formValidator()->errors()[0]);
    }

    // --- validation: max ---

    public function test_value_above_max_fails_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '123']);
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberMax(100.0);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    public function test_value_at_max_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '100']);
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberMax(100.0);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_max_error_message_mentions_value(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '200']);
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberMax(100.0);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertStringContainsString('100', $input->formValidator()->errors()[0]);
    }

    // --- validation: step ---

    public function test_value_on_step_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '15']);
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberStep(5.0);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_value_off_step_fails_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '122']);
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberStep(5.0);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    public function test_step_error_says_multiple_of_when_base_is_zero(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '3']);
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberStep(5.0);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $errors = $input->formValidator()->errors();
        $this->assertStringContainsString('multiple of 5', $errors[0]);
        $this->assertStringNotContainsString('away from', $errors[0]);
    }

    public function test_step_error_mentions_base_when_base_is_not_multiple_of_step(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '4']);
        $input = new NumberInput('my_field', 'My field');
        $input->setFormDefault(3.0);
        $input->setNumberStep(5.0);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $errors = $input->formValidator()->errors();
        $this->assertStringContainsString('away from', $errors[0]);
    }

    public function test_float_step_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '0.3']);
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberStep(0.5);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    public function test_float_step_valid_value_passes(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '1.5']);
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberStep(0.5);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    // --- validation: multiple errors ---

    public function test_multiple_errors_combined_in_single_message(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '123']);
        $input = new NumberInput('my_field', 'My field');
        $input->setNumberMax(100.0)->setNumberStep(5.0);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $errors = $input->formValidator()->errors();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('100', $errors[0]);
        $this->assertStringContainsString('multiple', $errors[0]);
    }

    // --- disabled ---

    public function test_form_value_returns_null_when_disabled(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '42']);
        $input = new NumberInput('my_field', 'My field');
        $form->addChild($input);
        $input->setFormDisabled(true);
        $this->assertNull($input->formValue());
    }

    public function test_disabled_attribute_renders_when_disabled(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $input->setFormDisabled(true);
        $this->assertStringContainsString('disabled', (string) $input);
    }

    // --- placeholder ---

    public function test_placeholder_renders_when_set(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $input->setPlaceholder('Enter a number');
        $this->assertStringContainsString('placeholder="Enter a number"', (string) $input);
    }

    public function test_placeholder_removed_when_set_to_null(): void
    {
        $input = new NumberInput('my_field', 'My field');
        $input->setPlaceholder('Enter a number');
        $input->setPlaceholder(null);
        $this->assertStringNotContainsString('placeholder', (string) $input);
    }

}
