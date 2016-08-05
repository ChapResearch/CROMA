<?php

// quick check to determine if this is the release version
// normally this would be set through a define in Drupal
if ($_SERVER['SERVER_PORT'] == ':80'){
  $dbBasePath = '/var/www-croma/database-release';
  $modulesBasePath = '/var/www-croma/croma_modules-release';
} else {
  $dbBasePath = '/var/www-croma/database';
  $modulesBasePath = '/var/www-croma/croma_modules';
}

include($dbBasePath.'/croma_dbFunctions.php');
include($dbBasePath.'/drupalCompatibility.php');

if (isset($_GET['teamNumber'])){
  $TID = dbGetTeamTIDByNumber($_GET['teamNumber']);
} else {
  return;
}

$fontpath = realpath('.'); //replace . with a different directory if needed
putenv('GDFONTPATH='.$fontpath);

// General settings
$padding = 10;
$image = imagecreatefrompng($modulesBasePath.'/images/widget.png');
$font['size'] = 40;
$font['angle'] = 0;
$font['file'] = 'Serpentine.ttf';
$font['color'] = imagecolorallocate($image, 0, 0, 0);
$font['minSize'] = 15;

// "Hours" settings
$hours['text'] = dbGetHoursForTeam($TID);
$hours['pos']['x'] = 132;
$hours['pos']['y'] = 114;
$hours['boundary']['side'] = 'left';
$hours['boundary']['x'] = 34 + $padding;

// "Outreaches" settings
$outreaches['text'] = dbGetNumOutreachForTeam($TID);
$outreaches['pos']['x'] = 351;
$outreaches['pos']['y'] = 114;
$outreaches['boundary']['side'] = 'right';
$outreaches['boundary']['x'] = 446 - $padding;

ob_clean(); // ensure output is clean before adding header
header('Content-Type: image/png');

$sizeOne = calculateSize($outreaches['text'], $font, $outreaches['pos'], $outreaches['boundary']);

$sizeTwo = calculateSize($hours['text'], $font, $hours['pos'], $hours['boundary']);

// the numbers should be the same size, so find the smallest
$font['size'] = min(array($sizeOne, $sizeTwo));

// determine where the bottom left corner of the hours text should start
$hours['offset'] = calculateOffset($hours['text'], $font, $hours['pos']);

// add the text
imagettftext($image, $font['size'], $font['angle'], $hours['offset']['x'], $hours['offset']['y'], $font['color'], $font['file'], $hours['text']);

// determine where the bottom left corner of the text should start
$outreaches['offset'] = calculateOffset($outreaches['text'], $font, $outreaches['pos']);

// add the number of outreaches
imagettftext($image, $font['size'], $font['angle'], $outreaches['offset']['x'], $outreaches['offset']['y'], $font['color'], $font['file'], $outreaches['text']);

// display the final image
imagepng($image);

function calculateSize($text, $font, $pos, $boundary)
{
  // create new variables to avoid modifying arguments
  $newSize = $font['size'];
  $newFont = $font;

  while($newSize > $font['minSize']){

    // check the location when using the new size
    // note that the offset is the location of the bottom left corner
    $newFont['size'] = $newSize;
    $offset = calculateOffset($text, $newFont, $pos);

    $dimensions = getTextDimensions($text, $font);

    // if the text doesn't cross the left boundary
    if($boundary['side'] == 'left' 
       && $offset['x'] > $boundary['x']){
      return $newSize;
    }

    // if the right edge of the text doesn't cross right boundary
    if($boundary['side'] == 'right' 
       && $offset['x'] + $dimensions['width'] < $boundary['x']){
      return $newSize;
    }

    $newSize--;
  }
  return $newSize;
}

function calculateOffset($text, $font, $pos)
{
  $dimensions = getTextDimensions($text, $font);
  $offset['x'] = round($pos['x'] - $dimensions['width']/2);
  $offset['y'] = round($pos['y'] + $dimensions['height']/2);

  return $offset;
}

function getTextDimensions($text, $font)
{
  // note that all coordinates are relative to the bottom left corner
  $edges = imageftbbox($font['size'], $font['angle'], $font['file'], $text);

  $dimensions['width'] = $edges[2]; // position of bottom right corner
  $dimensions['height'] = -1 * $edges[7]; // position of top right corner

  return $dimensions;
}

?>