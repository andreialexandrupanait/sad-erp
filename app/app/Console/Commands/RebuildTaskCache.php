<?php

namespace App\Console\Commands;

use App\Models\TaskDisplayCache;
use Illuminate\Console\Command;

class RebuildTaskCache extends Command
{
    protected $signature = 'tasks:rebuild-cache {--organization= : Rebuild cache for specific organization}';
    protected $description = 'Rebuild task display cache for fast rendering';

    public function handle()
    {
        $this->info('Rebuilding task display cache...');

        TaskDisplayCache::rebuildAll();

        $count = TaskDisplayCache::count();
        $this->info("✓ Successfully rebuilt cache for {$count} tasks");

        return Command::SUCCESS;
    }
}
