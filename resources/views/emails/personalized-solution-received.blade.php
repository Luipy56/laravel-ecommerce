<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('mail.personalized_solution.subject', ['id' => $solution->id]) }}</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1a1a1a;">
    <p>{{ __('mail.personalized_solution.greeting') }}</p>
    <p>{{ __('mail.personalized_solution.body', ['id' => $solution->id]) }}</p>
    <p>{{ __('mail.personalized_solution.footer') }}</p>
</body>
</html>
