<?php

namespace Joby\Smol\Form\Inputs;

use DateInterval;
use DateTimeImmutable;
use Joby\Smol\Form\FormTestCase;

class DateTimeInputTest extends FormTestCase
{

    // --- rendering ---

    public function test_renders_as_datetime_local_input(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $this->assertStringContainsString('type="datetime-local"', (string) $input);
    }

    public function test_has_correct_name(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $this->assertStringContainsString('name="my_field"', (string) $input);
    }

    public function test_has_smol_datetime_class(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $this->assertStringContainsString('smol-datetime', (string) $input);
    }

    public function test_label_is_set(): void
    {
        $input = new DateTimeInput('my_field', 'My Label');
        $this->assertEquals('My Label', $input->formLabel());
    }

    // --- formValue ---

    public function test_form_value_returns_null_without_form(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $this->assertNull($input->formValue());
    }

    public function test_form_value_returns_default_without_form(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $default = new DateTimeImmutable('2025-06-15 10:30:00');
        $input->setFormDefault($default);
        $this->assertEquals('2025-06-15 10:30:00', $input->formValue()->format('Y-m-d H:i:s'));
    }

    public function test_form_value_returns_submitted_value(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15T10:30']);
        $input = new DateTimeInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertEquals('2025-06-15 10:30:00', $input->formValue()->format('Y-m-d H:i:s'));
    }

    public function test_form_value_accepts_seconds_in_submitted_value(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15T10:30:45']);
        $input = new DateTimeInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertEquals('2025-06-15 10:30:45', $input->formValue()->format('Y-m-d H:i:s'));
    }

    public function test_form_value_returns_null_on_unsubmitted_form(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $input = new DateTimeInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertNull($input->formValue());
    }

    public function test_form_value_is_datetimeimmutable(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15T10:30']);
        $input = new DateTimeInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertInstanceOf(DateTimeImmutable::class, $input->formValue());
    }

