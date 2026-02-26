<?php

namespace Joby\Smol\Form\Inputs;

use Joby\Smol\Form\FormTestCase;

class PasswordInputTest extends FormTestCase
{

    public function test_renders_as_password_input(): void
    {
        $input = new PasswordInput('password', 'Password');
        $this->assertStringContainsString('type="password"', (string) $input);
    }

    public function test_has_correct_name(): void
    {
        $input = new PasswordInput('password', 'Password');
        $this->assertStringContainsString('name="password"', (string) $input);
    }

    public function test_has_smol_input_class(): void
    {
        $input = new PasswordInput('password', 'Password');
        $this->assertStringContainsString('smol-password', (string) $input);
    }

    public function test_label_is_set(): void
    {
        $input = new PasswordInput('password', 'My Password');
        $this->assertEquals('My Password', $input->formLabel());
    }

    public function test_input_value_returns_null_without_submission(): void
    {
        $input = new PasswordInput('password', 'Password');
        $this->assertNull($input->formValue());
    }

    public function test_input_value_returns_submitted_value(): void
    {
        $form = $this->make_submitted_form('test_form', ['password' => 'hunter2']);
        $input = new PasswordInput('password', 'Password');
        $form->addChild($input);
        $this->assertEquals('hunter2', $input->formValue());
    }

    public function test_submitted_value_does_not_render_in_output(): void
    {
        $form = $this->make_submitted_form('test_form', ['password' => 'hunter2']);
        $input = new PasswordInput('password', 'Password');
        $form->addChild($input);
        $this->assertStringNotContainsString('value="hunter2"', (string) $input);
    }

    public function test_input_value_returns_null_when_disabled(): void
    {
        $form = $this->make_submitted_form('test_form', ['password' => 'hunter2']);
        $input = new PasswordInput('password', 'Password');
        $form->addChild($input);
        $input->setFormDisabled(true);
        $this->assertNull($input->formValue());
    }

}
