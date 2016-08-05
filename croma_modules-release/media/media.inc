<?php
$upOne = realpath(dirname(__FILE__) . '/..');
include_once($upOne.'/includePaths.inc');
include_once(MODULES_FOLDER.'/themeCustomization/themeCustomization.inc');
define('MAX_DESCRIPTION_CHAR', 500); // maximum length for the body of the email put in description
?>