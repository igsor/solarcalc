<?

/*
 * This is the lib include file.
 * 
 * All lib files that must be included in any case should be included here.
 * Any bootstrapping or class loading functionality should be implemented here
 * or included from here.
 * 
 */

// Common symbols.
include_once('common.php');

// Common code for modules.
include_once('modules.php');
include_once('project.php');

// Configuration search algorithms.
include_once('composition.php');
include_once('SpecialList.php');
include_once('SpecialListClasses.php');
include_once('SearchConfig.php');
include_once('ConfigurationData.php');
// EOF //
