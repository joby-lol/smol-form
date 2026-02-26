<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form;

use Stringable;

/**
 * @internal
 * 
 * @method string name()
 */
interface CsrfInterface extends Stringable
{

    public function name(): string|Stringable|null;

    public function validateToken(string|null $token): bool;

    public function rotateToken(): static;

}
