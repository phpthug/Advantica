<?php
namespace Advantica\Controller;

use Advantica\Model;
use Advantica\Factory;

/**
* Class Provider
*
* @package Advanctica\Controller
* @author  Kenneth Howe <knnth.howe@gmail.com>
*/
class Controller {

	private $conn;
	private $geoCodeService;

	/**
	* Constructor
	*
	* Create new controller instances
	*
	* @var \PDO            $conn           PDO connection
	* @var Service\Geocode $geoCodeService Geocode service that implements third party API
	*/
	public function __construct(\PDO $conn, \Advantica\Service\Geocode $geoCodeService) {
		$this->conn = $conn;
		$this->geoCodeService = $geoCodeService;
	}

	public function debug() {

	}
	
	public function updatePhoneNumbers() {

		$phoneNumberField = 10;
		$id = 1;

		if (($handle = fopen("test.csv", "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
		    	if($id == 1) {
		    		$id++;
		    		continue;
		    	}
		    	$phone = $data[$phoneNumberField];
		    	$update = $this->conn->prepare('UPDATE provider SET phone=? WHERE id=?');
				$update->execute([$phone, $id]);
		        $id++;
		    }
		    fclose($handle);
		}
	}

	private function processObject(array $arr, $type) {
		$name = new Model\Name($arr['first_name'], $arr['last_name']);
		unset($arr['first_name']);
		unset($arr['last_name']);

		$address = new Model\Address($arr['address_1'], $arr['address_2'], $arr['city'], $arr['state'], $arr['zip_code'], new Model\Point($arr['latitude'], $arr['longitude']));

		unset($arr['address_1']);
		unset($arr['address_2']);
		unset($arr['city']);
		unset($arr['state']);
		unset($arr['zip_code']);
		unset($arr['latitude']);
		unset($arr['longitude']);

		$arr['address'] = $address;
		$arr['name'] = $name;

		return Factory\AdvanticaFactory::create($arr, $type);
	}

	/**
	* Generates a CSV with all contacts that have valid latitude and longitude (non-zero) and finds the three closest locations.
	*/
	public function generateCSV() {

		$customers = $providers = [];
		$filename = './'.\Advantica\Config\Config::$filename;

		$fh = fopen($filename, 'w+');

		$headers = "customer_first\tcustomer_last\tcustomer_address_1\tcustomer_address_2\tcustomer_city\tcustomer_state\tcustomer_zip_code\t";

		for($i = 1; $i < 4; $i++) {
			$headers .= "provider_{$i}_distance\tprovider_{$i}_practice_name\tprovider_{$i}_address_1\tprovider_{$i}_address_2\tprovider_{$i}_city\tprovider_{$i}_state\tprovider_{$i}_zip_code\tprovider_{$i}_phone\t";
		}

		$headers = substr($headers, 0, -1) . "\r\n";

		fwrite($fh, $headers);

		$statement = $this->conn->prepare('SELECT * FROM mail WHERE latitude != ?');
		$statement->execute([0]);
		$tmpCustomers = $statement->fetchAll(\PDO::FETCH_ASSOC);

		$statement = $this->conn->prepare('SELECT * FROM provider WHERE latitude != ?');
		$statement->execute([0]);
		$tmpProviders = $statement->fetchAll(\PDO::FETCH_ASSOC);

		foreach($tmpProviders as $provider) {
			$providers[$provider['id']] = $this->processObject($provider, 'Provider');
		}

		$customersMap = [];

		foreach($tmpCustomers as $customer) {
			$customers[$customer['id']] = $this->processObject($customer, 'Customer');
			$customersMap[$customer['id']] = [];
			foreach($providers as $provider) {
				$customersMap[$customer['id']][$provider->getId()] = $this->geoCodeService->getDistance($customers[$customer['id']]->getAddress()->getPoint(), $provider->getAddress()->getPoint());
			}
			$customersMap[$customer['id']] = array_unique($customersMap[$customer['id']]);
			asort($customersMap[$customer['id']]);
			$customersMap[$customer['id']] = array_slice($customersMap[$customer['id']], 0, 3, true);
		}

		foreach($customersMap as $customer => $inRangeProviders) {
			$tmp = $customers[$customer];
			$customerString = "{$tmp->getName()->getFirstName()}\t{$tmp->getName()->getLastName()}\t{$tmp->getAddress()->getStreet()}\t{$tmp->getAddress()->getSuite()}\t{$tmp->getAddress()->getCity()}\t{$tmp->getAddress()->getState()}\t{$tmp->getAddress()->getZipCode()}\t";
			$providerString = "";
			fwrite($fh, $customerString);
			foreach($inRangeProviders as $provider => $distance) {
				$tmp = $providers[$provider];
				$providerString .= "{$distance}\t{$tmp->getPracticeName()}\t{$tmp->getAddress()->getStreet()}\t{$tmp->getAddress()->getSuite()}\t{$tmp->getAddress()->getCity()}\t{$tmp->getAddress()->getState()}\t{$tmp->getAddress()->getZipCode()}\t{$tmp->getPhone()}\t";
			}
			fwrite($fh, substr($providerString, 0, -1));
			fwrite($fh, "\r\n");
		}

		if (file_exists($filename)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($filename));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($filename));
			ob_clean();
			flush();
			readfile($filename);
			die();
		}
	}

	/**
	* Creates and stores the Point for each provider
	*/
	public function generateProviderPoints() {
		$statement = $this->conn->prepare('SELECT * FROM provider');
		$statement->execute();
		$providers = $statement->fetchAll();

		foreach($providers as $customer) {
			$address = new Model\Address($customer['address_1'], $customer['address_2'], $customer['city'], $customer['state'], $customer['zip_code'], new Model\Point($customer['latitude'], $customer['longitude']));

			if ((int) $address->getPoint()->getLatitude() === 0 || (int) $address->getPoint()->getLongitude() === 0) {
				$result = $this->geoCodeService->getGeoInfo($address);
				try {
					$point = $this->geoCodeService->processResult($result);

					$address->setPoint($point);

					$update = $this->conn->prepare('UPDATE provider SET latitude=?, longitude=? WHERE id=?');
					$update->execute([$address->getPoint()->getLatitude(), $address->getPoint()->getLongitude(), $customer['id']]);
				} catch (\Exception $e) {
					echo 'Exception encountered for ' . $customer['id'] . ': ' . $e->getMessage();
				}
				
			}
		}
	}

	/**
	* Creates and stores the Point for each contact
	*/
	public function generateMailPoints() {
		$statement = $this->conn->prepare('SELECT * FROM mail');
		$statement->execute();
		$customers = $statement->fetchAll();
		foreach ($customers as $customer) {
			$address = new Model\Address($customer['address_1'], $customer['address_2'], $customer['city'], $customer['state'], $customer['zip_code'], new Model\Point($customer['latitude'], $customer['longitude']));
			if ((int) $address->getPoint()->getLatitude() === 0 || (int) $address->getPoint()->getLongitude() === 0) {
				$result = $this->geoCodeService->getGeoInfo($address);
				try {
					$point = $this->geoCodeService->processResult($result);
					$address->setPoint($point);
					$update = $this->conn->prepare('UPDATE mail SET latitude=?, longitude=? WHERE id=?');
					$update->execute([$address->getPoint()->getLatitude(), $address->getPoint()->getLongitude(), $customer['id']]);
				} catch (\Exception $e) {
					echo 'Exception encountered for ' . $customer['id'] . ': ' . $e->getMessage();
				}
			}
		}
	}

}