<?php

namespace App\Console\Commands;

use Api\Shared\Domain\Services\CacheServiceInterface;
use Illuminate\Console\Command;

/**
 * Comando Artisan para limpiar el cache de Media
 */
class ClearMediaCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:cache:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear media cache (search and getById)';

    /**
     * Execute the console command.
     */
    public function handle(CacheServiceInterface $cacheService): int
    {
        $this->info('Clearing media cache...');
        
        try {
            $cacheService->flush();
            
            $this->info('✓ Media cache cleared successfully!');
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('✗ Failed to clear media cache: ' . $e->getMessage());
            
            return self::FAILURE;
        }
    }
}
