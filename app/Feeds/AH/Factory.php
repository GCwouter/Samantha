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
	    				$product = [];
	    				$product['week'] = $functions->findWeek($productLabel['navItem']['link']['href']);
	    				$product['actionDateStart'] = Carbon::now()->startOfWeek()->toDateString();
	    				$product['actionDateEnd'] = Carbon::now()->endOfWeek()->toDateString();
	    				$product['label'] = $product['period'] = $product['originalPrice'] = null;
	    				$product['categorie'] = $productLabel['label'];
	    				$product['name'] = $productLabel['navItem']['title'];
	    				$productCard = $productLabel['_embedded']['product'];
	    				$product['unitSize'] = $productCard['unitSize'];
	    				if ( array_key_exists('was', $productCard['priceLabel']) ) {
	    					$product['originalPrice'] = $productCard['priceLabel']['was'];
	    				}
	    				$product['actionPrice'] = $productCard['priceLabel']['now'];
	    				if ( array_key_exists('discount', $productCard) ) {
		    				if ( array_key_exists('period', $productCard['discount']) ) {
		    					$product['period'] = $productCard['discount']['period'];
		    					$product['label'] = $productCard['discount']['label'];
		    				}
		    			}
	    				$product['AHid'] = $productCard['id'];
	    				$product_object = Product::firstOrNew([
											'AHid' => $product['AHid'], 
											'week' => $product['week']
										]);
	    				$product_object->week = $product['week'];
	    				$product_object->actionDateStart = $product['actionDateStart'];
	    				$product_object->actionDateEnd = $product['actionDateEnd'];
	    				$product_object->label = $product['label'];
	    				$product_object->categorie = $product['categorie'];
	    				$product_object->name = $product['name'];
	    				$product_object->unitSize = $product['unitSize'];
	    				$product_object->originalPrice = $product['originalPrice'];
	    				$product_object->actionPrice = $product['actionPrice'];
	    				$product_object->period = $product['period'];
	    				$product_object->AHid = $product['AHid'];
	    				$product_object->save();
	    			}
	    		}
	    	}
	    }
    }
}