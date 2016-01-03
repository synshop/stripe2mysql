<?php

$s2m = new strip2mysql();

// verify() currently die()s if there's any errors
$s2m->verify();

$subscribers = $s2m->getSubscribers();

foreach($subscribers as $key => $value){
	print "$key => $value\n";
}


class strip2mysql{

	// TODO - refactor to return error object instead of just die()


	var $s2mConfig = array();
	var $dbh = null;
	var $subscribers = array();
	var $constructError = true;

	function __construct(){
		if (is_file('config.php')) {
			require_once 'config.php';
			if (isset($s2mConfig) && is_array($s2mConfig)){
				$this->s2mConfig = $s2mConfig;
				if (isset($this->s2mConfig['api_path'])){
					require_once $this->s2mConfig['api_path']. '/init.php';
					$this->constructError = false;
				}
			}
		}
	}

	function getSubscribers(){
		// TODO write code!
		$result = array();
		\Stripe\Stripe::setApiKey($this->s2mConfig['stripe_api_key']);
		$subscribers = \Stripe\Customer::all(array("limit" => 3));
		if (is_array($subscribers)){
			foreach($subscribers as $subscriber){
				print_r($subscriber);
			}
		}
		return $result ;
	}

	function populateDB(){
		// get database handle if need be
		if ($this->dbh == null){
			$this->_initializeDbh();
		}
		// TODO write code
	}

	function verify(){
		// init
		$errorStart = 'Sorry an error occurred: ';
		$errorEnd = "\n";
		$requiredConfVars = array('api_path', 'mysql_db', 'mysql_user', 'mysql_password', 'mysql_port',
			'mysql_host', 'mysql_table', 'stripe_api_key');
		$missingConfVar = array();

		// check for config file
		if(!is_file('config.php')){
			die($errorStart . '"config.php" file not found.  Copy "config.dist.php" ' .
				'to "config.php" and edit it to have your values. Ensure config.php is in the ' .
				' path of this script. Path is: ' . get_include_path() . $errorEnd);
		} else {
			require_once 'config.php';
		}

		// check to make sure we have all the config vars
		if(!isset($s2mConfig) || !is_array($s2mConfig)){
			die($errorStart . ' "$s2mConfig" array is not defined correctly correctly in your "config.php" ' .
				'file.  See "config.dist.php" for reference' . $errorEnd);
		}
		foreach($requiredConfVars as $key){
			if (!isset($s2mConfig[$key])){
				$missingConfVar[] = $key;
			}

		}
		if (sizeof($missingConfVar) != 0){
			die($errorStart . '""$s2mConfig" is missing: ' .
				join(', ', $missingConfVar) . '. Check your "config.php" ' .
				'file and see "config.dist.php" for reference' . $errorEnd);
		} else {
			$this->s2mConfig = $s2mConfig;
		}

		// test mysql connection
		if (!defined('PDO::ATTR_DRIVER_NAME')) {
			die($errorStart . ' PDO is not enabled.  See https://secure.php.net/manual/en/pdo.installation.php' .
				$errorEnd);
		}
		$dsn = 'mysql:host=' . $this->s2mConfig['mysql_host'] . ';port=' . $this->s2mConfig['mysql_port']
			. ';charset=utf8';
			// ';dbname=' . $this->s2mConfig['mysql_db']
		$username = $this->s2mConfig['mysql_user'];
		$password = $this->s2mConfig['mysql_password'];
		try{
			$dbh = new PDO($dsn, $username, $password);
		} catch(PDOException $ex) {
			die($errorStart . ' Could not connect to the database.  Error is: ' . $ex->getMessage() . $errorEnd);
		}

		// check for database and table
		// TODO - this schema verification doesn't seem to actually work :( - it always passes
		$query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$this->s2mConfig['mysql_db']}'";
		try{
			$stmt = $dbh->query($query);
		} catch(PDOException $ex) {
			die($errorStart . ' Database error checking for db ' . $this->s2mConfig['mysql_db'] .
				'.  Error is: ' . $ex->getMessage() . $errorEnd);
		}
		if (!is_a($stmt, 'PDOStatement')){
			die($errorStart . 'Database error checking for db ' . $this->s2mConfig['mysql_db'] .
				'. PDOStatement object was not returned.' . $errorEnd);
		}
		try {
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $ex) {
			die($errorStart . ' Database error checking for db ' . $this->s2mConfig['mysql_db'] .
				'.  Error is: ' . $ex->getMessage() . $errorEnd);
		}
		if (sizeof($result) == 0){
			die($errorStart . ' Database ' . $this->s2mConfig['mysql_db'] .
				'.  doesn\'t exist! ' . $errorEnd);
			$dbh->exec("CREATE DATABASE `" . $this->s2mConfig['mysql_db'] . "`");

		}

		// check that we can include stripe API
		if(!is_file($this->s2mConfig['api_path']. '/init.php')){
			die($errorStart . 'Stripe API not find in ' . $this->s2mConfig['api_path'] . $errorEnd);
		}

		// try connecting to stripe with API secret, get one customer
		try{
			\Stripe\Stripe::setApiKey($this->s2mConfig['stripe_api_key']);
			$sampleCustomer = \Stripe\Customer::all(array("limit" => 3));
		} catch(\Stripe\Error\Card $ex) {
			die($errorStart . 'Could not connect to Stripe API with secret to fetch 1 customer as a test.  Error is: ' .
				$ex->getMessage() . $errorEnd);
		}

	}

	// TODO this code untested
	function createDb(){

		// get database handle if need be
		if ($this->dbh == null){
			$this->_initializeDbh();
		}

		// create table
		$sql ="CREATE table " . $s2mConfig['mysql_table'] . "(
				ID INT( 11 ) AUTO_INCREMENT PRIMARY KEY,
				stripe_id int( 11 ) NOT NULL,
				username VARCHAR( 250 ) NOT NULL,
				email VARCHAR( 250 ) NOT NULL,
				full_name VARCHAR( 250 ) NOT NULL);" ;
		try {
			$dbh->exec($sql);
		} catch(PDOException $ex) {
			die($errorStart . ' Database error creating table ' . $s2mConfig['mysql_table'] .
				'.  Error is: ' . $ex->getMessage() . $errorEnd);
		}
	}

	function _initializeDbh(){
		$dsn = 'mysql:host=' . $s2mConfig['mysql_host'] . ';port=' . $s2mConfig['mysql_port']
			. ';charset=utf8';
		// ';dbname=' . $s2mConfig['mysql_db']
		$username = $s2mConfig['mysql_user'];
		$password = $s2mConfig['mysql_password'];
		try{
			$dbh = new PDO($dsn, $username, $password);
		} catch(PDOException $ex) {
			die($errorStart . ' Could not connect to the database.  Error is: ' . $ex->getMessage() . $errorEnd);
		}

		$this->dbh = $dbh;
	}
}