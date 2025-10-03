<html>
<head><title>InOut bord  - Bewerken</title></head>
<body>

<h2>::: Bewerken</h2>

<?php

include('C:/Config/inout.php');
$con = mysql_connect($hostname,$username,$password);
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
  
mysql_select_db($dbname, $con);

if ($_POST['submit']){
	if (($_POST['naam'] === 'Jef Jonckheere') or ($_POST['naam'] === 'Koen Hens'))
		{
		$result = mysql_query("UPDATE bord SET
							  isIn = '".(($_POST['isIn'])?1:0)."',
							  rood = '".(($_POST['rood'])?1:0)."',
							  blauw = '".(($_POST['blauw'])?1:0)."',
							  geel = '".(($_POST['geel'])?1:0)."',
							  wacht = '".(($_POST['wacht'])?1:0)."',
							  late = '".(($_POST['late'])?1:0)."',
							  nota = '".$_POST['nota']."'
							 WHERE id = '".$_GET['nummer']."'") or die(mysql_error());
		}
	else
		{
		$result = mysql_query("UPDATE bord SET
							  isIn = '".(($_POST['isIn'])?1:0)."',
							  rood = '".(($_POST['rood'])?1:0)."',
							  blauw = '".(($_POST['blauw'])?1:0)."',
							  geel = '".(($_POST['geel'])?1:0)."',
							  wacht = '".(($_POST['wacht'])?1:0)."',
							  late = '".(($_POST['late'])?1:0)."',
							  nota = '".$_POST['nota']."'
							  WHERE id = '".$_GET['nummer']."'") or die(mysql_error());
		}
	if ($result) echo 'data saved';
	header("location:toonbord.php");
}
$ID = $_GET['nummer'];
$resultaat = mysql_query("SELECT * FROM `bord` WHERE `id` = '$ID'");
$rij = mysql_fetch_array($resultaat);
if (is_array($rij)) extract($rij);

echo "<form method='post' action='".$_SERVER['REQUEST_URI']."'>";
echo "<table border=1 width='70%'>";
echo "<tr>";
echo "<td width='30%'><input type='hidden' name='naam' value='". $naam . "'>&nbsp</td>";
echo "<input type='hidden' name='nummer' value='". $ID . "'>";
echo "<td >".$naam."</td>";
echo "</tr>";
echo "<tr>";
echo "<td width='30%'>In</td>";
if ($isIn==0)
	{
	echo "<td width='70%'><input name='isIn' type='checkbox' value='1' /></td>";
 } else{
 	echo "<td width='70%'><input name='isIn' type='checkbox' value='1' CHECKED /></td>";
}


echo "</tr>";

/*
echo "<tr>";
echo "<td width='30%'>Waar</td>";

echo "<td><select name='cars'>";
echo "<option value='D'>Desguinlei</option>";
echo "<option value='V'>E. Vloorsstraat</option>";
echo "<option value='L'>Lokeren</option>";
echo "</select></td>";
echo "<td width='70%'>$waar</td>";
echo "</tr>";
*/

if ($kb==1){
echo "<tr>";
echo "<td width='30%'>Rood</td>";
if ($rood==0)
	{
	echo "<td width='70%'><input name='rood' type='checkbox' value='1' /></td>";
 } else{
 	echo "<td width='70%'><input name='rood' type='checkbox' value='1' CHECKED /></td>";
}
echo "</tr>";


echo "<tr>";
echo "<td width='30%'>Blauw</td>";
if ($blauw==0)
	{
	echo "<td width='70%'><input name='blauw' type='checkbox' value='1' /></td>";
 } else{
 	echo "<td width='70%'><input name='blauw' type='checkbox' value='1' CHECKED /></td>";
}

echo "</tr>";

echo "<tr>";
echo "<td width='30%'>Geel</td>";
if ($geel==0)
	{
	echo "<td width='70%'><input name='geel' type='checkbox' value='1' /> Toxicologie </td>";
 } else{
 	echo "<td width='70%'><input name='geel' type='checkbox' value='1' CHECKED /> Toxicologie </td>";
}

echo "</tr>";


echo "<tr>";
echo "<td width='30%'>Wacht</td>";
if ($wacht==0)
	{
	echo "<td width='70%'><input name='wacht' type='checkbox' value='1' /></td>";
 } else{
 	echo "<td width='70%'><input name='wacht' type='checkbox' value='1' CHECKED /></td>";
}


echo "</tr>";


echo "<tr>";
echo "<td width='30%'>Late</td>";
if ($late==0)
	{
	echo "<td width='70%'><input name='late' type='checkbox' value='1' /></td>";
 } else{
 	echo "<td width='70%'><input name='late' type='checkbox' value='1' CHECKED /></td>";
}

echo "</tr>";
}

echo "<tr>";
echo "<td width='30%'>Nota</td>";
echo "<td width='70%'><input name='nota' type='text' value='$nota'. /></td>";
echo "</tr>";

echo "<tr>";
echo "<td width='30%'>&nbsp</td>";
echo "<td width='70%'><input type='submit' name='submit' value='Bewaren' /></td>";
echo "</tr>";

echo "</table>";
echo "</form>";
mysql_close($con);

?>



