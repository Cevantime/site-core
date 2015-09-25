<?php
define('BASEPATH', 'toto');
$db = array();
putenv('LANG=fr_FR.UTF-8');
putenv('APPLICATION_ENV=default');

require_once __DIR__.'/../../application/config/database.php';

$liquibaseJarFile = __DIR__.'/liquibase.jar';
$mysqlConnectorPath = __DIR__.'/mysql-connector-java-5.1.16.jar';
$changelogFile = __DIR__.'/changeLog.sql';
$db_active = $db[$active_group];

$username = $db_active['username'];
$password = $db_active['password'];
$dbhost = $db_active['hostname'];
$dbname = $db_active['database'];
$output = array();
echo '... updating database ...';
exec("java -jar $liquibaseJarFile --classpath=$mysqlConnectorPath --changeLogFile=$changelogFile --username=$username --password=$password --logLevel=info --url=jdbc:mysql://$dbhost/$dbname update 2>&1", $output);

foreach ($output as $out){
	echo "$out\n";
}

