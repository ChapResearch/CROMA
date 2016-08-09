<?php
$currentDirectory = getcwd();

if ($currentDirectory == '/var/www-croma/drupal-7.41-release'){
  define('MODULES_FOLDER', '/var/www-croma/croma_modules-release');
  define('DATABASE_FOLDER', '/var/www-croma/database-release');
  define('PORT', ''); // port 80 is the default anyway...
  define('TYPE', 'release');
} else {
  define('MODULES_FOLDER', '/var/www-croma/croma_modules');
  define('DATABASE_FOLDER', '/var/www-croma/database');
  define('PORT', ':8080'); 
  define('TYPE', 'test');
}
?>