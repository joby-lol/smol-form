<?php

namespace Joby\Smol\Form;

use Joby\Smol\Form\Inputs\TextInput;
use Joby\Smol\Request\Cookies\Cookies;
use Joby\Smol\Request\Headers\Headers;
use Joby\Smol\Request\Method;
use Joby\Smol\Request\Post\Post;
use Joby\Smol\Request\Request;
use Joby\Smol\Request\Source\Source;
use Joby\Smol\URL\UrlFactory;

class FormTest extends FormTestCase
{

    // --- basic rendering ---

    public function test_has_smol_form_class(): void
    {
        $form = new Form('test_form');
        $this->assertStringContainsString('smol-form', (string) $form);
    }

    public function test_renders_hidden_form_tag(): void
    {
        $form = new Form('test_form');
        $this->assertStringContainsString('display:none', (string) $form);
        $this->assertStringContainsString('visibility:hidden', (string) $form);
    }

    public function test_renders_submit_button(): void
    {
        $form = new Form('test_form');
        $this->assertStringContainsString('type="submit"', (string) $form);
    }

    public function test_submit_button_container_is_last_child(): void
    {
        $form = new Form('test_form');
        $input = new TextInput('name', 'Name');
        $form->addChild($input);
        $html = (string) $form;
        $this->assertGreaterThan(
            strpos($html, 'name="name"'),
            strpos($html, 'smol-form__submit'),
        );
    }

    public function test_renders_validation_container(): void
    {
        $form = new Form('test_form');
        $this->assertStringContainsString('smol-form__validation', (string) $form);
    }

    // --- form id ---

    public function test_form_id_matches_constructor(): void
    {
        $form = new Form('my_form');
        $this->assertEquals('my_form', $form->formId());
    }

    // --- submission detection ---

    public function test_is_not_submitted_without_post_data(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $this->assertFalse($form->isFormSubmitted());
    }

    public function test_is_submitted_with_correct_post_data(): void
    {
        $form = $this->make_submitted_form('test_form');
        $this->assertTrue($form->isFormSubmitted());
    }

    public function test_is_not_submitted_when_different_form_id_posted(): void
    {
        $request = new Request(
            (new UrlFactory())->baseUrl(),
            Method::POST,
            new Headers(null, null, null, null, []),
            new Cookies([]),
            new Post(['_smol' => 'other_form'], []),
            new Source('localhost', 'localhost'),
        );
        $form = new Form('test_form', $request, null);
        $this->assertFalse($form->isFormSubmitted());
    }

    // --- inputs walk ---

    public function test_inputs_finds_direct_child_input(): void
    {
        $form = new Form('test_form');
        $input = new TextInput('name', 'Name');
        $form->addChild($input);
        $found = iterator_to_array($form->inputs(), false);
        $this->assertContains($input, $found);
    }

    public function test_inputs_finds_nested_input(): void
    {
        $form = new Form('test_form');
        $input = new TextInput('name', 'Name');
        $field = new Field($input);
        $form->addChild($field);
        $found = iterator_to_array($form->inputs(), false);
        $this->assertContains($input, $found);
    }

    public function test_field_method_finds_input_by_name(): void
    {
        $form = new Form('test_form');
        $input = new TextInput('name', 'Name');
        $form->addChild($input);
        $this->assertSame($input, $form->field('name'));
    }

    public function test_field_method_returns_null_for_missing_name(): void
    {
        $form = new Form('test_form');
        $this->assertNull($form->field('nonexistent'));
    }

    // --- finalize / callbacks ---

    public function test_callback_not_called_when_not_submitted(): void
    {
        $form = $this->make_unsubmitted_form('test_form');
        $called = false;
        $form->addCallback(function () use (&$called) {
            $called = true;
        });
        $form->finalize();
        $this->assertFalse($called);
    }

    public function test_callback_called_on_valid_submission(): void
    {
        $form = $this->make_submitted_form('test_form');
        $called = false;
        $form->addCallback(function () use (&$called) {
            $called = true;
        });
        $form->finalize();
        $this->assertTrue($called);
    }

    public function test_callback_not_called_when_validation_fails(): void
    {
        $form = $this->make_submitted_form('test_form', ['name' => '']);
        $input = new TextInput('name', 'Name');
        $input->formValidator()->setRequired(true);
        $form->addChild($input);
        $called = false;
        $form->addCallback(function () use (&$called) {
            $called = true;
        });
        $form->finalize();
        $this->assertFalse($called);
    }

    public function test_finalize_is_idempotent(): void
    {
        $form = $this->make_submitted_form('test_form');
        $count = 0;
        $form->addCallback(function () use (&$count) {
            $count++;
        });
        $form->finalize();
        $form->finalize();
        $this->assertEquals(1, $count);
    }

    public function test_invalid_class_added_on_failed_validation(): void
    {
        $form = $this->make_submitted_form('test_form', ['name' => '']);
        $input = new TextInput('name', 'Name');
        $input->formValidator()->setRequired(true);
        $form->addChild($input);
        $form->finalize();
        $this->assertStringContainsString('smol-form--submitted', (string) $form);
        $this->assertStringContainsString('smol-form--invalid', (string) $form);
    }

    public function test_submitted_class_added_on_submission(): void
    {
        $form = $this->make_submitted_form('test_form');
        $form->finalize();
        $this->assertStringContainsString('smol-form--submitted', (string) $form);
    }

    public function test_add_callback_is_fluent(): void
    {
        $form = new Form('test_form');
        $this->assertSame($form, $form->addCallback(fn($f) => null));
    }

}
