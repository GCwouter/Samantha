<?php

namespace App;

use Carbon\Carbon;
use DOMDocument;
use DOMXPath;

class ScrapeFunctions
{
    public function saveToDbFlights($flight_data) 
	{
		if ($flight_data['flightCode'] !== NULL) {
				if ($flight_data['arrival'] == true) {
						$flight_object = Flight::airlineCode($flight_data['airlineCode'])
								->flightNumber($flight_data['flightNumber'])
								->arrivalDate(Carbon::parse($flight_data['scheduledArrival'])->toDateString())->first();
				} elseif ($flight_data['arrival'] == false) {
						$flight_object = Flight::airlineCode($flight_data['airlineCode'])
								->flightNumber($flight_data['flightNumber'])
								->departureDate(Carbon::parse($flight_data['scheduledDeparture'])->toDateString())->first();
				}
				if ( is_null($flight_object) ) {
					$flight_object = new Flight;
				}
				$flight_object->flightCode 			= $flight_data['flightCode'];
				$flight_object->airlineCode 		= $flight_data['airlineCode'];
				$flight_object->flightNumber 		= $flight_data['flightNumber'];
				$flight_object->airline_id 			= $flight_data['airline_id'];
				$flight_object->operatedBy 			= $flight_data['operatedBy'];
				$flight_object->departureAirport_id = $flight_data['departureAirport_id'];
				$flight_object->arrivalDatetime 	= $flight_data['scheduledArrival'];
				$flight_object->departureDatetime 	= $flight_data['scheduledDeparture'];
				$flight_object->arrivalAirport_id 	= $flight_data['arrivalAirport_id'];
				$flight_object->save();
		}
		return($flight_object);
	}

    public function domParser($raw)
	{
		$dom = new DOMDocument();
		if ( !is_object($raw) ) {
			@$dom->loadHTML($raw);
		} else {
			@$dom->appendChild($dom->importNode($raw,true));
		}
		$result = new DOMXPath($dom);
		return($result);
	}

	public function findTime($date, $raw)
	{
		if ( preg_match('/(?:[01][0-9]|2[0-3]):[0-5][0-9]/', $raw) ) {
			$time = preg_replace('/[a-zA-Z]/', '', $raw);
			$time = Carbon::parse($date . $time)->toDateTimeString();
		} else {
			$time = '0000-00-00 00:00:00';
		}
		return $time;
	}

	public function determineActualDate($scheduled, $actual)
	{
		if ( $actual !== '0000-00-00 00:00:00' ) {
			$scheduled = Carbon::parse($scheduled);
			$actual = Carbon::parse($actual);
			$endDay = Carbon::parse($scheduled)->endOfDay();
			if ( !Carbon::parse($actual)->between($scheduled, $endDay) ) {
				$result = Carbon::parse($actual)->addDay(1)->toDateTimeString();
			} else {
				$result = Carbon::parse($actual)->toDateTimeString();
			}
		} else {
			$result = $actual;
		}
		return $result;
	}

	public function getOperator($flight) 
	{
		if ($flight['aOrD'] == 'Arrivals') {
			$operator = LED_Flight::where(['scheduledArrival' => $flight['scheduledArrival'],
												'departureAirport_id' => $flight['departureAirport_id']])->first();
		} else {
			$operator = LED_Flight::where(['scheduledDeparture' => $flight['scheduledDeparture'],
												'arrivalAirport_id' => $flight['arrivalAirport_id']])->first();
		}
		if ($operator != null) {
			return $operator->airlineCode;
		} else {
			return $flight['airlineCode'];
		}
	}

	public function getAirline($carrierCode)
	{
		$airline = Airline::where('iata', $carrierCode)->first();
		if ( is_null($airline) ) {
			$airline = Airline::create(['iata' => $carrierCode]);
		}
		return $airline->id;
	}

    public function extractFlightCode($flightCode) 
	{
		$flightCode = $this->russianToWestern($flightCode);
		$flightCode = $this->cleanText($flightCode);
		$flightCode = $this->removeHtmlSpace($flightCode);
		$airline = $this->extractAirline($flightCode)[0];
		$airline_id = $this->getAirline($airline);
		$flightNumber = $this->extractAirline($flightCode)[1];
		$flightCode = $airline.$flightNumber;
		return [$flightCode, $airline, $airline_id, $flightNumber];
	}

	public function russianToWestern($text) {
		$code = [
                'ЛП' => 'LP',
                'ЮВ' => 'SE',
                'КБ' => 'CB'
                ];
        $newText = trim(preg_replace("/[a-z0-9]/","",$text));
        $number = substr($text, -4);
        if (array_key_exists($newText, $code)) {
            $iata = $code[$newText] . $number;
        } else {
            $iata = $text;
        }
        return $iata;
	}

	public function extractAirline($flightCode)
	{
		if ( strlen(preg_replace("/[^A-z]+/", "", $flightCode)) == 2 || strlen(preg_replace("/[^A-z]+/", "", $flightCode)) == 1 ) {
			$airline = substr($flightCode, 0, 2);
			$flightNumber = intval(substr($flightCode, 2));
		} else {
			$airline = substr($flightCode, 0, 3);
			$flightNumber = intval(substr($flightCode, 3));
		}
		return[$airline, $flightNumber];
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

	public function arrayTrim($array)
	{
		foreach ($array as $key) {
			$trimmed[] = trim($key);
		}
		return $trimmed;
	}

	public function cleanText($text)
	{
		$text = $this->removeNewLines($text);
		$text = $this->removeTabs($text);
		$text = $this->replaceSpaces($text);
		$text = preg_replace("/&#?[a-z0-9]+;/i","",$text);
		return trim($text);
	}

	public function removeNewLines($value)
	{
		return str_replace(array("\r\n", "\r", "\n"), "", $value);
	}

	public function removeTabs($value)
	{
		return preg_replace('/\t+/', '', $value);
	}

	public function replaceSpaces($value)
	{
		return preg_replace('/\s+/', '', $value);
	}

	public function keepOneSpace($value){
		return preg_replace("/[ ]{2,}/", " ", $value);
	}

	public function removeHtmlSpace($string){
		$string = htmlentities($string, null, 'utf-8');
		$string = str_replace("&nbsp;", "", $string);
		return trim($string);
	}
}