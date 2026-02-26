<?php

namespace Joby\Smol\Form;

use PHPUnit\Framework\TestCase;

class SubmitButtonTest extends TestCase
{

    public function test_renders_as_submit_button(): void
    {
        $button = new SubmitButton();
        $this->assertStringContainsString('type="submit"', (string) $button);
    }

    public function test_has_correct_class(): void
    {
        $button = new SubmitButton();
        $this->assertStringContainsString('smol-form__submit-button', (string) $button);
    }

    public function test_form_attribute_abset_without_parent_form(): void
    {
        $button = new SubmitButton();
        $this->assertStringNotContainsString('form=', (string) $button);
    }

    public function test_form_attributes_set_with_parent_form(): void
    {
        $form = new Form('test_form');
        $form->addChild($button = new SubmitButton());
        $this->assertStringContainsString('form="test_form"', (string) $button);
    }

    public function test_form_attributes_set_with_grandparent_form(): void
    {
        $form = new Form('test_form');
        $div = new \Joby\HTML\Html5\TextContentTags\DivTag();
        $form->addChild($div);
        $div->addChild($button = new SubmitButton());
        $this->assertStringContainsString('form="test_form"', (string) $button);
    }

}
