<?php

namespace Yasaie\Dictionary\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Yasaie\Dictionary\Dictionary;

/**
 * Class    RemoveDuplicateDictionaries
 *
 * @author  Payam Yasaie <payam@yasaie.ir>
 * @since   2019-09-08
 *
 * @package App\Console\Commands
 */
class RemoveDuplicateDictionaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dictionary:remove-duplicate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate items from dictionary';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $duplicates = Dictionary::select([
            \DB::raw('MAX(id) as id'),
            \DB::raw('COUNT(*) as counts')
        ])
            ->groupBy(['language_id', 'context_type', 'context_id', 'key'])
            ->having('counts', '>', 1)
            ->pluck('id');

        Dictionary::whereIn('id', $duplicates)
            ->delete();

        $counts = $duplicates->count();

        $this->info($counts . ' ' . Str::plural('duplicate', $counts) . ' removed');
    }
}
