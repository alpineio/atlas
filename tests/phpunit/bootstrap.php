<?php

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

require __DIR__ . '/../../vendor/autoload.php';

use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Set The Default Timezone
|--------------------------------------------------------------------------
|
| Here we will set the default timezone for PHP. PHP is notoriously mean
| if the timezone is not explicitly set. This will be used by each of
| the PHP date and date-time functions throughout the application.
|
*/

date_default_timezone_set( 'UTC' );

Carbon::setTestNow( Carbon::now() );
$settings = [
	'DB_NAME' => 'test',
	'DB_USER' => getenv('MYSQL_USER') ?: 'root',
	'DB_PASSWORD' => getenv('MYSQL_PASSWORD') ?: 'secret',
	'DB_HOST' => '127.0.0.1',
	'prefix' => 'wptests_',
];

if ( ! getenv('CI')) {
	$settings['DB_HOST'] = '127.0.0.1:33061';
}

// disable xdebug backtrace
if ( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}
// TODO Test to see if config exists and if not create it
//$configFilePath = __DIR__ . '/../../vendor/alpineio/wp-develop/tests/wp-tests-config.php';
$configFilePath = __DIR__ . '/../../vendor/alpineio/wp-develop/wp-tests-config.php';
unlink( $configFilePath );

if ( ! is_readable( $configFilePath ) ) {

	$sampleConfigFilePath = __DIR__ . '/../../vendor/alpineio/wp-develop/wp-tests-config-sample.php';
	if ( file_exists( $sampleConfigFilePath ) ) {
		$configFile = file( $sampleConfigFilePath );
	} else {
		die( 'Sorry, I need a wp-test-config-sample.php file to work from. Please re-install or update composer package.' );
	}

	foreach ( $configFile as $lineNumber => $line ) {
		if ( '$table_prefix  =' == substr( $line, 0, 16 ) ) {
			$configFile[ $lineNumber ] = '$table_prefix  = \'' . addcslashes( $settings['prefix'], "\\'" ) . "';\r\n";
			continue;
		}
		
		if ( ! preg_match( '/^define\(\s\'([A-Z_]+)\',([ ]+)/', $line, $match ) )
			continue;
		
		$constant = $match[1];
		$padding  = $match[2];
		switch ( $constant ) {
			case 'DB_NAME'     :
			case 'DB_USER'     :
			case 'DB_PASSWORD' :
			case 'DB_HOST'     :
				$configFile[ $lineNumber ] = "define( '" . $constant . "'," . $padding . "'" . addcslashes( $settings[$constant] , "\\'" ) . "');\r\n";
				break;
		}	
	}
	$handle = fopen( $configFilePath, 'w' );
	foreach ( $configFile as $line ) {
		fwrite( $handle, $line );
	}
	fclose( $handle );
	chmod( $configFilePath, 0666 );

}
//var_dump($configFile);

require __DIR__ . '/../../vendor/alpineio/wp-develop/tests/phpunit/includes/bootstrap.php';

