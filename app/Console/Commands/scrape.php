<?php

namespace App\Console\Commands;

use App\Jobs\ScraperJob;
use Illuminate\Console\Command;

class scrape extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'scrape';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'scrape all sites';

    /**
     * The services that we will dispatch
     *
     * @var array
     */
    protected $services = [
        \App\Feeds\AH\Service::class,

    ];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Let\'s start scraping');
        foreach ($this->services as $service) {
            $this->info('' . $service);
            $service_object = new $service;
            $service_object->run();
        }
        $this->info('Finished');
    }
}