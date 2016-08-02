<?php

include_once("config.php");
//include_once("drupalCompatibility.php");

/* dbErrorMsg() - called to generate error messages for someone.
   Currently, just prints out the error message, which will
   have the effect of coming out on the browser.
*/
function dbErrorMsg($msg)
{
  if(function_exists("dpm")){
    dpm($msg, "DB Error");
  } else {
    print("ChapR ERP: \"$msg\"\n");
  }
}

function printMsg($msg)
{
  if(function_exists("drupal_set_message")){
    drupal_set_message($msg);
  } else {
    print($msg);
  }
}

function printErrorMsg($msg)
{
  if(function_exists("drupal_set_message")){
    drupal_set_message($msg, 'error');
  } else {
    print('ERROR: ' . $msg);
  }
}

/* dbConnect() - connect to the mysql database according to the configuration.
   Returns the mysql connect if things went OK, null otherwise and
   an error message was generated.
*/
function dbConnect()
{
  $con = mysqli_connect(DB_HOST,DB_USERNAME,DB_PASSWORD);
  if (mysqli_connect_errno()) {
    dbErrorMsg("Failed to connect to MySQL: " . mysqli_connect_error());
    return(null);
  }

  $sql="USE " . DB_DATABASE;

  if (!mysqli_query($con,$sql)) {
    dbErrorMsg("Failed to execute USE for the database: " . mysqli_error());
    dbClose($con);
    return(null);
  }

  return($con);
}

function dbClose($con)
{
  mysqli_close($con);
}

/* dbDatePHP2SQL() - convert back and forth between PHP and SQL time stamps.
   dbDateSQL2PHP()
*/
function dbDatePHP2SQL($timestamp)
{
     if($timestamp === false || $timestamp == 0) {
	  return(0);
     } else {
	  return(gmdate('Y-m-d H:i:s',$timestamp));
     }
}

function dbDateSQL2PHP($sqltime) 
{
     // dates in SQL can be 0000-00-00 00:00:00
     // and get read back as false (not zero) to php - or maybe we
     // should special case and use null as the no date value

     if($sqltime == "0000-00-00 00:00:00") {
          return(0); 
     } else {
          return(strtotime($sqltime . " GMT"));
     }
}

/* dbGenericInsert() - used by many insert functions to insert something into a database. The incoming array must have only those fields that are valid for that database. Returns 0 upon failure, or the new key upon success.  
 */
function dbGenericInsert($row,$table)
{
  $proxyFields = array();

  $sql = 'INSERT ';

  $sql .= "INTO $table (";

  $first = true;
  foreach($row as $field => $value) {
    if(!$first) {
      $sql .= ",";
    }
    $sql .= $field;
    $first = false;
  }
  $sql .= ") VALUES (";
  $first = true;
  foreach($row as $field => $value) {
    if(!$first) {
      $sql .= ",";
    }
    if($value === null || $value == '') {
      $sql .= "NULL";
    } else {
      $sql .= ":$field";
      $proxyFields[":" . $field] = $value;
    }
    $first = false;
  }
  $sql .= ") ";

  try {
    $newID = db_query($sql, $proxyFields); // this variable is meaningless within Drupal
    if (function_exists("dpm")) { // in Drupal land
      return Database::getConnection()->lastInsertId();
    } else {
      return $newID;
    }
  } catch (\PDOException $e){
    $error = $e->getMessage();
    dbErrorMsg($error);
    return false;
  }
}

/* dbSimpleSelect() - selects data from the table where various idName(s) = idValue(s)
 */
function dbSimpleSelect($table, $idName, $idValue, $idName2 = null, $idValue2 = null)
{
  $proxyFields = array();

  $proxyFields[":" . $idName] = $idValue;
  if ($idName2 !== null && $idValue2 !== null){
    $proxyFields[":" . $idName2] = $idValue2;
  }

  $sql = "SELECT * FROM $table ";
  if ($idValue !== null){
    $sql .= "WHERE $idName = :$idName";
  } else {
    $sql .= "WHERE $idName is NULL";
  }

  if ($idName2 !== null){
    if ($idValue2 !== null){
      $sql .= " AND $idName2 = :$idName2";
    } else {
      $sql .= " AND $idName2 is NULL";
    }
  }
  
  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));

}

/* dbSelect() - a more complicated form of select which allows the user to pass in an array of filter parameters and ordering values.
Ex: dbSelect("outreach", array("status"=>"isIdea", "canceled"=>"false"), array("hoursLogged"=>"ASC"));
 */
