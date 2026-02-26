<?php

namespace Joby\Smol\Form\Inputs;

use Joby\Smol\Form\FormTestCase;

class TextareaInputTest extends FormTestCase
{

    // --- rendering ---

    public function test_renders_as_textarea(): void
    {
        $input = new TextareaInput('my_field', 'My field');
        $this->assertStringContainsString('<textarea', (string) $input);
    }

    public function test_has_correct_name(): void
    {
        $input = new TextareaInput('my_field', 'My field');
        $this->assertStringContainsString('name="my_field"', (string) $input);
    }

    public function test_has_smol_textarea_class(): void
    {
        $input = new TextareaInput('my_field', 'My field');
        $this->assertStringContainsString('smol-textarea', (string) $input);
    }

    public function test_label_is_set(): void
    {
        $input = new TextareaInput('my_field', 'My Label');
        $this->assertEquals('My Label', $input->formLabel());
    }

    public function test_does_not_have_type_attribute(): void
    {
        $input = new TextareaInput('my_field', 'My field');
        $this->assertStringNotContainsString('type=', (string) $input);
    }

    // --- formValue without a form ---

    public function test_form_value_returns_null_without_form(): void
    {
        $input = new TextareaInput('my_field', 'My field');
        $this->assertNull($input->formValue());
    }

    public function test_form_value_returns_default_without_form(): void
    {
        $input = new TextareaInput('my_field', 'My field');
        $input->setFormDefault('default text');
        $this->assertEquals('default text', $input->formValue());
    }

    // --- formValue with unsubmitted form ---

    public function test_form_value_returns_null_on_unsubmitted_form(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $input = new TextareaInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertNull($input->formValue());
    }

    public function test_form_value_returns_default_on_unsubmitted_form(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $input = new TextareaInput('my_field', 'My field');
        $input->setFormDefault('default text');
        $form->addChild($input);
        $this->assertEquals('default text', $input->formValue());
    }

    // --- formValue with submitted form ---

    public function test_form_value_returns_submitted_value(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'submitted text']);
        $input = new TextareaInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertEquals('submitted text', $input->formValue());
    }

    public function test_form_value_returns_null_when_not_submitted(): void
    {
        $form = $this->make_unsubmitted_form('test_form', ['my_field' => '']);
        $input = new TextareaInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertNull($input->formValue());
    }

    // --- content rendering ---

    public function test_submitted_value_renders_as_content(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'submitted text']);
        $input = new TextareaInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertStringContainsString('submitted text', (string) $input);
    }

    public function test_default_value_renders_as_content_without_form(): void
    {
        $input = new TextareaInput('my_field', 'My field');
        $input->setFormDefault('default text');
        $this->assertStringContainsString('default text', (string) $input);
    }

    public function test_empty_content_when_no_value(): void
    {
        $input = new TextareaInput('my_field', 'My field');
        $output = (string) $input;
        $this->assertMatchesRegularExpression('/<textarea[^>]*><\/textarea>/', $output);
    }

    public function test_html_special_chars_are_escaped_in_content(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => '<script>alert("xss")</script>']);
        $input = new TextareaInput('my_field', 'My field');
        $form->addChild($input);
        $output = (string) $input;
        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }

    public function test_multiline_content_is_preserved(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => "line one\nline two"]);
        $input = new TextareaInput('my_field', 'My field');
        $form->addChild($input);
        $this->assertStringContainsString("line one\nline two", (string) $input);
    }

    // --- disabled ---

    public function test_form_value_returns_null_when_disabled(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_field' => 'submitted text']);
        $input = new TextareaInput('my_field', 'My field');
        $form->addChild($input);
        $input->setFormDisabled(true);
        $this->assertNull($input->formValue());
    }

    public function test_disabled_attribute_renders_when_disabled(): void
    {
        $input = new TextareaInput('my_field', 'My field');
        $input->setFormDisabled(true);
        $this->assertStringContainsString('disabled', (string) $input);
    }

    public function test_disabled_attribute_absent_when_not_disabled(): void
    {
        $input = new TextareaInput('my_field', 'My field');
        $this->assertStringNotContainsString('disabled', (string) $input);
    }

    // --- required ---

    public function test_required_attribute_renders_when_required(): void
    {
        $input = new TextareaInput('my_field', 'My field');
        $input->formValidator()->setRequired(true);
        $this->assertStringContainsString('required', (string) $input);
    }

    public function test_required_attribute_absent_when_not_required(): void
    {
        $input = new TextareaInput('my_field', 'My field');
        $this->assertStringNotContainsString('required', (string) $input);
    }

}
