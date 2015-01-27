<?php

/*
 * This is the templates include file.
 * 
 * All template files that must be included in any case should be included here.
 * Any bootstrapping or class loading functionality should be implemented here
 * or included from here.
 * 
 */

// Layout start / end.
include_once('page.php');

// Table templates.
include_once('modules.php');
include_once('project.php');

// Measurement units.
include_once('units.php');

// Error messages.
include_once('errors.php');

// EOF //
