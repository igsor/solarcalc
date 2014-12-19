<?

/*
 * This is the main initialization file.
 * 
 * Usually, the first line in every other script is to include this file.
 * Any operation to set up the project can be put here. Like includes or
 * global definitions.
 * 
 */

/* Customize php settings */

// Debug settings
ini_set('display_errors', 'On');
ini_set('log_errors', 'Off'); 
//ini_set('error_reporting', 'E_ALL | E_STRICT');
ini_set('display_startup_errors', 'On');
ini_set('track_errors', 'On');
ini_set('html_errors', 'On');
ini_set('safe_mode_gid', 'On'); // Only group must match

// Security settings
ini_set('magic_quotes_gpc', 'Off');
ini_set('safe_mode', 'On');

// Code style enforcement
ini_set('short_open_tag', 'Off');
ini_set('allow_call_time_pass_reference', 'Off');

/* Includes */

// Global config.
require_once('config.php');

// Include library code.
require_once('lib/include.php');

// Include templates.
require_once('templates/include.php');

// EOF //
