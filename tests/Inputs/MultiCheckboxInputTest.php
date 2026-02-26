<?php

namespace Joby\Smol\Form\Inputs;

use Joby\Smol\Form\FormTestCase;

class MultiCheckboxInputTest extends FormTestCase
{

    protected function options(): array
    {
        return [
            'red'   => 'Red',
            'green' => 'Green',
            'blue'  => 'Blue',
        ];
    }

    protected function checkboxName(string $field_name, string $key): string
    {
        return 'opt_' . md5($field_name . $key);
    }

    // --- rendering ---

    public function test_has_smol_multicheckbox_class(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $this->assertStringContainsString('smol-multicheckbox', (string) $input);
    }

    public function test_label_is_set(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My Label', $this->options());
        $this->assertEquals('My Label', $input->formLabel());
    }

    public function test_option_labels_appear_in_output(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $output = (string) $input;
        $this->assertStringContainsString('Red', $output);
        $this->assertStringContainsString('Green', $output);
        $this->assertStringContainsString('Blue', $output);
    }

    public function test_renders_correct_number_of_checkboxes(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $output = (string) $input;
        $this->assertEquals(3, substr_count($output, 'type="checkbox"'));
    }

    public function test_renders_with_empty_options(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', []);
        $this->assertStringNotContainsString('type="checkbox"', (string) $input);
    }

    // --- formValue without a form ---

    public function test_form_value_returns_null_without_form(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $this->assertNull($input->formValue());
    }

    public function test_form_value_returns_default_without_form(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options(), ['red', 'blue']);
        $this->assertEquals(['red', 'blue'], $input->formValue());
    }

    // --- formValue with unsubmitted form ---

    public function test_form_value_returns_null_on_unsubmitted_form(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $form->addChild($input);
        $this->assertNull($input->formValue());
    }

    public function test_form_value_returns_default_on_unsubmitted_form(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options(), ['green']);
        $form->addChild($input);
        $this->assertEquals(['green'], $input->formValue());
    }

    // --- formValue with submitted form ---

    public function test_form_value_returns_selected_keys(): void
    {
        $red_name = $this->checkboxName('my_field', 'red');
        $blue_name = $this->checkboxName('my_field', 'blue');
        $form = $this->make_submitted_form('test_form', [
            $red_name  => 'on',
            $blue_name => 'on',
        ]);
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $form->addChild($input);
        $value = $input->formValue();
        $this->assertIsArray($value);
        $this->assertContains('red', $value);
        $this->assertContains('blue', $value);
        $this->assertNotContains('green', $value);
    }

    public function test_form_value_returns_empty_array_when_nothing_checked(): void
    {
        $form = $this->make_submitted_form('test_form', []);
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $form->addChild($input);
        $this->assertIsArray($input->formValue());
        $this->assertEmpty($input->formValue());
    }

    public function test_form_value_returns_all_keys_when_all_checked(): void
    {
        $post = array_fill_keys(
            array_map(fn($k) => $this->checkboxName('my_field', $k), array_keys($this->options())),
            'on',
        );
        $form = $this->make_submitted_form('test_form', $post);
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $form->addChild($input);
        $value = $input->formValue();
        $this->assertIsArray($value);
        $this->assertCount(3, $value);
    }

    // --- checked state renders correctly ---

    public function test_checked_options_render_as_checked_after_submission(): void
    {
        $red_name = $this->checkboxName('my_field', 'red');
        $form = $this->make_submitted_form('test_form', [$red_name => 'on']);
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $form->addChild($input);
        // There should be exactly one checked checkbox
        $output = (string) $input;
        $this->assertStringContainsString('checked', (string) $output);
    }

    // --- key isolation ---

    public function test_checkbox_names_are_unique_across_different_field_names(): void
    {
        $this->assertNotEquals(
            $this->checkboxName('field_a', 'red'),
            $this->checkboxName('field_b', 'red'),
        );
    }

    public function test_checkbox_names_are_unique_across_different_keys(): void
    {
        $this->assertNotEquals(
            $this->checkboxName('my_field', 'red'),
            $this->checkboxName('my_field', 'blue'),
        );
    }

    protected function submittedKeys(array $keys): array
    {
        return array_fill_keys(
            array_map(fn($k) => $this->checkboxName('my_field', $k), $keys),
            'on',
        );
    }

    // --- fluency ---

    public function test_set_form_min_selections_is_fluent(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $this->assertSame($input, $input->setFormMinSelections(1));
    }

    public function test_set_form_max_selections_is_fluent(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $this->assertSame($input, $input->setFormMaxSelections(2));
    }

    // --- min ---

    public function test_below_min_fails_validation(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $input->setFormMinSelections(2);
        $form = $this->make_submitted_form('test_form', $this->submittedKeys(['red']));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    public function test_at_min_passes_validation(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $input->setFormMinSelections(2);
        $form = $this->make_submitted_form('test_form', $this->submittedKeys(['red', 'green']));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_above_min_passes_validation(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $input->setFormMinSelections(1);
        $form = $this->make_submitted_form('test_form', $this->submittedKeys(['red', 'green']));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_min_error_message_mentions_count(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $input->setFormMinSelections(2);
        $form = $this->make_submitted_form('test_form', $this->submittedKeys(['red']));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertStringContainsString('2', $input->formValidator()->errors()[0]);
    }

    // --- max ---

    public function test_above_max_fails_validation(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $input->setFormMaxSelections(1);
        $form = $this->make_submitted_form('test_form', $this->submittedKeys(['red', 'green']));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    public function test_at_max_passes_validation(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $input->setFormMaxSelections(2);
        $form = $this->make_submitted_form('test_form', $this->submittedKeys(['red', 'green']));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_below_max_passes_validation(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $input->setFormMaxSelections(3);
        $form = $this->make_submitted_form('test_form', $this->submittedKeys(['red']));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_max_error_message_mentions_count(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $input->setFormMaxSelections(1);
        $form = $this->make_submitted_form('test_form', $this->submittedKeys(['red', 'green']));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertStringContainsString('1', $input->formValidator()->errors()[0]);
    }

    // --- none selected ---

    public function test_no_selections_passes_when_no_min(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $form = $this->make_submitted_form('test_form', []);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_no_selections_fails_when_min_set(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $input->setFormMinSelections(1);
        $form = $this->make_submitted_form('test_form', []);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        var_dump($input->formValidator()->errors());
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    // --- multiple errors ---

    public function test_min_max_multiple_errors_combined_in_single_message(): void
    {
        // inverted min/max to force both errors simultaneously
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $input->setFormMinSelections(3);
        $input->setFormMaxSelections(1);
        $form = $this->make_submitted_form('test_form', $this->submittedKeys(['red', 'green']));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $errors = $input->formValidator()->errors();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('3', $errors[0]);
        $this->assertStringContainsString('1', $errors[0]);
    }

    // --- validation: required interaction ---

    public function test_empty_array_on_required_field_fails_validation(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $input->formValidator()->setRequired(true);
        $form = $this->make_submitted_form('test_form', []);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    public function test_empty_array_on_non_required_field_passes_validation(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $form = $this->make_submitted_form('test_form', []);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_non_empty_array_on_required_field_passes_validation(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $input->formValidator()->setRequired(true);
        $form = $this->make_submitted_form('test_form', $this->submittedKeys(['red']));
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_min_of_zero_with_empty_array_passes_validation(): void
    {
        $input = new MultiCheckboxInput('my_field', 'My field', $this->options());
        $input->setFormMinSelections(0);
        $form = $this->make_submitted_form('test_form', []);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

}
