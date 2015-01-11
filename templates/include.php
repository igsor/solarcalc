<?

/*
 * This is the templates include file.
 * 
 * All template files that must be included in any case should be included here.
 * Any bootstrapping or class loading functionality should be implemented here
 * or included from here.
 * 
 */

// Layout start / end.
include_once('common.php');

// Table templates.
include_once('tables.php');
include_once('loadtable.php');
include_once('module_forms.php');
include_once('project.php');

// Measurement units.
include_once('units.php');

// Error messages.
include_once('errors.php');

// EOF //
