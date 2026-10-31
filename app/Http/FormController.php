<?php

declare(strict_types=1);

namespace App\Http;

use App\Frontend\Svelte;
use Sahil\Contracts\Foundation\ApplicationContract;
use Sahil\Http\Request;
use Sahil\Http\Response;

/**
 * Example form with server-side validation feedback.
 *
 * The GET request renders an empty form. A POST request validates the
 * submitted values and either renders a success state or re-renders the same
 * page with per-field errors and the user's values preserved. No session is
 * required, which keeps the example focused on the Svelte data pipeline.
 */
final class FormController
{
    public function __construct(private readonly ApplicationContract $application)
    {
    }

    public function show(): Response
    {
        return $this->render(
            values: ['name' => '', 'email' => '', 'message' => ''],
            errors: [],
        );
    }

    public function submit(Request $request): Response
    {
        $form = $request->form();
        $values = [
            'name' => trim((string) ($form['name'] ?? '')),
            'email' => trim((string) ($form['email'] ?? '')),
            'message' => trim((string) ($form['message'] ?? '')),
        ];

        $errors = $this->validate($values);

        return $this->render(
            values: $values,
            errors: $errors,
            success: $errors === [] ? 'Thanks — your message passed both client-side and server-side validation.' : null,
            status: $errors === [] ? 200 : 422,
        );
    }

    /**
     * @param array{name: string, email: string, message: string} $values
     * @param array<string, string> $errors
     */
    private function render(array $values, array $errors, ?string $success = null, int $status = 200): Response
    {
        return Svelte::render('pages.form', [
            'title' => 'Contact form',
            'app_name' => (string) $this->application->config()->get('app.name', 'SahilPHP'),
            'php_version' => PHP_VERSION,
            'values' => $values,
            'errors' => $errors,
            'success' => $success,
            'status' => $status,
        ], $status);
    }

    /**
     * @param array{name: string, email: string, message: string} $values
     * @return array<string, string>
     */
    private function validate(array $values): array
    {
        $errors = [];

        $nameLength = strlen($values['name']);

        if ($nameLength < 2 || $nameLength > 80) {
            $errors['name'] = 'Enter a name between 2 and 80 characters.';
        }

        if ($values['email'] === '' || filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Enter a valid email address.';
        }

        $messageLength = strlen($values['message']);

        if ($messageLength < 10 || $messageLength > 2000) {
            $errors['message'] = 'Enter a message between 10 and 2000 characters.';
        }

        return $errors;
    }
}
