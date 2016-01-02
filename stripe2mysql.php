<?php

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
	die($errorStart . ' "$s2mConfig" is not defined correctly.  Check your "config.php" ' .
		'file and see "config.dist.php" for reference' . $errorEnd);
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
}

// test mysql connection
if (!defined('PDO::ATTR_DRIVER_NAME')) {
	die($errorStart . ' PDO is not enabled.  See https://secure.php.net/manual/en/pdo.installation.php' .
		$errorEnd);
}
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

// check for database and table
// TODO - check for DB virst
$query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$s2mConfig['mysql_table']}'";
try{
	$stmt = $dbh->query($query);
} catch(PDOException $ex) {
	die($errorStart . ' Database error checking for table ' . $s2mConfig['mysql_table'] .
		'.  Error is: ' . $ex->getMessage() . $errorEnd);
}
if (!is_a($stmt, 'PDOStatement')){
	die($errorStart . 'Database error checking for table ' . $s2mConfig['mysql_table'] .
		'. PDOStatement object was not returned.' . $errorEnd);
}
try {
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $ex) {
	die($errorStart . ' Database error checking for table ' . $s2mConfig['mysql_table'] .
		'.  Error is: ' . $ex->getMessage() . $errorEnd);
}
if (sizeof($result) == 0){

	// create table here
	// TODO - not create here?  just verify not present?
	$sql ="CREATE table " . $s2mConfig['mysql_table'] . "(
     ID INT( 11 ) AUTO_INCREMENT PRIMARY KEY,
     stripe_id int( 11 ) NOT NULL,
     username VARCHAR( 250 ) NOT NULL,
     email VARCHAR( 250 ) NOT NULL,
     full_name VARCHAR( 250 ) NOT NULL);" ;
	try {
		$dbh->exec($sql);
	} catch(PDOException $ex) {
		die($errorStart . ' Database error creating table ' . $s2mConfig['mysql_db'] .
			'.  Error is: ' . $ex->getMessage() . $errorEnd);
	}
}

// TODO - you know, everything ;)