<?php

namespace Atom\Core\Console\Commands;

use Atom\Core\Models\Badge;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\progress;

class BadgeSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atom:sync-badges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The command for syncing the badges.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = Storage::disk('static')
            ->get(config('nitro.external_texts_file'));

        if (! $file) {
            $this->error(sprintf('The external texts file is empty or missing in %s.', Storage::disk('static')->path(config('nitro.external_texts_file'))));

            return 1;
        }

        $badges = collect(json_decode($file, true))
            ->filter(fn ($value, $key) => str_starts_with($key, 'badge_name_') || str_starts_with($key, 'badge_desc_'))
            ->toArray();

        progress(
            label: 'Syncing badges',
            steps: array_keys($badges),
            callback: fn ($key) => $this->sync($key, $badges[$key]),
        );
    }

    /**
     * Sync the local badge data.
     */
    public function sync(string $key, string $value): bool
    {
        $code = trim(str_replace(['badge_name_', 'badge_desc_'], '', $key));

        $column = str_starts_with($key, 'badge_name_') ? 'name' : 'description';

        return (bool) Badge::withoutEvents(fn () => Badge::updateOrCreate(
            ['code' => $code, 'file' => sprintf('%s.gif', $code)],
            [$column => $value],
        ));
    }
}
