<?php

namespace Joby\Smol\Form;

use PHPUnit\Framework\TestCase;

class CsrfInputTest extends TestCase
{

    protected function setUp(): void
    {
        // ensure clean session state for each test
        $_SESSION = [];
    }

    public function test_renders_as_hidden_input(): void
    {
        $form = new Form('test_form');
        $csrf = new CsrfInput();
        $form->addChild($csrf);
        $this->assertStringContainsString('type="hidden"', (string) $csrf);
    }

    public function test_has_correct_name(): void
    {
        $form = new Form('test_form');
        $csrf = new CsrfInput();
        $form->addChild($csrf);
        $this->assertStringContainsString('name="_csrf"', (string) $csrf);
    }

    public function test_throws_without_parent_form(): void
    {
        $this->expectException(\RuntimeException::class);
        $csrf = new CsrfInput();
        $csrf->getToken();
    }

    public function test_generates_token_on_first_access(): void
    {
        $form = new Form('test_form');
        $csrf = new CsrfInput();
        $form->addChild($csrf);
        $token = $csrf->getToken();
        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token));
    }

    public function test_token_is_stable_before_rotation(): void
    {
        $form = new Form('test_form');
        $csrf = new CsrfInput();
        $form->addChild($csrf);
        $this->assertEquals($csrf->getToken(), $csrf->getToken());
    }

    public function test_rotate_changes_current_token(): void
    {
        $form = new Form('test_form');
        $csrf = new CsrfInput();
        $form->addChild($csrf);
        $original = $csrf->getToken();
        $csrf->rotateToken();
        $this->assertNotEquals($original, $csrf->getToken());
    }

    public function test_validate_token_accepts_current_token(): void
    {
        $form = new Form('test_form');
        $csrf = new CsrfInput();
        $form->addChild($csrf);
        $this->assertTrue($csrf->validateToken($csrf->getToken()));
    }

    public function test_validate_token_accepts_previous_token(): void
    {
        $form = new Form('test_form');
        $csrf = new CsrfInput();
        $form->addChild($csrf);
        $original = $csrf->getToken();
        $csrf->rotateToken();
        $this->assertTrue($csrf->validateToken($original));
    }

    public function test_validate_token_rejects_old_token_after_two_rotations(): void
    {
        $form = new Form('test_form');
        $csrf = new CsrfInput();
        $form->addChild($csrf);
        $original = $csrf->getToken();
        $csrf->rotateToken();
        $csrf->rotateToken();
        $this->assertFalse($csrf->validateToken($original));
    }

    public function test_validate_token_rejects_null(): void
    {
        $form = new Form('test_form');
        $csrf = new CsrfInput();
        $form->addChild($csrf);
        $this->assertFalse($csrf->validateToken(null));
    }

    public function test_validate_token_rejects_invalid_token(): void
    {
        $form = new Form('test_form');
        $csrf = new CsrfInput();
        $form->addChild($csrf);
        $this->assertFalse($csrf->validateToken('not_a_valid_token'));
    }

    public function test_tokens_are_independent_per_form(): void
    {
        $form1 = new Form('form_one');
        $form2 = new Form('form_two');
        $csrf1 = new CsrfInput();
        $csrf2 = new CsrfInput();
        $form1->addChild($csrf1);
        $form2->addChild($csrf2);
        $this->assertNotEquals($csrf1->getToken(), $csrf2->getToken());
    }

}
