<?php

return [
    'github_repo' => env('ADMIN_HELP_GITHUB_REPO', 'Luipy56/laravel-ecommerce'),
    'validation_label' => env('ADMIN_HELP_VALIDATION_LABEL', 'waiting for human validation'),
    'validation_label_color' => 'C2E0C6',
    'validation_label_description' => 'Human review required before further agent automation',
    'storage_path' => storage_path('app/admin-help'),
    'prompt_path' => config_path('admin_help_prompt.md'),
    'cursor_agent_timeout' => (int) env('ADMIN_HELP_CURSOR_AGENT_TIMEOUT', 300),
    'processing_stale_minutes' => (int) env('ADMIN_HELP_PROCESSING_STALE_MINUTES', 30),
    'comment_max_length' => 4000,
    'title_max_length' => 200,
];