    public function test_form_value_returns_null_for_invalid_datetime_string(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'not-a-datetime']);
        $input = new DateTimeInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertNull($input->formValue());
    }

    // --- min/max/step attributes ---

    public function test_min_attribute_renders_when_set(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeMin(new DateTimeImmutable('2025-01-01 00:00:00'));
        $this->assertStringContainsString('min="2025-01-01T00:00:00"', (string) $input);
    }

    public function test_min_attribute_absent_when_not_set(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $this->assertStringNotContainsString('min=', (string) $input);
    }

    public function test_min_attribute_removed_when_set_to_null(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeMin(new DateTimeImmutable('2025-01-01 00:00:00'));
        $input->setDateTimeMin(null);
        $this->assertStringNotContainsString('min=', (string) $input);
    }

    public function test_max_attribute_renders_when_set(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeMax(new DateTimeImmutable('2025-12-31 23:59:59'));
        $this->assertStringContainsString('max="2025-12-31T23:59:59"', (string) $input);
    }

    public function test_max_attribute_absent_when_not_set(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $this->assertStringNotContainsString('max=', (string) $input);
    }

    public function test_step_attribute_renders_when_set_as_int(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeStep(3600);
        $this->assertStringContainsString('step="3600"', (string) $input);
    }

    public function test_step_attribute_renders_when_set_as_dateinterval(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeStep(new DateInterval('PT1H'));
        $this->assertStringContainsString('step="3600"', (string) $input);
    }

    public function test_step_attribute_absent_when_not_set(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $this->assertStringNotContainsString('step=', (string) $input);
    }

    // --- fluency ---

    public function test_set_datetime_min_is_fluent(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $this->assertSame($input, $input->setDateTimeMin(new DateTimeImmutable('2025-01-01')));
    }

    public function test_set_datetime_max_is_fluent(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $this->assertSame($input, $input->setDateTimeMax(new DateTimeImmutable('2025-12-31')));
    }

    public function test_set_datetime_step_is_fluent(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $this->assertSame($input, $input->setDateTimeStep(3600));
    }

    // --- DateInterval conversion ---

    public function test_dateinterval_hours_converts_correctly(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeStep(new DateInterval('PT2H'));
        $this->assertStringContainsString('step="7200"', (string) $input);
    }

    public function test_dateinterval_minutes_converts_correctly(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeStep(new DateInterval('PT30M'));
        $this->assertStringContainsString('step="1800"', (string) $input);
    }

    public function test_dateinterval_days_converts_correctly(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeStep(new DateInterval('P1D'));
        $this->assertStringContainsString('step="86400"', (string) $input);
    }

    // --- validation: valid values ---

    public function test_valid_datetime_in_range_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15T10:30']);
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeMin(new DateTimeImmutable('2025-01-01 00:00:00'));
        $input->setDateTimeMax(new DateTimeImmutable('2025-12-31 23:59:59'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_empty_non_required_field_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', []);
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeMin(new DateTimeImmutable('2025-01-01 00:00:00'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_empty_required_field_fails_validation(): void
    {
        $form = $this->make_submitted_form('test_form', []);
        $input = new DateTimeInput('my_field', 'My field');
        $input->formValidator()->setRequired(true);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    // --- validation: min ---

    public function test_datetime_before_min_fails_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2024-12-31T23:59']);
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeMin(new DateTimeImmutable('2025-01-01 00:00:00'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    public function test_datetime_at_min_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-01-01T00:00']);
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeMin(new DateTimeImmutable('2025-01-01 00:00:00'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_min_error_message_mentions_datetime(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2024-01-01T00:00']);
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeMin(new DateTimeImmutable('2025-01-01 00:00:00'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertStringContainsString('2025-01-01', $input->formValidator()->errors()[0]);
    }

    // --- validation: max ---

    public function test_datetime_after_max_fails_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2026-01-01T00:00']);
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeMax(new DateTimeImmutable('2025-12-31 23:59:59'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    public function test_datetime_at_max_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-12-31T23:59']);
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeMax(new DateTimeImmutable('2025-12-31 23:59:59'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_max_error_message_mentions_datetime(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2026-06-01T00:00']);
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeMax(new DateTimeImmutable('2025-12-31 23:59:59'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertStringContainsString('2025-12-31', $input->formValidator()->errors()[0]);
    }

    // --- validation: step ---

    public function test_step_ignored_without_default(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15T10:37']);
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeStep(3600);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_value_on_step_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15T12:00']);
        $input = new DateTimeInput('my_field', 'My field');
        $input->setFormDefault(new DateTimeImmutable('2025-06-15 10:00:00'));
        $input->setDateTimeStep(3600);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_value_off_step_fails_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15T10:37']);
        $input = new DateTimeInput('my_field', 'My field');
        $input->setFormDefault(new DateTimeImmutable('2025-06-15 10:00:00'));
        $input->setDateTimeStep(3600);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    public function test_step_error_message_mentions_step_and_base(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15T10:37']);
        $input = new DateTimeInput('my_field', 'My field');
        $input->setFormDefault(new DateTimeImmutable('2025-06-15 10:00:00'));
        $input->setDateTimeStep(3600);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $errors = $input->formValidator()->errors();
        $this->assertStringContainsString('3600', $errors[0]);
        $this->assertStringContainsString('2025-06-15', $errors[0]);
    }

    public function test_dateinterval_step_validates_correctly(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15T10:37']);
        $input = new DateTimeInput('my_field', 'My field');
        $input->setFormDefault(new DateTimeImmutable('2025-06-15 10:00:00'));
        $input->setDateTimeStep(new DateInterval('PT1H'));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    // --- validation: multiple errors ---

    public function test_multiple_errors_combined_in_single_message(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15T10:37']);
        $input = new DateTimeInput('my_field', 'My field');
        $input->setDateTimeMax(new DateTimeImmutable('2025-01-01 00:00:00'));
        $input->setFormDefault(new DateTimeImmutable('2025-06-15 10:00:00'));
        $input->setDateTimeStep(3600);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $errors = $input->formValidator()->errors();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('2025-01-01', $errors[0]);
        $this->assertStringContainsString('3600', $errors[0]);
    }

    // --- disabled ---

    public function test_form_value_returns_null_when_disabled(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '2025-06-15T10:30']);
        $input = new DateTimeInput('my_field', 'My field');
        $form->addChild($input);
        $input->setFormDisabled(true);
        $this->assertNull($input->formValue());
    }

    public function test_disabled_attribute_renders_when_disabled(): void
    {
        $input = new DateTimeInput('my_field', 'My field');
        $input->setFormDisabled(true);
        $this->assertStringContainsString('disabled', (string) $input);
    }

}
