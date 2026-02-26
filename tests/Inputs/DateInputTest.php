<?php

namespace Joby\Smol\Form\Inputs;

use DateTimeImmutable;
use Joby\Smol\Form\FormTestCase;

class DateInputTest extends FormTestCase
{

    // --- rendering ---

    public function test_renders_as_date_input(): void
    {
        $input = new DateInput('my_field', 'My field');
        $this->assertStringContainsString('type="date"', (string) $input);
    }

    public function test_has_correct_name(): void
    {
        $input = new DateInput('my_field', 'My field');
        $this->assertStringContainsString('name="my_field"', (string) $input);
    }

    public function test_has_smol_date_class(): void
    {
        $input = new DateInput('my_field', 'My field');
        $this->assertStringContainsString('smol-date', (string) $input);
    }

    public function test_label_is_set(): void
    {
        $input = new DateInput('my_field', 'My Label');
        $this->assertEquals('My Label', $input->formLabel());
    }

    // --- formValue ---

    public function test_form_value_returns_null_without_form(): void
    {
        $input = new DateInput('my_field', 'My field');
        $this->assertNull($input->formValue());
    }

    public function test_form_value_returns_default_without_form(): void
    {
        $input = new DateInput('my_field', 'My field');
        $default = new DateTimeImmutable('2025-06-15');
        $input->setFormDefault($default);
        $this->assertEquals('2025-06-15', $input->formValue()->format('Y-m-d'));
    }

    public function test_form_value_returns_submitted_value(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15']);
        $input = new DateInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertEquals('2025-06-15', $input->formValue()->format('Y-m-d'));
    }

    public function test_form_value_returns_null_on_unsubmitted_form(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $input = new DateInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertNull($input->formValue());
    }

    public function test_form_value_is_datetimeimmutable(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15']);
        $input = new DateInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertInstanceOf(DateTimeImmutable::class, $input->formValue());
    }

    public function test_form_value_returns_null_for_invalid_date_string(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'not-a-date']);
        $input = new DateInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertNull($input->formValue());
    }

    public function test_submitted_value_time_is_zeroed(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15']);
        $input = new DateInput('my_field', 'My field');
        $form->addChild($input);
        $value = $input->formValue();
        $this->assertEquals('00:00:00', $value->format('H:i:s'));
    }

    // --- min/max attributes ---

    public function test_min_attribute_renders_when_set(): void
    {
        $input = new DateInput('my_field', 'My field');
        $input->setDateMin(new DateTimeImmutable('2025-01-01'));
        $this->assertStringContainsString('min="2025-01-01"', (string) $input);
    }

    public function test_min_attribute_absent_when_not_set(): void
    {
        $input = new DateInput('my_field', 'My field');
        $this->assertStringNotContainsString('min=', (string) $input);
    }

    public function test_min_attribute_removed_when_set_to_null(): void
    {
        $input = new DateInput('my_field', 'My field');
        $input->setDateMin(new DateTimeImmutable('2025-01-01'));
        $input->setDateMin(null);
        $this->assertStringNotContainsString('min=', (string) $input);
    }

    public function test_max_attribute_renders_when_set(): void
    {
        $input = new DateInput('my_field', 'My field');
        $input->setDateMax(new DateTimeImmutable('2025-12-31'));
        $this->assertStringContainsString('max="2025-12-31"', (string) $input);
    }

    public function test_max_attribute_absent_when_not_set(): void
    {
        $input = new DateInput('my_field', 'My field');
        $this->assertStringNotContainsString('max=', (string) $input);
    }

    // --- fluency ---

    public function test_set_date_min_is_fluent(): void
    {
        $input = new DateInput('my_field', 'My field');
        $this->assertSame($input, $input->setDateMin(new DateTimeImmutable('2025-01-01')));
    }

    public function test_set_date_max_is_fluent(): void
    {
        $input = new DateInput('my_field', 'My field');
        $this->assertSame($input, $input->setDateMax(new DateTimeImmutable('2025-12-31')));
    }

    // --- validation: valid values ---

    public function test_valid_date_in_range_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15']);
        $input = new DateInput('my_field', 'My field');
        $input->setDateMin(new DateTimeImmutable('2025-01-01'));
        $input->setDateMax(new DateTimeImmutable('2025-12-31'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_empty_non_required_field_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', []);
        $input = new DateInput('my_field', 'My field');
        $input->setDateMin(new DateTimeImmutable('2025-01-01'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_empty_required_field_fails_validation(): void
    {
        $form = $this->make_submitted_form('test_form', []);
        $input = new DateInput('my_field', 'My field');
        $input->formValidator()->setRequired(true);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    // --- validation: min ---

    public function test_date_before_min_fails_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2024-12-31']);
        $input = new DateInput('my_field', 'My field');
        $input->setDateMin(new DateTimeImmutable('2025-01-01'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    public function test_date_at_min_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-01-01']);
        $input = new DateInput('my_field', 'My field');
        $input->setDateMin(new DateTimeImmutable('2025-01-01'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_min_error_message_mentions_date(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2024-01-01']);
        $input = new DateInput('my_field', 'My field');
        $input->setDateMin(new DateTimeImmutable('2025-01-01'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertStringContainsString('2025-01-01', $input->formValidator()->errors()[0]);
    }

    // --- validation: max ---

    public function test_date_after_max_fails_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2026-01-01']);
        $input = new DateInput('my_field', 'My field');
        $input->setDateMax(new DateTimeImmutable('2025-12-31'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    public function test_date_at_max_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-12-31']);
        $input = new DateInput('my_field', 'My field');
        $input->setDateMax(new DateTimeImmutable('2025-12-31'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_max_error_message_mentions_date(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2026-06-01']);
        $input = new DateInput('my_field', 'My field');
        $input->setDateMax(new DateTimeImmutable('2025-12-31'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertStringContainsString('2025-12-31', $input->formValidator()->errors()[0]);
    }

    // --- validation: multiple errors ---

    public function test_multiple_errors_combined_in_single_message(): void
    {
        // This shouldn't normally happen (before min AND after max simultaneously),
        // but tests that the message combining works if constraints are inverted
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15']);
        $input = new DateInput('my_field', 'My field');
        $input->setDateMin(new DateTimeImmutable('2025-12-01'));
        $input->setDateMax(new DateTimeImmutable('2025-01-01'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $errors = $input->formValidator()->errors();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('2025-12-01', $errors[0]);
        $this->assertStringContainsString('2025-01-01', $errors[0]);
    }

    // --- disabled ---

    public function test_form_value_returns_null_when_disabled(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15']);
        $input = new DateInput('my_field', 'My field');
        $form->addChild($input);
        $input->setFormDisabled(true);
        $this->assertNull($input->formValue());
    }

    public function test_disabled_attribute_renders_when_disabled(): void
    {
        $input = new DateInput('my_field', 'My field');
        $input->setFormDisabled(true);
        $this->assertStringContainsString('disabled', (string) $input);
    }

}
