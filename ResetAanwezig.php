<?php
include('C:/Config/inout.php');

// mysql_select_db("inout", $con);
// $query = "UPDATE bord SET isIn=0 WHERE isIn=1";
// $sql = mysql_query($query) or die ( mysql_error( ) );
// mysql_close($con);

$mysqli = mysqli_connect($hostname, $username, $password, $dbname );
if (!$mysqli)
{
    die('Could not connect: ' . mysqli_error());
}

$query = "UPDATE bord SET isIn=0 WHERE isIn=1";
$res1 = mysqli_query($mysqli, $query);

mysqli_close($mysqli);

?>