function dbSelect($table, $filterParams, $orderParams = null, $limit = null, $selectStuff = null)
{
  $proxyFields = array();

  if($selectStuff != null){
    $sql = "SELECT $selectStuff ";
  } else {
    $sql = "SELECT * ";
  }

  $sql .= "FROM $table ";

  $first = true;
  foreach ($filterParams as $filterID => $filterValue){
    $safeName = strpos($filterID,'.')?explode('.',$filterID)[1]:$filterID; // fix up name
    if ($filterID !== null){
      $proxyFields[":".$safeName] = $filterValue;
    }
    if ($filterValue !== null){
      if($first){
	$sql .= 'WHERE ';
	$first = false;
      } else {
	$sql .= 'AND ';
      }
      $sql .= "$filterID = :$safeName ";
    } else {
      if($first){
	$sql .= 'WHERE ';
	$first = false;
      } else {
	$sql .= 'AND ';
      }
      $sql .= "$filterID is NULL ";
    }
  }
  if ($orderParams != null) {
    $sql .= "ORDER BY ";
    $i = 1;
    foreach ($orderParams as $orderID => $orderType){
      $sql .= "$orderID $orderType";
      if ($i != sizeOf($orderParams)){
	$sql .= ", ";
      }
      $i++;
    }
  }
  if ($limit != null) {
    $sql .= " LIMIT $limit";
  }

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
}

/* dbUpdate() - a general function to update the given table based off the modifyFields array. $idName and $idValue can be used to narrow down which rows are affected.
 */
function dbUpdate($table, $modifyFields, $idName, $idValue, $idName2 = null, $idValue2 = null)
{
  $i = 1;
  $sql = "UPDATE $table SET ";
  $proxyFields = array((":".$idName)=>$idValue);
  foreach ($modifyFields as $column => $value){
    if ($value !== null){
      $proxyFields[":" . $column] = $value;
    } else {
      $proxyFields[":" . $column] = NULL;
    }
    $sql .= "$column = :$column";
    if ($i != sizeOf($modifyFields)){
      $sql .= ", ";
    } else {
      $sql .= " ";
    }
    $i++;
  }
  $sql .= "WHERE $idName = :$idName ";
  if ($idName2 != null && $idValue2 != null){
    $sql .= "AND $idName2 = :$idName2 ";
    $proxyFields[":" .$idName2] = $idValue2;
  } else {
    $sql .= "LIMIT 1;";
  }

  $result = db_query($sql, $proxyFields);

  return ($result != false);
}

/* dbRemoveEntry() - removes a single row from a table (given the column name and value). Note: this deletes a single row.
 */
function dbRemoveEntry($table, $idName, $idValue, $idName2 = null, $idValue2 = null)
{
  $sql = "DELETE FROM $table ";
  $sql .= "WHERE $idName = :$idName ";

  if ($idName2 != null && $idValue2 != null){
      $sql .= "AND $idName2 = :$idName2 ";
  }

  $sql .= "LIMIT 1;";

  $proxyFields = array((":" . $idName)=>$idValue);

  if ($idName2 != null){
    $proxyFields[':' . $idName2] = $idValue2;
  }

  $result = db_query($sql, $proxyFields);

  return ($result != false);
}

/* dbRemoveEntries() - used for more elaborate removal of entries. The function takes in an array of "filter params" that it will use to determine which entries to delete from the given table. The user can also specifiy the limit to the number of rows to be deleted.
 */
function dbRemoveEntries($filterParams, $table, $limit = null)
{
  $sql = "DELETE FROM $table ";

  $first = true;
  foreach($filterParams as $paramName => $paramValue){
    if ($first){ // start off qualifiers
      if ($paramValue != null){
	$sql .= "WHERE $paramName = :$paramName ";
      } else {
	$sql .= "WHERE $paramName is NULL ";
      }
      $first = false;
    }
    else { // subsequent qualifiers
      if ($paramValue != null){
	$sql .= "AND $paramName = :$paramName ";
      } else {
	$sql .= "AND $paramName is NULL ";
      }
    }
    $proxyFields[":" . $paramName] = $paramValue;
  }

  if ($limit != null){
    $sql .= "LIMIT $limit";
  }

  $result = db_query($sql, $proxyFields);

  return ($result != false);
}
?>