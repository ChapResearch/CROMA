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

/* dbGenericInsert() - used by many insert functions to insert something into a database. The incoming array must have only those fields that are valid for that database. Returns 0 upon failure, or the new key upon success.  
 */
function dbGenericInsert($row,$table)
{
  $proxyFields = array();

  $sql = "INSERT INTO $table (";

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
    if($value === null) {
      $sql .= "NULL";
    } else {
      $sql .= ":$field";
      $proxyFields[":" . $field] = $value;
    }
    $first = false;
  }
  $sql .= ");";

  try {
    db_query($sql, $proxyFields);
    return Database::getConnection()->lastInsertId();
  } catch (\PDOException $e){
    $error = $e->getMessage();
    dpm($error);
    return false;
  }
}

/* dbSimpleSelect() - selects data from the table where various idName(s) = idValue(s)
 */
function dbSimpleSelect($table, $idName, $idValue, $idName2 = null, $idValue2 = null)
{
  $proxyFields = array();

  $proxyFields[":" . $idName] = $idValue;
  if ($idName2 != null && $idValue2 != null){
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

//	DATABASE AUDIT - CHANGES ARE MADE - AUDIT NOT COMPLETE
//		The potential issue here is that the $oid could be
//		sent as a non-number by the caller (maybe exposed
//		by custom-crafting a URL).  ALSO, the incoming fields
//		are enclosed in ticks, but still need to be escaped.
//
function dbUpdate($table, $modifyFields, $idName, $idValue, $idName2 = null, $idValue2 = null)
{
  $i = 1;
  $sql = "UPDATE $table SET ";
  $proxyFields = array((":".$idName)=>$idValue);
  foreach ($modifyFields as $column => $value){
    if ($value != ""){
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

function dbRemoveEntry($table, $idName, $idValue)
{
  $sql = "DELETE FROM $table ";
  $sql .= "WHERE $idName = :$idName ";
  $sql .= "LIMIT 1;";

  $proxyFields = array((":" . $idName)=>$idValue);

  $result = db_query($sql, $proxyFields);

  return ($result != false);
}

?>