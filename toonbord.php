<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta content="text/html; charset=ISO-8859-1"http-equiv="content-type">
  <title>In Uit Bord</title>
  <link rel="stylesheet" type="text/css" href="css/inout.css" />
  <meta http-equiv="refresh"content="90">
</head>
<body>
<?php
error_reporting(E_ALL ^ E_DEPRECATED);
set_time_limit(0);
include('C:/Config/inout.php');
$con = mysql_connect($hostname,$username,$password);
if (!$con)
 {
  die('Could not connect: ' . mysql_error());
  }
mysql_select_db($dbname, $con);
$query = "SELECT * FROM `bord` order by tabk ";
$sql = mysql_query($query) or die ( mysql_error( ) );
$DatumUur = "Laatste update: " . date("d-m-Y H:i");
echo ('<div  class="centre">');
echo ('<div  class="titel">');
echo ('<CENTER> IN UIT BORD v2.00 - <small><a href="https://inout-test.ot.sonic.lab/help.pdf">Help</a> <a href="https://amllabbe-my.sharepoint.com/:x:/g/personal/sofie_melis_aml-lab_be/EY7RDhi_SPdBhNFFVlfMPS0ByY9gJ7Jnckb9cd3bV6Gidg?CID=AD4A6ADD-D2AE-41A5-BD1E-47ED9BD8EED4&wdLOR=c9DBC8CD2-A544-4179-B06F-8E6A2413B807">Werkplanning KB</a>');
echo (" - $DatumUur");
echo ('</SMALL></CENTER>');
echo ('</div>');
echo ('<div class="kolom">');
echo"<table border=1 cellpadding=1 cellspacing=0 width=100%><tr><th width=35%><center>Naam</center></th><th width=8%>Tel</th><th width=6%>Late</th><th width=6%>Wacht</th><th width=45%><center>Nota</center></th></tr>";
$rij=0;
while($record = mysql_fetch_object($sql)){
	$rij=$rij+1;
	if ( $record->blauw+$record->rood+$record->geel>=1  ) {
		if ($record->blauw==1) {
			$color='Aqua';
			//print $color;
			//print '<br>';
		} 
		if ($record->rood==1) {
			$color='red';
			//print $color;
			//print '<br>';
		}
		if ($record->geel==1) {
			$color='yellow';
			//print $color;
			//print '<br>';
		}
		} else{
			if ($rij%2==0){
			$color='Azure';
			} else{
			$color='ghostwhite';
			}
	}
	echo "<tr>";
	if ($record->titel==1)
		{
		if ($rij > 49)
			{
			$rij=0;
			echo"</table>";
			echo"</DIV>";
			echo ('<div class="kolom">');
			echo"<table border=1 cellpadding=1 cellspacing=0 width=100%><tr><th width=40%><center>Naam</center></th><th width=8%>Tel</th><th width=6%>Late</th><th width=6%>Wacht</th><th width=40%><center>Nota</center></th></tr>";
			}

		echo "<td colspan='5' bgcolor='#A9D0F5' ><b><CENTER>".$record->naam."</CENTER></b></td>";
		}
	else
		{
		if ($record->isIn==1)
			{
			$iocolor = "#A9F5BC";
			if ($record->naam == "Michel Stalpaert"  )
				{
					$dag= date("d/m/y");	
					switch ($dag)
					{
						case "10/12/18":
						$iocolor = pink;
						break;
					
						case "11/12/18":
						$iocolor = orange;
						break;
					
						case "12/12/18":
						$iocolor = "#b4b4b4";
						break;
					
						case "13/12/18":
						$iocolor = "#D4AF37";
						break;
					
						default:
						continue;
					}
				}
			}
		else
			{
//			$iocolor = "#F78181";
			$iocolor = "#FFFFFF";
			}			
		echo "<td bgcolor='$iocolor' title='".$record->nota."'><a href='bewerken.php?nummer=$record->id'>$record->naam</a></td>";
		if ($record->tel=="")
			{
			echo "<td bgcolor='$color' >&nbsp</td>";
			} 
		else
			{
			echo "<td bgcolor='$color' >".$record->tel."</td>";
			}
		if ($record->late==1)
			{
			echo "<td background='images/maan.jpg'></td>";
			} 
		else
			{
			echo "<td  bgcolor='$color'>&nbsp</td>";
			}
		if ($record->wacht==1)
			{
			echo "<td background='images/telefoon.jpg' width='5' height='10'></td>";
			} 
		else
			{
			if ($record->afdeling === NULL)
				{
				echo "<td  bgcolor='$color'>&nbsp</td>";
				}
			else
				{
				echo "<td  bgcolor='$color'>" . $record->afdeling . "</td>";
				}
			}
		if ($record->nota=="")
			{
			echo "<td bgcolor='$color' >&nbsp</td>";
			}
		else
			{
			if (strlen($record->nota) > 50) 
				{
				$nota = substr($record->nota,0,50) . "...";
				}
			else
				{
				$nota = $record->nota;
				}
			echo "<td overflow='hidden' bgcolor='$color' title='".$record->nota."'>".$nota."&nbsp</td>";
			}
		}
	echo "</tr>";
}
echo"</table>";
echo"</DIV>";
echo"</DIV>";
mysql_close($con);
?>
</body>
</html>