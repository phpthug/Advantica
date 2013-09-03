<?php

namespace Advantica\Service;

interface GeocodeInterface {
	/**
	* Get the geolocation information for a given address
	* 
	* @var mixed $address
	*
	* @return mixed 
	*/
	public function getGeoInfo($address);
}

class Geocode implements GeocodeInterface {

	/**
	* processResult
	*
	* @var string $result
	*
	* @return Advantica\Model\Point
	* @throws \Exception
	*/
	public function processResult($result) {
		if ($result->statusCode === 200 && count($result->resourceSets[0]->resources)) {
			$coordinates = $result->resourceSets[0]->resources[0]->geocodePoints[0]->coordinates;
			return new Model\Point($coordinates[0], $coordinates[1]);
			
		} else {
			throw new \Exception("Unable to retrieve address information");
		}
	}

	/**
	* @see Advantica\Service\GeocodeInterface
	*/
	public function getGeoInfo($address) {
		$ch = curl_init();
		$url = \Advantica\Config\Config::$geoCacheBaseUrl . rawurlencode($address->__toString()) . '?o=json&key=' . \Advantica\Config\Config::$bingMapsKey;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = json_decode(curl_exec($ch));
		return $result;
	}

	/**
	* Get the distance between two points over a sphere (such as the earth)
	*
	* @var Advantica\Model\Point $point1 The first point
	* @var Advantica\Model\Point $point2 The second point
	*
	* @return float
	*/
	public function getDistance(\Advantica\Model\Point $point1, \Advantica\Model\Point $point2) {
		$radius = \Advantica\Config\config::$radius;
		$dLat = deg2rad($point2->getLatitude() - $point1->getLatitude());
		$dLng = deg2rad($point2->getLongitude() - $point1->getLongitude());
		$lat1 = deg2rad($point1->getLatitude());
		$lat2 = deg2rad($point2->getLatitude());

		$a = sin($dLat/2) * sin($dLat/2) + sin($dLng/2) * sin($dLng/2) * cos($lat2);
		$c = 2 * atan2(sqrt($a), sqrt(1-$a));

		return $radius * $c;
	}
}