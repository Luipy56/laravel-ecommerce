<?php

namespace App\Services\AdminChat;

use Illuminate\Support\Facades\File;

class AdminChatContextPack
{
    public function load(): string
    {
        $dir = (string) config('admin_chat.pack_path');
        $files = ['CONTEXT.md', 'DENY.md', 'ALLOW.md', 'SCHEMA-BRIEF.md', 'TOOLS.md', 'EXAMPLES.md'];
        $parts = [];
        foreach ($files as $file) {
            $path = $dir.DIRECTORY_SEPARATOR.$file;
            if (File::isFile($path)) {
                $parts[] = "# FILE {$file}\n".File::get($path);
            }
        }

        return implode("\n\n", $parts);
    }
}
