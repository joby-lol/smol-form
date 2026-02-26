<?php

namespace Joby\Smol\Form\Inputs\Helpers;

use Joby\Smol\Form\FormTestCase;

class HelperCheckboxTest extends FormTestCase
{

    public function test_renders_as_checkbox(): void
    {
        $checkbox = new HelperCheckbox('my_checkbox');
        $this->assertStringContainsString('type="checkbox"', (string) $checkbox);
    }

    public function test_has_correct_name(): void
    {
        $checkbox = new HelperCheckbox('my_checkbox');
        $this->assertStringContainsString('name="my_checkbox"', (string) $checkbox);
    }

    public function test_form_attribute_set_with_parent_form(): void
    {
        $form = new \Joby\Smol\Form\Form('test_form');
        $form->addChild($checkbox = new HelperCheckbox('my_checkbox'));
        $this->assertStringContainsString('form="test_form"', (string) $checkbox);
    }

    public function test_form_attribute_absent_without_parent_form(): void
    {
        $checkbox = new HelperCheckbox('my_checkbox');
        $this->assertStringNotContainsString('form=', (string) $checkbox);
    }

    public function test_not_checked_by_default(): void
    {
        $checkbox = new HelperCheckbox('my_checkbox');
        $this->assertStringNotContainsString('checked', (string) $checkbox);
    }

    public function test_checked_when_default_is_true(): void
    {
        $checkbox = new HelperCheckbox('my_checkbox', true);
        $this->assertStringContainsString('checked', (string) $checkbox);
    }

    public function test_submitted_value_null_without_parent_form(): void
    {
        $checkbox = new HelperCheckbox('my_checkbox');
        $this->assertNull($checkbox->submittedValue());
    }

    public function test_submitted_value_null_when_form_not_attempted(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $form->addChild($checkbox = new HelperCheckbox('my_checkbox'));
        $this->assertNull($checkbox->submittedValue());
    }

    public function test_submitted_value_true_when_checked(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_checkbox' => 'on']);
        $form->addChild($checkbox = new HelperCheckbox('my_checkbox'));
        $this->assertTrue($checkbox->submittedValue());
    }

    public function test_submitted_value_false_when_unchecked(): void
    {
        $form = $this->make_submitted_form('test_form');
        $form->addChild($checkbox = new HelperCheckbox('my_checkbox'));
        $this->assertFalse($checkbox->submittedValue());
    }

    public function test_checked_when_submitted_checked(): void
    {
        $form = $this->make_submitted_form('test_form', ['my_checkbox' => 'on']);
        $form->addChild($checkbox = new HelperCheckbox('my_checkbox'));
        $this->assertStringContainsString('checked', (string) $checkbox);
    }

    public function test_not_checked_when_submitted_unchecked(): void
    {
        $form = $this->make_submitted_form('test_form');
        $form->addChild($checkbox = new HelperCheckbox('my_checkbox'));
        $this->assertStringNotContainsString('checked', (string) $checkbox);
    }

    public function test_submitted_state_overrides_true_default(): void
    {
        $form = $this->make_submitted_form('test_form');
        $form->addChild($checkbox = new HelperCheckbox('my_checkbox', true));
        $this->assertStringNotContainsString('checked', (string) $checkbox);
        $this->assertFalse($checkbox->submittedValue());
    }

    public function test_set_default_is_fluent(): void
    {
        $checkbox = new HelperCheckbox('my_checkbox');
        $this->assertSame($checkbox, $checkbox->setDefault(true));
    }

}
