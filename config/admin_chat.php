<?php

return [

    /*
    | Default provider: ollama | cursor | heuristic
    | heuristic = tools only (no LLM). Used in tests and as fallback.
    */
    'default_provider' => env('ADMIN_CHAT_PROVIDER', 'ollama'),

    'max_message_chars' => 4000,

    'max_history' => 8,

    'max_tool_rounds' => 3,

    'chat_row_limit' => 10,

    'pack_path' => env('ADMIN_CHAT_PACK_PATH', base_path('docs/admin-chat')),

    'ollama' => [
        'base_url' => env('ADMIN_CHAT_OLLAMA_URL', 'http://host.docker.internal:11434'),
        'model' => env('ADMIN_CHAT_OLLAMA_MODEL', 'qwen3.8:latest'),
        'timeout' => (int) env('ADMIN_CHAT_OLLAMA_TIMEOUT', 45),
    ],

    'cursor' => [
        'binary' => env('ADMIN_CHAT_CURSOR_AGENT_PATH', env('ADMIN_HELP_CURSOR_AGENT_PATH', 'cursor-agent')),
        'timeout' => (int) env('ADMIN_CHAT_CURSOR_TIMEOUT', 60),
    ],

    'download_ttl_seconds' => 1800,

];
