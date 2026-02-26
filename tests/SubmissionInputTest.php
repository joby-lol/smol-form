<?php

namespace Joby\Smol\Form;

use PHPUnit\Framework\TestCase;

class SubmissionInputTest extends TestCase
{

    public function test_renders_as_hidden_input(): void
    {
        $input = new SubmissionInput();
        $this->assertStringContainsString('type="hidden"', (string) $input);
    }

    public function test_has_correct_name(): void
    {
        $input = new SubmissionInput();
        $this->assertStringContainsString('name="_smol"', (string) $input);
    }

    public function test_value_absent_without_parent_form(): void
    {
        $input = new SubmissionInput();
        $this->assertStringNotContainsString('value=', (string) $input);
    }

    public function test_value_set_to_form_id_with_direct_parent_form(): void
    {
        $form = new Form('test_form');
        $form->addChild($input = new SubmissionInput());
        $this->assertStringContainsString('value="test_form"', (string) $input);
    }

    public function test_value_set_to_form_id_with_grandparent_form(): void
    {
        $form = new Form('test_form');
        $div = new \Joby\HTML\Html5\TextContentTags\DivTag();
        $form->addChild($div);
        $div->addChild($input = new SubmissionInput());
        $this->assertStringContainsString('value="test_form"', (string) $input);
    }

}
