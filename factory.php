<?php
namespace Advantica\Factory;

use Advantica\Model;

class AdvanticaFactory {

	const MODEL_NAMESPACE = '\Advantica\Model\\';
	
	/**
	* Create an object from an associative array of key=>value
	*
	* @var array  $arr  Associative array
	* @var string $type The type of object to create
	*
	* @return mixed
	*/
	public static function create(array $arr, $type) {
		$className = self::MODEL_NAMESPACE . $type;
		$obj = new $className;

		foreach($arr as $key => $value) {
			if(method_exists($obj, 'set' . str_replace('_','', $key))) {
				call_user_func([$obj, 'set' . str_replace('_','', $key)], $value);
			}
		}

		return $obj;
	}

}