<?php

namespace Advantica\Model;

/**
* Class Name
*
* @package Advanctica\Model
* @author  Kenneth Howe <knnth.howe@gmail.com>
*/

class Addressee {
	protected $name;
	protected $address;

	public function setAddress(Address $address) {
		$this->address = $address;

		return $this;
	}

	public function getAddress() {
		return $this->address;
	}

	public function setName(Name $name) {
		$this->name = $name;

		return $this;
	}

	public function getName() {
		return $this->name;
	}
}

class Name {
	private $firstName;
	private $lastName;

	public function __construct($firstName, $lastName) {
		$this->firstName = $firstName;
		$this->lastName = $lastName;
	}

	public function setFirstName($firstName) {
		$this->firstName = $firstName;

		return $this;
	}

	public function setLastName($lastName) {
		$this->lastName = $lastName;

		return $this;
	}

	public function getFirstName() {
		return $this->firstName;
	}

	public function getLastName() {
		return $this->lastName;
	}
}

/**
* Class Address
*
* @package Advanctica\Model
* @author  Kenneth Howe <knnth.howe@gmail.com>
*/
class Address {
	private $street;
	private $suite;
	private $city;
	private $state;
	private $zipCode;
	private $point;

	public function __construct($street, $suite, $city, $state, $zipCode, Point $point) {
		$this->street = $street;
		$this->suite = $suite;
		$this->city = $city;
		$this->state = $state;
		$this->zipCode =  preg_replace("/^(\d{5})(\d{1,})$/", "$1-$2", $zipCode);
		$this->point = $point;
	}

	public function setStreet($street) {
		$this->street = $street;

		return $this;
	}

	public function getStreet() {
		return $this->street;
	}

	public function setSuite($suite) {
		$this->suite = $suite;
	}

	public function getSuite() {
		return $this->suite;
	}

	public function setCity($city) {
		$this->city = $city;

		return $this;
	}

	public function getCity() {
		return $this->city;
	}

	public function setState($state) {
		$this->state = $state;

		return $this;
	}

	public function getState() {
		return $this->state;
	}

	public function setZipCode($zipCode) {
		$this->zipCode = $zipCode;

		return $this;
	}

	public function getZipCode() {
		return $this->zipCode;
	}

	public function setPoint(Point $point) {
		$this->point = $point;

		return $this;
	}

	public function getPoint() {
		return $this->point;
	}

	public function __toString() {
		return $this->street . ' ' . $this->state . ', ' . $this->zipCode;
	}
}

/**
* Class Point
*
* Represents a latitude/longitude point
*
* @package Advanctica\Model
* @author  Kenneth Howe <knnth.howe@gmail.com>
*/
class Point {
	private $latitude;
	private $longitude;

	public function __construct($latitude = null, $longitude = null) {
		$this->latitude = $latitude;
		$this->longitude = $longitude;
	}

	public function setLatitude($latitude) {
		$this->latitude = $latidude;

		return $this;
	}

	public function setLongitude($longitude) {
		$this->longitude = $longitue;

		return $this;
	}

	public function getLatitude() {
		return $this->latitude;
	}

	public function getLongitude() {
		return $this->longitude;
	}
}

/**
* Class Provider
*
* @package Advanctica\Model
* @author  Kenneth Howe <knnth.howe@gmail.com>
*/
class Provider extends Addressee {
	private $id;
	private $county;
	private $npi;
	private $practiceName;
	private $phone;
	private $fax;
	private $specialty;
	private $medicareId;
	private $medicaidId;
	private $par;

	public function setId($id) {
		$this->id = $id;

		return $this;
	}

	public function getId() {
		return $this->id;
	}

	public function setCounty($county) {
		$this->county = $county;

		return $this;
	}

	public function getCounty() {
		return $this->county;
	}

	public function setName(Name $name) {
		$this->name = $name;

		return $this;
	}

	public function getName() {
		return $this->name;
	}

	public function setNpi($npi) {
		$this->npi = $npi;

		return $this;
	}

	public function getNpi() {
		return $this->npi;
	}

	public function setPracticeName($practiceName) {
		$this->practiceName = $practiceName;

		return $this;
	}

	public function getPracticeName() {
		return $this->practiceName;
	}

	public function setAddress(Address $address) {
		$this->address = $address;

		return $this;
	}

	public function getAddress() {
		return $this->address;
	}

	public function setPhone($phone) {
		$this->phone = $phone;

		return $this;
	}

	public function getPhone() {
		return $this->phone;
	}

	public function setFax($fax) {
		$this->fax = $fax;

		return $this;
	}

	public function getFax() {
		return $this->fax;
	}

	public function setSpecialty($specialty) {
		$this->specialty = $specialty;

		return $this;
	}

	public function getSpecialty() {
		return $this->specialty;
	}

	public function setMedicareId($medicareId) {
		$this->medicareId = $medicareId;

		return $this;
	}

	public function getMedicareId() {
		return $this->medicareId;
	}

	public function setMedicaidId($medicaidId) {
		$this->medicaidId = $medicaidId;

		return $this;
	}

	public function getMedicaidId() {
		return $this->medicaidId;
	}
}

/**
* Class Customer
*
* @package Advanctica\Model
* @author  Kenneth Howe <knnth.howe@gmail.com>
*/
class Customer extends Addressee {
	private $id;

	public function setId($id) {
		$this->id = $id;

		return $this;
	}

	public function getId() {
		return $this->id;
	}

	public function setName($name) {
		$this->name = $name;

		return $this;
	}

	public function getName() {
		return $this->name;
	}
}