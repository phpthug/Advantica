<?php

/**
* Bootstrap for Advantica mail-merge application
*/
use \Advantica\Model;
use \Advantica\Service;
use \Advantica\Controller;

require_once 'Autoloader.php';

set_time_limit(0);
$start = microtime(true);
try {
	$conn = new \PDO('mysql:host=localhost;dbname=' . \Advantica\Config\config::$dbName, 'root', '');	
} catch (\Exception $e) {
	echo "Unable to connect to DB: " . $e->getMessage();
	die;
}

$controller = new Controller\Controller($conn, new Service\Geocode);

switch($_GET['route']) {
	case 'generateMailPoints':
		$controller->generateMailPoints();
		break;
	case 'generateProviderPoints':
		$controller->generateProviderPoints();
		break;
	case 'generateCsv':
		$controller->generateCSV();
		break;
	case 'updatePhoneNumbers':
		$controller->updatePhoneNumbers();
		break;
	default:
		$controller->debug();
		break;
}

echo "Total Execution Time: " . (microtime(true) - $start) . " seconds";

?>
<script type="text/javascript" src="terminal.js"></script>