<?php

namespace App\Feeds\AH;

use App\Feeds\AH\Crawler;
use App\Feeds\AH\Factory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Service
{
    public function run()
    {
        $date = Carbon::now();
        $crawler = new Crawler;
        $data = $crawler->run();
        $factory = new Factory;
        $AH = $factory->process($data);
    }
}

class Product extends Model
{
	public $table = 'AHaction_table';

	public $fillable = ['AHid'];
}