<?php

// Set the local timezone, if not set
if(empty(ini_get('date.timezone'))){
	date_default_timezone_set('UTC');
}

// Make the application root available in a globally defined constant
const APP_ROOT = __DIR__ . '/';
const PACKAGE_ROOT = APP_ROOT . '../';
const VENDOR_ROOT = PACKAGE_ROOT . 'vendor/';

// Use the composer autoloader
require VENDOR_ROOT . 'autoload.php';

// Setup our autoloader for library classes
spl_autoload_register(function($class_name){

	// Project Namespace (PSR-4)
	$prefix = 'Triage\\';

	// Location of our classes
	$base_directory = APP_ROOT . '/code/';

	// Check if the class_name begins with our prefix
	if(0 !== strpos($class_name, $prefix)){
		// It doesn't so don't handle this autoload request
		return;
	}

	// Get the class name relative to our prefix
	$relative_class = substr($class_name, strlen($prefix));

	// Replace the prefix with the base directory
	// Replace the namespace separator with the directory separator
	// Append .php
	$file = $base_directory . str_replace('\\', '/', $relative_class) . '.php';

	// If the file exists, require it
	if(file_exists($file)){
		require $file;
	}

});
