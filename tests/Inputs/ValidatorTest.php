<?php

namespace Joby\Smol\Form\Inputs;

use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{

    /**
     * @param mixed $value
     * @param int|null $expected_calls — null means "don't verify call count"
     * @return Validator<mixed>
     */
    protected function make(mixed $value = null, int|null $expected_calls = null): Validator
    {
        $input = $this->createMock(InputInterface::class);
        $expectation = $expected_calls !== null
            ? $input->expects($this->exactly($expected_calls))->method('formValue')
            : $input->method('formValue');
        $expectation->willReturn($value);
        return new Validator($input);
    }

    // --- basic state ---

    public function test_errors_are_empty_before_validation(): void
    {
        $v = $this->make('hello', expected_calls: 0);
        $this->assertSame([], $v->errors());
    }

    public function test_required_defaults_to_false(): void
    {
        $this->assertFalse($this->make(expected_calls: 0)->required());
    }

    // --- runValidation / short-circuit ---

    public function test_does_not_run_twice_without_force(): void
    {
        $call_count = 0;
        $v = $this->make('value', expected_calls: 1);
        $v->addRule(function ($val) use (&$call_count) {
            $call_count++;
            return null;
        });

        $v->runValidation();
        $v->runValidation();

        $this->assertSame(1, $call_count);
    }

    public function test_force_run_reruns_validation(): void
    {
        $call_count = 0;
        $v = $this->make('value', expected_calls: 2);
        $v->addRule(function ($val) use (&$call_count) {
            $call_count++;
            return null;
        });

        $v->runValidation();
        $v->runValidation(force_run: true);

        $this->assertSame(2, $call_count);
    }

    // --- required ---

    public function test_required_fails_on_null_value(): void
    {
        $v = $this->make(null, expected_calls: 1);
        $v->setRequired(true)->runValidation();
        $this->assertNotEmpty($v->errors());
    }

    public function test_required_fails_on_empty_string(): void
    {
        $v = $this->make('', expected_calls: 1);
        $v->setRequired(true)->runValidation();
        $this->assertNotEmpty($v->errors());
    }

    public function test_required_passes_with_non_empty_value(): void
    {
        $v = $this->make('hello', expected_calls: 1);
        $v->setRequired(true)->runValidation();
        $this->assertSame([], $v->errors());
    }

    public function test_required_uses_default_message(): void
    {
        $v = $this->make(null, expected_calls: 1);
        $v->setRequired(true)->runValidation();
        $this->assertSame(['Field is required'], $v->errors());
    }

    public function test_required_uses_custom_message(): void
    {
        $v = $this->make(null, expected_calls: 1);
        $v->setRequired(true)
            ->setRequiredErrorMessage('This field cannot be blank.')
            ->runValidation();
        $this->assertSame(['This field cannot be blank.'], $v->errors());
    }

    public function test_reset_required_message_restores_default(): void
    {
        $v = $this->make(null, expected_calls: 1);
        $v->setRequired(true)
            ->setRequiredErrorMessage('Custom message')
            ->setRequiredErrorMessage(null)
            ->runValidation();
        $this->assertSame(['Field is required'], $v->errors());
    }

    public function test_required_short_circuits_rules(): void
    {
        $rule_ran = false;
        $v = $this->make(null, expected_calls: 1);
        $v->setRequired(true)->addRule(function ($val) use (&$rule_ran) {
            $rule_ran = true;
            return 'error';
        });
        $v->runValidation();

        $this->assertFalse($rule_ran, 'Rules should not run when required check fails');
        $this->assertCount(1, $v->errors());
    }

    // --- rules ---

    public function test_passing_rule_adds_no_errors(): void
    {
        $v = $this->make('valid', expected_calls: 1);
        $v->addRule(fn($val) => null)->runValidation();
        $this->assertSame([], $v->errors());
    }

    public function test_failing_rule_adds_error(): void
    {
        $v = $this->make('bad', expected_calls: 1);
        $v->addRule(fn($val) => 'Value is bad')->runValidation();
        $this->assertSame(['Value is bad'], $v->errors());
    }

    public function test_multiple_rules_can_all_fail(): void
    {
        $v = $this->make('x', expected_calls: 1);
        $v->addRule(fn($val) => 'Error one')
            ->addRule(fn($val) => 'Error two')
            ->runValidation();
        $this->assertSame(['Error one', 'Error two'], $v->errors());
    }

    public function test_rules_receive_input_value(): void
    {
        $received = null;
        $v = $this->make('expected-value', expected_calls: 1);
        $v->addRule(function ($val) use (&$received) {
            $received = $val;
            return null;
        });
        $v->runValidation();
        $this->assertSame('expected-value', $received);
    }

    public function test_rules_do_not_run_on_null_value(): void
    {
        $rule_ran = false;
        $v = $this->make(null, expected_calls: 1);
        $v->addRule(function ($val) use (&$rule_ran) {
            $rule_ran = true;
            return 'error';
        });
        $v->runValidation();
        $this->assertFalse($rule_ran);
        $this->assertSame([], $v->errors());
    }

    public function test_rules_do_not_run_on_empty_string(): void
    {
        $rule_ran = false;
        $v = $this->make('', expected_calls: 1);
        $v->addRule(function ($val) use (&$rule_ran) {
            $rule_ran = true;
            return 'error';
        });
        $v->runValidation();
        $this->assertFalse($rule_ran);
        $this->assertSame([], $v->errors());
    }

    public function test_rules_still_run_on_non_empty_value(): void
    {
        $rule_ran = false;
        $v = $this->make('something', expected_calls: 1);
        $v->addRule(function ($val) use (&$rule_ran) {
            $rule_ran = true;
            return null;
        });
        $v->runValidation();
        $this->assertTrue($rule_ran);
    }

    public function test_not_required_does_not_run_rules_on_empty_value(): void
    {
        $rule_ran = false;
        $v = $this->make(null, expected_calls: 1);
        $v->addRule(function ($val) use (&$rule_ran) {
            $rule_ran = true;
            return null;
        });
        $v->runValidation();
        $this->assertFalse($rule_ran);
    }

    // --- resetValidation ---

    public function test_reset_clears_errors(): void
    {
        $v = $this->make('bad', expected_calls: 2);
        $v->addRule(fn($val) => 'Error')->runValidation();
        $this->assertNotEmpty($v->errors());

        $v->resetValidation();
        $v->runValidation();
        $this->assertSame(['Error'], $v->errors());
    }

    public function test_reset_allows_rerun(): void
    {
        $call_count = 0;
        $v = $this->make('value', expected_calls: 2);
        $v->addRule(function ($val) use (&$call_count) {
            $call_count++;
            return null;
        });

        $v->runValidation();
        $v->resetValidation();
        $v->runValidation();

        $this->assertSame(2, $call_count);
    }

    public function test_reset_does_not_remove_rules(): void
    {
        $call_count = 0;
        $v = $this->make('value', expected_calls: 2);
        $v->addRule(function ($val) use (&$call_count) {
            $call_count++;
            return null;
        });

        $v->runValidation();
        $v->resetValidation();
        $v->runValidation();

        $this->assertSame(2, $call_count, 'Rule should still be present after reset');
    }

    // --- method chaining ---

    public function test_set_required_returns_static(): void
    {
        $v = $this->make(expected_calls: 0);
        $this->assertSame($v, $v->setRequired(true));
    }

    public function test_set_required_message_returns_static(): void
    {
        $v = $this->make(expected_calls: 0);
        $this->assertSame($v, $v->setRequiredErrorMessage('msg'));
    }

    public function test_add_rule_returns_static(): void
    {
        $v = $this->make(expected_calls: 0);
        $this->assertSame($v, $v->addRule(fn($val) => null));
    }

    public function test_run_validation_returns_static(): void
    {
        $v = $this->make(expected_calls: 1);
        $this->assertSame($v, $v->runValidation());
    }

    public function test_reset_validation_returns_static(): void
    {
        $v = $this->make(expected_calls: 0);
        $this->assertSame($v, $v->resetValidation());
    }

}
