<?php

namespace Joby\Smol\Form;

use Joby\Smol\Form\Form;
use Joby\Smol\Request\Cookies\Cookies;
use Joby\Smol\Request\Headers\Headers;
use Joby\Smol\Request\Method;
use Joby\Smol\Request\Post\Post;
use Joby\Smol\Request\Request;
use Joby\Smol\Request\Source\Source;
use Joby\Smol\URL\UrlFactory;
use PHPUnit\Framework\TestCase;

abstract class FormTestCase extends TestCase
{

    protected function make_submitted_form(string $form_id, array $post_data = []): Form
    {
        return new Form(
            $form_id,
            new Request(
                (new UrlFactory())->baseUrl(),
                Method::POST,
                new Headers(null, null, null, null, []),
                new Cookies([]),
                new Post(array_merge(['_smol' => $form_id], $post_data), []),
                new Source('localhost', 'localhost'),
            ),
            null,
        );
    }

    protected function make_unsubmitted_form(string $form_id): Form
    {
        return new Form(
            $form_id,
            new Request(
                (new UrlFactory())->baseUrl(),
                Method::POST,
                new Headers(null, null, null, null, []),
                new Cookies([]),
                new Post([], []),
                new Source('localhost', 'localhost'),
            ),
            null,
        );
    }

}
