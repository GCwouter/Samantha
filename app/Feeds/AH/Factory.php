<?php

namespace App\Feeds\AH;

use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use App\Functions;

class Factory
{
    public function process($dataset)
    {
    	foreach ($dataset as $data) {
	    	Carbon::setWeekStartsAt(Carbon::MONDAY);
			Carbon::setWeekEndsAt(Carbon::SUNDAY);
    		$functions = new Functions;
	    	$raw = json_decode($data, true);
	    	$lanes = $raw['_embedded']['lanes'];
	    	foreach ($lanes as $lane) {
	    		$categories = $lane['_embedded']['items'];
	    		foreach ($categories as $categorie) {
	    			if ($categorie['resourceType'] == 'Product') {
	    				$productLabel = $categorie['_embedded']['productCard'];
	    				var_dump($productLabel);
	    				$product = [];
	    				$product['week'] = $functions->findWeek($productLabel['navItem']['link']['href']);
	    				$product['actionDateStart'] = Carbon::now()->startOfWeek()->toDateString();
	    				$product['actionDateEnd'] = Carbon::now()->endOfWeek()->toDateString();
	    				$product['orginalPrice'] = null;
	    				$product['categorie'] = $productLabel['label'];
	    				$product['name'] = $productLabel['navItem']['title'];
	    				$productCard = $productLabel['_embedded']['product'];
	    				//var_dump($productCard);
	    				$product['unitSize'] = $productCard['unitSize'];
	    				if ( array_key_exists('was', $productCard['priceLabel']) ) {
	    					$product['orginalPrice'] = $productCard['priceLabel']['was'];
	    				}
	    				$product['actionPrice'] = $productCard['priceLabel']['now'];
	    				if ( array_key_exists('discount', $productCard) ) {
		    				var_dump(array_key_exists('period', $productCard['discount']));
		    				if ( array_key_exists('period', $productCard['discount']) ) {
		    					$product['period'] = $productCard['discount']['period'];
		    					$product['label'] = $productCard['discount']['label'];
		    				}
		    			}
	    				$product['AHid'] = $productCard['id'];
	    				
	    				//dd($categorie);
	    				var_dump($product);
	    				//dd('doei');
	    			}
	    		}
	    	}
	    }
    }
}