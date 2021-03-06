<?php

/*
------------------------------------------ drupalCompatibility.php ------------------------------------------------
This file is designed to be included to allow drupal-style database calls to work outside of Drupal. The functions are designed to fulfill only ourown (limited) use of the Drupal API and are intended for debugging purposes only.
 */

include_once("dbFunctions.php");

/* result - defines the class to be returned by db calls; can be used for to call a variety of functions in the OOP style. For example, db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);
 */
class result
{
  private $result;

  public function __construct($r)
  {
    $this->result = $r;
  }

  public function fetchAll($mode)
  {
    if ($mode == 2){
      $returnArray = array();
      while ($row = mysqli_fetch_assoc($this->result)) {
	$returnArray[] = $row;
      }
      return $returnArray;
    } else {
      echo "add PDO::FETCH_ASSOC";
    }
  }

}

/* db_query() - a simply function to imitate the Drupal-defined function of the same name. Designed to allow Drupal-oriented code to be written and tested outside of Drupal.
 */
function db_query($sql, $proxyFields)
{
  $con = dbConnect();

  if($con == null) {
    return(null);
  }

  foreach(array_keys($proxyFields) as $key){
    $value = mysqli_real_escape_string($con, $proxyFields[$key]);
    if (is_string($value)){
      $value = "\"$value\"";
    }
    $sql = str_replace($key, $value, $sql);
  }

  $result = mysqli_query($con, $sql);

  if (!$result){
    echo "has error";
    print_r(debug_backtrace());
    $firstCall = end(debug_backtrace());
    $errMsg = "Error calling " . $firstCall["function"];
    $errMsg .= " on line " . $firstCall["line"];
    $errMsg .= " in " . $firstCall["file"] . ": " . mysqli_error($con);
    dbErrorMsg($errMsg);
    return false;
  } else {
    if (strpos($sql, "INSERT") !== false){
      return mysqli_insert_id($con);
    } else {
      return(new result($result));
    }
  }

  dbClose($con);

}

?>