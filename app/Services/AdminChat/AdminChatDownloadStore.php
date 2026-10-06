<?php

namespace App\Services\AdminChat;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminChatDownloadStore
{
    /**
     * @param  array{filename: string, content_type: string, body: string}  $payload
     */
    public function put(array $payload): string
    {
        $token = Str::random(40);
        Cache::put($this->key($token), $payload, (int) config('admin_chat.download_ttl_seconds', 1800));

        return $token;
    }

    /**
     * @return array{filename: string, content_type: string, body: string}|null
     */
    public function get(string $token): ?array
    {
        $value = Cache::get($this->key($token));

        return is_array($value) ? $value : null;
    }

    private function key(string $token): string
    {
        return 'admin_chat_dl:'.$token;
    }
}
