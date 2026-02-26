<?php

/**
 * smolForm
 * https://github.com/joby-lol/smol-form
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\Form;

use Joby\HTML\Html5\Forms\InputTag;
use Joby\HTML\Html5\Forms\InputTag\TypeValue;
use Joby\Smol\Session\Session;
use RuntimeException;

/**
 * @internal
 * 
 * @method string name()
 */
class CsrfInput extends InputTag implements CsrfInterface
{

    public function __construct(
    )
    {
        parent::__construct(TypeValue::hidden);
        $this->setName('_csrf');
    }

    protected function parentForm(): Form
    {
        return $this->parentOfType(Form::class)
            ?? throw new RuntimeException("CsrfInput must be a child of a Form object");
    }

    public function getToken(): string
    {
        $tokens = $this->getAllTokens();
        // @phpstan-ignore-next-line getAllTokens() makes sure there's at least one token
        return end($tokens);
    }

    public function rotateToken(): static
    {
        $token_id = static::class . '::' . $this->parentForm()->formId();
        $tokens = Session::get($token_id) ?? [];
        if (!is_array($tokens))
            throw new RuntimeException("Session storage for CSRF tokens is not an array");
        $tokens[] = bin2hex(random_bytes(32));
        while (count($tokens) > 2)
            array_shift($tokens);
        Session::set($token_id, $tokens);
        Session::commit();
        return $this;
    }

    /**
     * @return array<int,string>
     */
    protected function getAllTokens(): array
    {
        $token_id = static::class . '::' . $this->parentForm()->formId();
        if (!Session::get($token_id))
            $this->rotateToken();
        $tokens = Session::get($token_id);
        if (!is_array($tokens))
            throw new RuntimeException("Session storage for CSRF tokens is not an array");
        // @phpstan-ignore-next-line we have to just trust the session
        return $tokens;
    }

    /**
     * @inheritDoc
     */
    public function validateToken(string|null $token): bool
    {
        if ($token === null)
            return false;
        return in_array($token, $this->getAllTokens());
    }

    public function __toString(): string
    {
        $this->setName('_csrf');
        $this->setValue($this->getToken());
        return parent::__toString();
    }

}
