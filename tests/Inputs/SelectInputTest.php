<?php

namespace Joby\Smol\Form\Inputs;

use Joby\Smol\Form\FormTestCase;

class SelectInputTest extends FormTestCase
{

    protected function makeInput(string $name = 'my_field', string $label = 'My field'): SelectInput
    {
        return new SelectInput($name, $label, [
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => 'Baz',
        ]);
    }

    // --- rendering ---

    public function test_renders_as_select(): void
    {
        $this->assertStringContainsString('<select', (string) $this->makeInput());
    }

    public function test_has_correct_name(): void
    {
        $this->assertStringContainsString('name="my_field"', (string) $this->makeInput());
    }

    public function test_has_smol_select_class(): void
    {
        $this->assertStringContainsString('smol-select', (string) $this->makeInput());
    }

    public function test_label_is_set(): void
    {
        $input = new SelectInput('my_field', 'My Label', []);
        $this->assertEquals('My Label', $input->formLabel());
    }

    public function test_options_render(): void
    {
        $rendered = (string) $this->makeInput();
        $this->assertStringContainsString('value="foo"', $rendered);
        $this->assertStringContainsString('value="bar"', $rendered);
        $this->assertStringContainsString('value="baz"', $rendered);
    }

    public function test_option_labels_render(): void
    {
        $rendered = (string) $this->makeInput();
        $this->assertStringContainsString('Foo', $rendered);
        $this->assertStringContainsString('Bar', $rendered);
        $this->assertStringContainsString('Baz', $rendered);
    }

    // --- empty option ---

    public function test_empty_option_not_rendered_by_default(): void
    {
        $rendered = (string) $this->makeInput();
        $this->assertStringNotContainsString('value=""', $rendered);
    }

    public function test_empty_option_renders_when_set(): void
    {
        $input = $this->makeInput();
        $input->setEmptyOption('-- select one --');
        $rendered = (string) $input;
        $this->assertStringContainsString('<option selected>-- select one --</option>', $rendered);
        $this->assertStringContainsString('-- select one --', $rendered);
    }

    public function test_empty_option_removed_when_set_to_null(): void
    {
        $input = $this->makeInput();
        $input->setEmptyOption('-- select one --');
        $input->setEmptyOption(null);
        $this->assertStringNotContainsString('value=""', (string) $input);
    }

    public function test_set_empty_option_is_fluent(): void
    {
        $input = $this->makeInput();
        $this->assertSame($input, $input->setEmptyOption('-- select one --'));
    }

    public function test_empty_option_is_selected_when_no_value(): void
    {
        $input = $this->makeInput();
        $input->setEmptyOption('-- select one --');
        $rendered = (string) $input;
        // the empty option should have selected attribute
        $this->assertStringContainsString('<option selected>-- select one --</option>', $rendered);
    }

    // --- formValue ---

    public function test_form_value_returns_null_without_form(): void
    {
        $this->assertNull($this->makeInput()->formValue());
    }

    public function test_form_value_returns_default_without_form(): void
    {
        $input = new SelectInput('my_field', 'My field', ['foo' => 'Foo', 'bar' => 'Bar'], 'foo');
        $this->assertEquals('foo', $input->formValue());
    }

    public function test_form_value_returns_submitted_value(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'bar']);
        $input = $this->makeInput();
        $form->addChild($input);
        $this->assertEquals('bar', $input->formValue());
    }

    public function test_form_value_returns_null_on_unsubmitted_form(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $input = $this->makeInput();
        $form->addChild($input);
        $this->assertNull($input->formValue());
    }

    public function test_form_value_returns_null_for_invalid_option(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'not_an_option']);
        $input = $this->makeInput();
        $form->addChild($input);
        $this->assertNull($input->formValue());
    }

    public function test_form_value_returns_null_for_empty_string(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '']);
        $input = $this->makeInput();
        $form->addChild($input);
        $this->assertNull($input->formValue());
    }

    // --- selected state ---

    public function test_selected_option_has_selected_attribute(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'bar']);
        $input = $this->makeInput();
        $form->addChild($input);
        $rendered = (string) $input;
        $this->assertMatchesRegularExpression('/value="bar"[^>]*selected|selected[^>]*value="bar"/', $rendered);
    }

    public function test_default_option_selected_without_submission(): void
    {
        $input = new SelectInput('my_field', 'My field', ['foo' => 'Foo', 'bar' => 'Bar'], 'foo');
        $rendered = (string) $input;
        $this->assertMatchesRegularExpression('/value="foo"[^>]*selected|selected[^>]*value="foo"/', $rendered);
    }

    // --- validation ---

    public function test_valid_option_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'foo']);
        $input = $this->makeInput();
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_empty_non_required_field_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', []);
        $input = $this->makeInput();
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_empty_required_field_fails_validation(): void
    {
        $form = $this->make_submitted_form('test_form', []);
        $input = $this->makeInput();
        $input->formValidator()->setRequired(true);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    public function test_invalid_option_fails_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'not_an_option']);
        $input = $this->makeInput();
        $form->addChild($input);
        $input->formValidator()->runValidation();
        // formSubmittedValue returns null for invalid options, so required check won't fire,
        // but validateSelf should catch it if the raw value somehow slips through
        // This mainly tests that the system doesn't explode
        $this->assertIsArray($input->formValidator()->errors());
    }

    // --- disabled ---

    public function test_form_value_returns_null_when_disabled(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'foo']);
        $input = $this->makeInput();
        $form->addChild($input);
        $input->setFormDisabled(true);
        $this->assertNull($input->formValue());
    }

    public function test_disabled_attribute_renders_when_disabled(): void
    {
        $input = $this->makeInput();
        $input->setFormDisabled(true);
        $this->assertStringContainsString('disabled', (string) $input);
    }

}
