<?php

return [
    'github_repo' => env('ADMIN_HELP_GITHUB_REPO', 'Luipy56/laravel-ecommerce'),
    'fallback_label' => 'waiting for human validation',
    'allowed_labels' => [
        'to-staging' => [
            'color' => '5319E7',
            'description' => 'Admin Help: autoagents implements and deploys to staging',
        ],
        'waiting for human validation' => [
            'color' => 'C5DEF5',
            'description' => 'Admin Help: pending human review before autoagents queue',
        ],
    ],
    'storage_path' => storage_path('app/admin-help'),
    'prompt_path' => base_path('autoissue/admin-help-agent.md'),
    'cursor_agent_timeout' => (int) env('ADMIN_HELP_CURSOR_AGENT_TIMEOUT', 900),
    'cursor_agent_path' => env('ADMIN_HELP_CURSOR_AGENT_PATH', 'cursor-agent'),
    'processing_stale_minutes' => (int) env('ADMIN_HELP_PROCESSING_STALE_MINUTES', 30),
    'fallback_schedule_at' => env('ADMIN_HELP_FALLBACK_SCHEDULE_AT', '03:00'),
    'fallback_schedule_limit' => (int) env('ADMIN_HELP_FALLBACK_SCHEDULE_LIMIT', 10),
    'comment_max_length' => 4000,
    'title_max_length' => 200,
];
