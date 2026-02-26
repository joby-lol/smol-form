<?php

namespace Joby\Smol\Form\Inputs;

use Joby\Smol\Form\FormTestCase;

class TelInputTest extends FormTestCase
{

    // --- rendering ---

    public function test_renders_as_tel_input(): void
    {
        $input = new TelInput('my_field', 'My field');
        $this->assertStringContainsString('type="tel"', (string) $input);
    }

    public function test_has_correct_name(): void
    {
        $input = new TelInput('my_field', 'My field');
        $this->assertStringContainsString('name="my_field"', (string) $input);
    }

    public function test_has_smol_tel_class(): void
    {
        $input = new TelInput('my_field', 'My field');
        $this->assertStringContainsString('smol-tel', (string) $input);
    }

    public function test_label_is_set(): void
    {
        $input = new TelInput('my_field', 'My Label');
        $this->assertEquals('My Label', $input->formLabel());
    }

    // --- formValue ---

    public function test_form_value_returns_null_without_form(): void
    {
        $input = new TelInput('my_field', 'My field');
        $this->assertNull($input->formValue());
    }

    public function test_form_value_returns_default_without_form(): void
    {
        $input = new TelInput('my_field', 'My field');
        $input->setFormDefault('+1 555 000 0000');
        $this->assertEquals('+1 555 000 0000', $input->formValue());
    }

    public function test_form_value_returns_submitted_value(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '+1 555 000 0000']);
        $input = new TelInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertEquals('+1 555 000 0000', $input->formValue());
    }

    public function test_form_value_returns_null_on_unsubmitted_form(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $input = new TelInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertNull($input->formValue());
    }

    // --- validation ---

    public function test_any_non_empty_value_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '+1 555 000 0000']);
        $input = new TelInput('my_field', 'My field');
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_empty_non_required_field_passes_validation(): void
    {
        $form = $this->make_submitted_form('test_form', []);
        $input = new TelInput('my_field', 'My field');
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertSame([], $input->formValidator()->errors());
    }

    public function test_empty_required_field_fails_validation(): void
    {
        $form = $this->make_submitted_form('test_form', []);
        $input = new TelInput('my_field', 'My field');
        $input->formValidator()->setRequired(true);
        $form->addChild($input);
        $input->formValidator()->runValidation();
        $this->assertNotEmpty($input->formValidator()->errors());
    }

    // --- disabled ---

    public function test_form_value_returns_null_when_disabled(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '+1 555 000 0000']);
        $input = new TelInput('my_field', 'My field');
        $form->addChild($input);
        $input->setFormDisabled(true);
        $this->assertNull($input->formValue());
    }

    public function test_disabled_attribute_renders_when_disabled(): void
    {
        $input = new TelInput('my_field', 'My field');
        $input->setFormDisabled(true);
        $this->assertStringContainsString('disabled', (string) $input);
    }

    // --- placeholder ---

    public function test_placeholder_renders_when_set(): void
    {
        $input = new TelInput('my_field', 'My field');
        $input->setPlaceholder('+1 555 000 0000');
        $this->assertStringContainsString('placeholder="+1 555 000 0000"', (string) $input);
    }

    public function test_set_placeholder_is_fluent(): void
    {
        $input = new TelInput('my_field', 'My field');
        $this->assertSame($input, $input->setPlaceholder('+1 555 000 0000'));
    }

    public function test_placeholder_removed_when_set_to_null(): void
    {
        $input = new TelInput('my_field', 'My field');
        $input->setPlaceholder('+1 555 000 0000');
        $input->setPlaceholder(null);
        $this->assertStringNotContainsString('placeholder', (string) $input);
    }

}
