# smolForm

A lightweight, composable form builder for PHP. Forms work without JavaScript and are usable without CSS. Any JavaScript or CSS you add is genuine progressive enhancement — never load-bearing.

## Installation

```bash
composer require joby/smol-form
```

## PHP Version

Requires PHP 8.3+.

## About

smolForm provides typed, validated form inputs with a fluent API. Each input is an HTML element that can be cast to a string, CSRF protection is built in via session, and validation runs server-side with errors surfaced inline.

- **No required JavaScript**: Forms submit and validate without a single line of JS
- **Typed values**: Each input returns a typed PHP value (`string`, `float`, `DateTimeImmutable`, `bool`, `array`)
- **Server-side validation**: Built-in rules per input type, plus custom rules on the Validator
- **Fluent API**: Chainable configuration methods on all inputs
- **CSRF protection**: Built in via smol-session
- **Disableable inputs**: Disabled inputs return null regardless of submitted data

## Basic Usage

```php
use Joby\Smol\Form\Form;
use Joby\Smol\Form\Field;
use Joby\Smol\Form\Inputs\TextInput;
use Joby\Smol\Form\Inputs\EmailInput;

$form = new Form('contact_form');

$name = new TextInput('name', 'Your name');
$email = new EmailInput('email', 'Email address');
$email->formValidator()->setRequired(true);

$form->addChild(new Field($name));
$form->addChild(new Field($email));
$form->finalize();

echo $form;

if ($form->isFormAttempted() && $form->isValid()) {
    $name_value = $name->formValue();   // string|null
    $email_value = $email->formValue(); // string|null
}
```

## Inputs

All inputs are constructed with at minimum a field name and a label. Most accept an optional default value.

### Text inputs

```php
use Joby\Smol\Form\Inputs\TextInput;
use Joby\Smol\Form\Inputs\TextareaInput;
use Joby\Smol\Form\Inputs\EmailInput;
use Joby\Smol\Form\Inputs\PasswordInput;
use Joby\Smol\Form\Inputs\UrlInput;
use Joby\Smol\Form\Inputs\TelInput;
use Joby\Smol\Form\Inputs\SearchInput;

$text     = new TextInput('username', 'Username');
$textarea = new TextareaInput('bio', 'Biography');
$email    = new EmailInput('email', 'Email address');     // validates format
$password = new PasswordInput('password', 'Password');   // never pre-filled
$url      = new UrlInput('website', 'Website');           // validates format, defaults placeholder to https://
$tel      = new TelInput('phone', 'Phone number');        // no format validation — phone formats vary too much internationally
$search   = new SearchInput('q', 'Search');

// Placeholder text (note: bad practice to use instead of a label)
$text->setPlaceholder('e.g. johndoe');
```

`formValue()` returns `string|null` for all text inputs.

### Number input

```php
use Joby\Smol\Form\Inputs\NumberInput;

$number = new NumberInput('quantity', 'Quantity');
$number->setNumberMin(1.0);
$number->setNumberMax(100.0);
$number->setNumberStep(5.0);
```

`formValue()` returns `float|null`. Min, max, and step constraints are validated server-side and also set as HTML attributes for browser-level enforcement. Step is validated relative to the field's default value if one is set, otherwise from zero.

### Date input

```php
use Joby\Smol\Form\Inputs\DateInput;

$date = new DateInput('birthday', 'Date of birth');
$date->setDateMin(new DateTimeImmutable('1900-01-01'));
$date->setDateMax(new DateTimeImmutable('today'));
```

`formValue()` returns `DateTimeImmutable|null` with time zeroed to midnight. Min and max accept any `DateTimeInterface`.

### DateTime input

```php
use Joby\Smol\Form\Inputs\DateTimeInput;

$dt = new DateTimeInput('scheduled_at', 'Schedule for');
$dt->setDateTimeMin(new DateTimeImmutable('now'));
$dt->setDateTimeMax(new DateTimeImmutable('+1 year'));

// Step accepts integer seconds or a DateInterval
// Note: months and years are not supported in DateInterval step (variable length)
$dt->setDateTimeStep(1800);                    // 30 minutes in seconds
$dt->setDateTimeStep(new DateInterval('PT1H')); // 1 hour as DateInterval

// Step validation uses the field's default as base — if no default is set, step is not validated
$dt->setFormDefault(new DateTimeImmutable('2025-01-01 09:00:00'));
```

`formValue()` returns `DateTimeImmutable|null`.

### Checkbox

```php
use Joby\Smol\Form\Inputs\CheckboxInput;

$agree = new CheckboxInput('agree', 'I agree to the terms', default: false);
```

`formValue()` returns `bool`. Note that unchecked checkboxes submit no value — smolForm handles this correctly.

### Radio buttons

```php
use Joby\Smol\Form\Inputs\RadioInput;

$size = new RadioInput('size', 'Size', [
    'sm' => 'Small',
    'md' => 'Medium',
    'lg' => 'Large',
], default: 'md');
```

`formValue()` returns `string|null`. Only valid option keys are returned — submitted values not in the options array are rejected.

### Select

```php
use Joby\Smol\Form\Inputs\SelectInput;

$country = new SelectInput('country', 'Country', [
    'us' => 'United States',
    'ca' => 'Canada',
    'gb' => 'United Kingdom',
]);

// Optional empty/placeholder option
$country->setEmptyOption('-- select a country --');
```

`formValue()` returns `string|null`. Submitted values not in the options array are rejected.

### Multi-checkbox

```php
use Joby\Smol\Form\Inputs\MultiCheckboxInput;

$tags = new MultiCheckboxInput('tags', 'Tags', [
    'php'        => 'PHP',
    'javascript' => 'JavaScript',
    'css'        => 'CSS',
], default: ['php']);

// Optional selection count constraints
$tags->setFormMinSelections(1);
$tags->setFormMaxSelections(3);
```

`formValue()` returns `array<string>` of selected keys (empty array if none selected, null if form not yet attempted). Only valid option keys are included.

## Validation

Each input has a `Validator` accessible via `formValidator()`. The validator runs required checks first, then any self-validation built into the input type, then any custom rules you attach.

```php
// Required
$input->formValidator()->setRequired(true);

// Custom rule — return an error string or null
$input->formValidator()->addRule(function($value) {
    if (strlen($value) < 8)
        return 'Must be at least 8 characters';
    return null;
});

// Check validity
$form->finalize();
if ($form->isFormAttempted()) {
    if (!$form->isValid()) {
        // errors are surfaced inline when the form renders
    }
}
```

Validation only runs after `finalize()` is called and the form has been attempted.

## Disabling inputs

Inputs implementing `DisableableInput` return null from `formValue()` when disabled, regardless of what was submitted. This prevents disabled fields from being tampered with client-side.

```php
$input->setFormDisabled(true);
$input->formValue(); // always null when disabled
```

## Setting defaults

```php
$input->setFormDefault('some value');
```

The default is used as the initial value before submission, and as the step base for `NumberInput` and `DateTimeInput`.

## Field wrapper

The `Field` class wraps an input with its label and error display. Inputs implementing `SelfLabeledInput` (checkbox, radio, multi-checkbox) don't need a `Field` wrapper as they render their own label structure.

```php
$form->addChild(new Field($text_input));   // most inputs
$form->addChild($checkbox_input);           // SelfLabeledInput
$form->addChild($radio_input);             // SelfLabeledInput
$form->addChild($multi_checkbox_input);    // SelfLabeledInput
```

## License

MIT License