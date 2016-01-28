<?php

namespace App;

use Carbon\Carbon;
use DOMDocument;
use DOMXPath;

class Functions
{
	public function findWeek($string) {
		$result = $this->between('week/', '?', $string);
		if ( strlen($result) == 0 ) {
			$result = $this->between('week/', '/', $string);
		}
		return intval($result);
	}

	public function before($first, $inthat)
	{
   		return substr($inthat, 0, strpos($inthat, $first));
	}
	
	public function before_last ($first, $inthat)
	{
		return substr($inthat, 0, $this->strrevpos($inthat, $first));
	}

	public function after($first, $inthat)
	{
		if (!is_bool(strpos($inthat, $first))) {
			return substr($inthat, strpos($inthat,$first)+strlen($first));
		}
	}

	public function after_last ($first, $inthat)
	{
		if (!is_bool($this->strrevpos($inthat, $first))) {
			return substr($inthat, $this->strrevpos($inthat, $first)+strlen($first));
		}
	}

	public function between($first, $that, $inthat)
	{
		return $this->before ($that, $this->after($first, $inthat));
	}

	public function between_last ($first, $that, $inthat)
	{
		return $this->after_last($first, $this->before_last($that, $inthat));
	}

	public function strrevpos($instr, $needle)
	{
		$rev_pos = strpos (strrev($instr), strrev($needle));
		if ($rev_pos===false) {
			return false;
		} else {
			return strlen($instr) - $rev_pos - strlen($needle);
		}
	}
}