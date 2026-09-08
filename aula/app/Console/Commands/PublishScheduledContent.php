<?php

namespace App\Console\Commands;

use App\Models\Content;
use Illuminate\Console\Command;

class PublishScheduledContent extends Command
{
    protected $signature = 'content:publish-scheduled';

    protected $description = 'Publica contenido cuya fecha programada ya pasó';

    public function handle(): int
    {
        $contents = Content::query()
            ->where('status', Content::STATUS_SCHEDULED)
            ->where('published_at', '<=', now())
            ->get();

        foreach ($contents as $content) {
            $content->update(['status' => Content::STATUS_PUBLISHED]);
        }

        $published = $contents->count();

        if ($published > 0) {
            $this->info("{$published} contenido(s) publicado(s) automáticamente.");
        } else {
            $this->line('No hay contenido pendiente de publicación.');
        }

        return self::SUCCESS;
    }
}
