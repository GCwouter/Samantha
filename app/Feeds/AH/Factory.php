<?php

namespace App\Feeds\AH;

use Carbon\Carbon;
use DOMDocument;
use DOMXPath;

class Factory
{
    public function process($dataset)
    {
    	foreach ($dataset as $data) {
	    	$raw = json_decode($data, true);
	    	$lanes = $raw['_embedded']['lanes'];

	    	foreach ($lanes as $lane) {
	    		$categories = $lane['_embedded']['items'];
	    		foreach ($categories as $categorie) {
	    			if ($categorie['resourceType'] == 'Product') {
	    				$productLabel = $categorie['_embedded']['productCard'];
	    				//var_dump($productLabel);
	    				$product = [];
	    				$product['categorie'] = $productLabel['label'];
	    				$product['name'] = $productLabel['navItem']['title'];
	    				$productCard = $productLabel['_embedded']['product'];
	    				//var_dump($productCard);
	    				$product['unitSize'] = $productCard['unitSize'];
	    				$product['price']['orginalPrice'] = $productCard['priceLabel']['was'];
	    				$product['price']['actionPrice'] = $productCard['priceLabel']['now'];
	    				$product['period'] = $productCard['discount']['period'];
	    				$product['AHid'] = $productCard['id'];
	    				//dd($categorie);
	    				var_dump($product);
	    				dd('doei');
	    			}
	    		}
	    	}
	    }
    }
}