<?php
error_reporting(E_ALL ^ E_DEPRECATED);
  $menu = array();
  $menu["Nieuwe titel"]   = sprintf("%s?%s",$_SERVER["PHP_SELF"],"todo=newTitle");
  $menu["Reorganisatie"]  = sprintf("%s?%s",$_SERVER["PHP_SELF"],"todo=reorganize");

  function connect()
  {
    
    include('C:/Config/inout.php');

    try
    {
      $con = new PDO("mysql:host=$hostname;dbname=$dbname",$username,$password);
      $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $con->setattribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_OBJ);
    }
    catch (PDOException $e)
    {
      die( 'Connection failed: ' . $e->getMessage());
    }
    return $con;
  }

  function reorganize($conn)
  {
    $tabTitle = 0;
    $tabUser  = 0;
    $arrTabs = array();
    $statement = $conn->query('SELECT * FROM bord ORDER BY tabk');
    while($row = $statement->fetch(PDO::FETCH_OBJ))
    {
      if ($row->titel)
      {
        $tabTitle += 100;
        $tabUser = 1;
        $arrTabs[$row->id] = $tabTitle;
      }
      else
      {
        $arrTabs[$row->id] = $tabTitle + $tabUser;
        $tabUser += 1;
      }
    }
    foreach ($arrTabs as $id => $value)
    {
      $statement = $conn->query("UPDATE bord SET tabk=$value WHERE id=$id");
    }
    $action = $_SERVER["PHP_SELF"];
    echo "<form name='frmReorganize' method='POST' action='$action'>";
    echo "<script language='JavaScript'>frmReorganize.submit();</script>";
    echo "</form>";
  }

  function list_users($conn)
  {
    $action = $_SERVER["PHP_SELF"];
    try
    {
      $statement = $conn->query('SELECT * FROM bord ORDER BY tabk');
      echo "<form name='frmList' method='POST' action='$action'>";
      echo "<p><table class='list'>";
      echo "<tr>";
      echo "<th class='list'>Volgorde</th>";
      echo "<th class='list'>Naam</th>";
      echo "<th class='list'>Initialen</th>";
      echo "<th class='list'>Telefoon</th>";
      echo "<th class='list'>Email</th>";
      echo "<th class='list'>Afdeling</th>";
      echo "<th colspan='5' class='list'>Acties</th>";
      echo "</tr>";
      $rownum = 0;
      while($row = $statement->fetch(PDO::FETCH_OBJ))
      {
        if ($row->titel)
        {
          $rowclass = "title";
        }
        else
        {
          $rownum ++;
          $rowclass = $rownum % 2 == 0 ? "even" : "odd";
        }
        echo "<tr class='$rowclass'>";
        if ($rowclass == "title")
        {
          echo sprintf("<td>%s</td>",$row->tabk);
          echo sprintf("<td class='$rowclass' colspan='5'>%s</td>",$row->naam);
          echo "<td class='button'><input type='submit' class='add' value='$row->id' name='add' title='Gebruiker toevoegen'></td>";
          echo "<td class='button'><input type='submit' class='modify' value='$row->id' name='modifytitle' title='Titel wijzigen'></td>";
          echo "<td class='button'><input type='submit' class='delete' value='$row->id' name='deletetitle' title='Titel verwijderen'></td>";
          echo "<td class='button'><input type='submit' class='up' value='$row->id' name='titleup' title='Titel naar boven'></td>";
          echo "<td class='button'><input type='submit' class='down' value='$row->id' name='titledown' title='Titel naar beneden'></td>";
        }
        else
        {
          echo sprintf("<td>%s</td>",$row->tabk);
          echo sprintf("<td>%s</td>",$row->naam);
          echo sprintf("<td>%s</td>",$row->Initialen);
          echo sprintf("<td>%s</td>",$row->tel);
          echo sprintf("<td>%s</td>",$row->email);
          echo sprintf("<td>%s</td>",$row->afdeling);
          echo "<td class='button'>&nbsp;</td>";
          echo "<td class='button'><input type='submit' class='modify' value='$row->id' name='modify' title='Wijzigen'></td>";
          echo "<td class='button'><input type='submit' class='delete' value='$row->id' name='delete' title='Verwijderen'></td>";
          echo "<td class='button'><input type='submit' class='up' value='$row->id' name='moveup' title='Naar boven'></td>";
          echo "<td class='button'><input type='submit' class='down' value='$row->id' name='movedown' title='Naar beneden'></td>";
        }
        echo "</tr>";
      }
      echo "</table></p>";
      echo "</form>";
    }
    catch (PDOException $e)
    {
      die( '<br>Error in statement: ' . $e->getMessage());
    }
  }

  function getNewTabk($conn)
  {
    $titleTabk = intval($_POST['toTitle'],10);
    $statement = $conn->query("SELECT tabk FROM bord WHERE titel=1 AND tabk > $titleTabk ORDER BY tabk LIMIT 1");
    $nextTitleTabk  = 0;
    $row = $statement->fetch(PDO::FETCH_OBJ);
    if ($row === false)
    {
      return $titleTabk + 1;
    }
    else
    {
      $nextTitleTabk = intval($row->tabk,10) - 1;
      $sql = "SELECT MAX(tabk) as mTabk FROM bord WHERE tabk BETWEEN $titleTabk AND $nextTitleTabk";
      $statement = $conn->query($sql);
      $row = $statement->fetch(PDO::FETCH_OBJ);
      return intval($row->mTabk,10) + 1;
    }
  }

  function getTitle($conn,$id)
  {
    $statement = $conn->query("SELECT tabk FROM bord WHERE id=$id");
    $row = $statement->fetch(PDO::FETCH_OBJ);
    $tabk = intval($row->tabk/100) * 100;
    $statement = $conn->query("SELECT naam FROM bord WHERE tabk = $tabk AND titel=1 LIMIT 1");
    $row = $statement->fetch(PDO::FETCH_OBJ);
    return $row->naam;
  }

  function Store($conn)
  {
    $action = $_SERVER["PHP_SELF"];
    $mode   = $_POST["store"];
    unset($_POST["store"]);
    if ($_POST["Opslaan"] == "Opslaan" or $_POST["Opslaan"] == "Delete")
    {
      switch ($mode)
      {
        case "new":
          $newTabk = getNewTabk($conn);
          $sql  = "INSERT INTO bord (id,naam,tabk,afdeling,rood,blauw,geel,wacht,late,kb,titel,bord,nota,tel,isIn,Initialen,email) ";
          $sql .= sprintf("VALUES (0,'%s',%d,'%s',",$_POST['naam'],$newTabk,$_POST['afdeling']);
          $sql .= sprintf("0,0,0,0,0,%d,0,'%s','',",isset($_POST['kb']),$_POST['selBord']);
          $sql .= sprintf("'%s',0,'%s','%s')",$_POST['tel'],strtoupper($_POST['initialen']),$_POST['email']);
          $statement = $conn->query($sql);
          break;
        case "modify":
          $sql  = "UPDATE bord SET ";
          $sql .= sprintf("naam='%s',",$_POST['naam']);
          $sql .= sprintf("Initialen='%s',",strtoupper($_POST['initialen']));
          $sql .= sprintf("afdeling='%s',",$_POST['afdeling']);
          $sql .= sprintf("tel='%s',",$_POST['tel']);
          $sql .= sprintf("email='%s',",$_POST['email']);
          $sql .= sprintf("bord='%s', ",$_POST['selBord']);
          $sql .= sprintf("kb=%d ",isset($_POST['kb']));
          $sql .= sprintf("WHERE id=%d",$_POST['id']);
          $statement = $conn->query($sql);
          break;
        case "delete":
          $sql = sprintf("DELETE FROM bord WHERE id=%d",$_POST['id']);
          $statement = $conn->query($sql);
          break;
        case "newtitle":
          $sql = "SELECT MAX(tabk) as maxtabk FROM bord WHERE titel=1";
          $statement = $conn->query($sql);
          $row = $statement->fetch(PDO::FETCH_OBJ);
          $newTitleTabk = intval($row->maxtabk,10) + 100;
          $sql  = "INSERT INTO bord (id,naam,tabk,afdeling,rood,blauw,geel,wacht,late,kb,titel,bord,nota,tel,isIn,Initialen,email) ";
          $sql .= sprintf("VALUES (0,'%s',%d,'',",$_POST['titel'],$newTitleTabk);
          $sql .= "0,0,0,0,0,0,1,'','','',0,'','')";
          $statement = $conn->query($sql);
          break;
        case "modifytitle":
          $sql = sprintf("UPDATE bord SET naam='%s' WHERE id=%d",$_POST['titel'],$_POST['id']);
          $statement = $conn->query($sql);
          break;
        case "deletetitle":
          $sql = sprintf("DELETE FROM bord WHERE id=%d",$_POST['id']);
          $statement = $conn->query($sql);
          break;
      }
    }
    echo "<form name='frmStore' method='POST' action='$action'>";
    echo "<script language='JavaScript'>frmStore.submit();</script>";
    echo "</form>";
  }

  function Add($conn)
  {
    $action = $_SERVER["PHP_SELF"];
    $ID = intval($_POST["add"],10);
    unset($_POST["add"]);
    $statement = $conn->query("SELECT tabk,naam FROM bord WHERE id=$ID");
    $row = $statement->fetch(PDO::FETCH_OBJ);
    echo "<form name='frmAdd' method='POST' action='$action'>";
    echo "<input type='hidden' name='store' value='new'>";
    echo "<input type='hidden' name='toTitle' value='$row->tabk'>";
    echo "<table class='adduser'>";
    echo "<caption>Toevoegen van een gebruiker aan $row->naam</caption>";
    echo "<tr>";
    echo "<td class='adduser'>Naam:</td>";
    echo "<td class='adduser'><input type='text' name='naam' id='naam' class='data'></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='adduser'>Initialen:</td>";
    echo "<td class='adduser'><input type='text' name='initialen' id='initialen' class='data'></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='adduser'>Afdeling:</td>";
    echo "<td class='adduser'><input type='text' name='afdeling' id='afdeling' class='data'></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='adduser'>Klinisch bioloog:</td>";
    echo "<td class='adduser'><input type='checkbox' name='kb' id='kb' class='data'></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='adduser'>Telefoon:</td>";
    echo "<td class='adduser'><input type='text' name='tel' id='tel' class='data' onkeypress='return isNumberKey(event)'></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='adduser'>Email:</td>";
    echo "<td class='adduser'><input type='email' name='email' id='email' class='data'></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='adduser'>Bord:</td>";
    echo "<td class='adduser'>";
    echo "<select name='selBord' class='data'>";
    echo "<option name='x' value='AML' selected>AML</option>";
    echo "<option name='x' value='LLO'>Lokeren</option>";
    echo "<option name='x' value='BXL'>Brussel</option>";
    echo "</select>";
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td colspan='2' class='bottomline'>";
    echo "<input type='submit' name='Opslaan' value='Opslaan' onclick='return checkFormData(frmAdd);'>";
    echo "&nbsp;&nbsp;";
    echo "<input type='submit' name='Opslaan' value='Annuleren'>";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</form>";
    echo "</div>";
  }

  function Modify($conn)
  {
    $action = $_SERVER["PHP_SELF"];
    $ID = intval($_POST["modify"],10);
    unset($_POST["modify"]);
    $statement = $conn->query("SELECT * FROM bord WHERE id=$ID");
    $row = $statement->fetch(PDO::FETCH_OBJ);
    echo "<div class='content'>";
    echo "<form name='frmModify' method='POST' action='$action'>";
    echo "<input type='hidden' name='store' value='modify'>";
    echo "<input type='hidden' name='id' value='$ID'>";
    echo "<table class='adduser'>";
    echo "<caption>Wijzigen van een gebruiker</caption>";
    echo "<tr>";
    echo "<td class='adduser'>Naam:</td>";
    echo "<td class='adduser'><input type='text' name='naam' id='naam' class='data' value='$row->naam'></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='adduser'>Initialen:</td>";
    echo "<td class='adduser'><input type='text' name='initialen' id='initialen' class='data' value='$row->Initialen'></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='adduser'>Afdeling:</td>";
    echo "<td class='adduser'><input type='text' name='afdeling' id='afdeling' class='data' value='$row->afdeling'></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='adduser'>Klinisch bioloog:</td>";
    $checked = $row->kb == 1 ? "checked" : "";
    echo "<td class='adduser'><input type='checkbox' name='kb' id='kb' class='data' $checked></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='adduser'>Telefoon:</td>";
    echo "<td class='adduser'><input type='text' name='tel' id='tel' class='data' value='$row->tel' onkeypress='return isNumberKey(event)'></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='adduser'>Email:</td>";
    echo "<td class='adduser'><input type='email' name='email' id='email' class='data' value='$row->email'></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='adduser'>Bord:</td>";
    echo "<td class='adduser'>";
    echo "<select name='selBord' class='data'>";
    echo "<option name='x' value='AML' selected>AML</option>";
    echo "<option name='x' value='LLO'>Lokeren</option>";
    echo "<option name='x' value='BXL'>Brussel</option>";
    echo "</select>";
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td colspan='2' class='bottomline'>";
    echo "<input type='submit' name='Opslaan' value='Opslaan' onclick='return checkFormData(frmModify);'>";
    echo "&nbsp;&nbsp;";
    echo "<input type='submit' name='Opslaan' value='Annuleren'>";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</form>";
  }

  function modifyTitle($conn)
  {
    $action = $_SERVER["PHP_SELF"];
    $ID = $_POST['modifytitle'];
    unset($_POST['modifytitle']);
    $statement = $conn->query("SELECT naam FROM bord WHERE id=$ID");
    $row = $statement->fetch(PDO::FETCH_OBJ);
    echo "<div class='content'>";
    echo "<form name='frmModifyTitle' method='POST' action='$action'>";
    echo "<input type='hidden' name='store' value='modifytitle'>";
    echo "<input type='hidden' name='id' value='$ID'>";
    echo "<table class='adduser'>";
    echo "<caption>Toevoegen van een titel</caption>";
    echo "<tr>";
    echo "<td class='adduser'>Titel:</td>";
    echo "<td class='adduser'><input type='text' name='titel' id='titel' value=\"$row->naam\" class='data'></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td colspan='2' class='bottomline'>";
    echo "<input type='submit' name='Opslaan' value='Opslaan' onclick='return checkFormData(frmModifyTitle);'>";
    echo "&nbsp;&nbsp;";
    echo "<input type='submit' name='Opslaan' value='Annuleren'>";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</form>";
    echo "</div>";
  }

  function deleteTitle($conn)
  {
    $action = $_SERVER["PHP_SELF"];
    $ID = intval($_POST["deletetitle"],10);
    $statement = $conn->query("SELECT tabk,naam FROM bord WHERE id=$ID");
    $row = $statement->fetch(PDO::FETCH_OBJ);
    $currTabk = intval($row->tabk,10);
    $delNaam = $row->naam;
    $sql = sprintf("SELECT tabk,naam FROM bord WHERE tabk > %d AND titel=1 ORDER BY tabk",$currTabk);
    $statement = $conn->query($sql);
    $row = $statement->fetch(PDO::FETCH_OBJ);
    if ($row)
    {
      $nextTabk = intval($row->tabk,10);
      $sql = sprintf("SELECT tabk FROM bord WHERE titel=0 AND tabk BETWEEN %d AND %d",$currTabk,$nextTabk);
    }
    else
    {
      $sql = sprintf("SELECT tabk FROM bord WHERE titel=0 AND tabk > %d",$currTabk);
    }
    $statement = $conn->query($sql);
    $row = $statement->fetch(PDO::FETCH_OBJ);
    echo "<div class='content'>";
    echo "<form name='frmDeleteTitle' method='POST' action='$action'>";
    echo "<input type='hidden' name='store' value='deletetitle'>";
    echo "<input type='hidden' name='id' value='$ID'>";
    echo "<table class='adduser'>";
    echo "<caption>Verwijderen van een titel</caption>";
    if ($row === false)
    {
      echo "<tr>";
      echo "<td class='deluser'><p class='deluser'>Wilt u <em>'$delNaam'</em> werkelijk verwijderen?</p></td>";
      echo "</tr>";
      echo "<tr>";
      echo "<td class='bottomline'>";
      echo "<input type='submit' name='Opslaan' value='Delete'>";
      echo "&nbsp;&nbsp;";
      echo "<input type='submit' name='Opslaan' value='Annuleren'>";
      echo "</td>";
      echo "</tr>";
    }
    else
    {
      echo "<tr>";
      echo "<td class='deluser'><p class='deluser'><em>'$delNaam'</em> bevat nog namen, mag niet verwijderd worden</p></td>";
      echo "</tr>";
      echo "<tr>";
      echo "<td class='bottomline'>";
      echo "<input type='submit' name='Opslaan' value='Annuleren'>";
      echo "</td>";
      echo "</tr>";
    }
    echo "</table>";
    echo "</form>";
    echo "<div>";
  }

  function Delete($conn)
  {
    $action = $_SERVER["PHP_SELF"];
    $ID = $_POST["delete"];
    $statement = $conn->query("SELECT naam FROM bord WHERE id=$ID");
    $row = $statement->fetch(PDO::FETCH_OBJ);
    $delNaam = $row->naam;
    $delTitle = getTitle($conn,$ID);
    unset($_POST["delete"]);
    echo "<div class='content'>";
    echo "<form name='frmDelete' method='POST' action='$action'>";
    echo "<input type='hidden' name='store' value='delete'>";
    echo "<input type='hidden' name='id' value='$ID'>";
    echo "<table class='adduser'>";
    echo "<caption>Verwijderen van een gebruiker</caption>";
    echo "<tr>";
    echo "<td class='deluser'><p class='deluser'>Wilt u <em>'$delNaam'</em> werkelijk verwijderen uit <em>'$delTitle'</em> ?</p></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td class='bottomline'>";
    echo "<input type='submit' name='Opslaan' value='Delete'>";
    echo "&nbsp;&nbsp;";
    echo "<input type='submit' name='Opslaan' value='Annuleren'>";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</form>";
  }

  function switchTabs($conn,$aTabs)
  {
    if (count($aTabs) > 1)
    {
      $aTabs[0]['newtabk'] = $aTabs[1]['tabk'];
      $aTabs[1]['newtabk'] = $aTabs[0]['tabk'];
      foreach ($aTabs as $index => $values)
      {
        $sql = sprintf("UPDATE bord SET tabk=%d WHERE id=%d",$values['newtabk'],$values['id']);
        $statement = $conn->query($sql);
      }
    }
  }

  function MoveUp($conn)
  {
    $aTabs = array();
    $currID = intval($_POST['moveup'],10);
    unset($_POST["moveup"]);

    $statement = $conn->query("SELECT tabk FROM bord WHERE id=$currID");
    $row = $statement->fetch(PDO::FETCH_OBJ);
    $currTabk = intval($row->tabk,10);
    $aTabs[] = array('id' => $currID,'tabk' => $currTabk,'newtabk' => 0);

    $prevTabk = $currTabk - 1;
    $statement = $conn->query("SELECT id,titel FROM bord WHERE tabk=$prevTabk");
    $row = $statement->fetch(PDO::FETCH_OBJ);
    if (!$row->titel)
      $aTabs[] = array('id' => intval($row->id,10), 'tabk' => $prevTabk, 'newtabk' => 0);
    switchTabs($conn,$aTabs);
    $action = $_SERVER["PHP_SELF"];
    echo "<form name='frmMoveUp' method='POST' action='$action'>";
    echo "</form>";
    echo "<script language='JavaScript'>frmMoveUp.submit();</script>";
  }

  function MoveDown($conn)
  {
    $aTabs = array();
    $currID = intval($_POST['movedown'],10);
    unset($_POST["movedown"]);

    $statement = $conn->query("SELECT tabk FROM bord WHERE id=$currID");
    $row = $statement->fetch(PDO::FETCH_OBJ);
    $currTabk = intval($row->tabk,10);
    $aTabs[] = array('id' => $currID,'tabk' => $currTabk,'newtabk' => 0);

    $nextTabk = $currTabk + 1;
    $statement = $conn->query("SELECT id FROM bord WHERE tabk=$nextTabk");
    $row = $statement->fetch(PDO::FETCH_OBJ);
    if ($row)
      $aTabs[] = array('id' => intval($row->id,10), 'tabk' => $nextTabk, 'newtabk' => 0);
    switchTabs($conn,$aTabs);
    $action = $_SERVER["PHP_SELF"];
    echo "<form name='frmMoveDown' method='POST' action='$action'>";
    echo "</form>";
    echo "<script language='JavaScript'>frmMoveDown.submit();</script>";
  }

  function switchTitles($conn,$aTabs)
  {
    if (count($aTabs) > 1)
    {
      $direction = $aTabs['from']['tabk'] > $aTabs['to']['tabk'] ? "up" : "down";
      $aTabs['from']['newtabk'] = $aTabs['to']['tabk'];
      $aTabs['to']['newtabk'] = $aTabs['from']['tabk'];
      switch ($direction)
      {
        case "up":
          $sql = sprintf("SELECT id,tabk,naam FROM bord WHERE tabk BETWEEN %d AND %d AND titel=0",$aTabs['to']['tabk'],$aTabs['from']['tabk']);
          $statement = $conn->query($sql);
          while($row = $statement->fetch(PDO::FETCH_OBJ))
          {
            $aTabs['to']['users'][] = array('id' => intval($row->id,10), 'naam' => $row->naam, 'tabk' => intval($row->tabk,10), 'newtabk' => 0);
          }
          $sql = sprintf("SELECT tabk FROM bord WHERE tabk > %d AND titel=1 ORDER BY tabk LIMIT 1",$aTabs['from']['tabk']);
          $statement = $conn->query($sql);
          $row = $statement->fetch(PDO::FETCH_OBJ);
          if ($row)
          {
            $toTabk = intval($row->tabk,10);
            $sql = sprintf("SELECT id,tabk,naam FROM bord WHERE tabk BETWEEN %d AND %d AND titel=0",$aTabs['from']['tabk'],$toTabk);
          }
          else //het is de laatste
          {
            $sql = sprintf("SELECT id,tabk,naam FROM bord WHERE tabk > %d AND titel=0",$aTabs['from']['tabk']);
          }
          $statement = $conn->query($sql);
          while($row = $statement->fetch(PDO::FETCH_OBJ))
          {
            $aTabs['from']['users'][] = array('id' => intval($row->id,10), 'naam' => $row->naam, 'tabk' => intval($row->tabk,10), 'newtabk' => 0);
          }
          break;
        case "down":
          $sql =  sprintf("SELECT id,tabk,naam FROM bord WHERE tabk BETWEEN %d AND %d AND titel=0",$aTabs['from']['tabk'],$aTabs['to']['tabk']);
          $statement = $conn->query($sql);
          while($row = $statement->fetch(PDO::FETCH_OBJ))
          {
            $aTabs['from']['users'][] = array('id' => intval($row->id,10), 'naam' => $row->naam, 'tabk' => intval($row->tabk,10), 'newtabk' => 0);
          }
          $sql = sprintf("SELECT tabk FROM bord WHERE tabk > %d AND titel=1 ORDER BY tabk LIMIT 1",$aTabs['to']['tabk']);
          $statement = $conn->query($sql);
          $row = $statement->fetch(PDO::FETCH_OBJ);
          $toTabk = intval($row->tabk,10);
          $sql = sprintf("SELECT id,tabk,naam FROM bord WHERE tabk BETWEEN %d AND %d AND titel=0",$aTabs['to']['tabk'],$toTabk);
          $statement = $conn->query($sql);
          while($row = $statement->fetch(PDO::FETCH_OBJ))
          {
            $aTabs['to']['users'][] = array('id' => intval($row->id,10), 'naam' => $row->naam, 'tabk' => intval($row->tabk,10), 'newtabk' => 0);
          }
          break;
      }
      foreach ($aTabs as &$aTitle)
      {
        $aTitle['sql'] = sprintf("UPDATE bord SET tabk=%d WHERE id=%d",$aTitle['newtabk'],$aTitle['id']);
        $statement = $conn->query($aTitle['sql']);
        if (isset($aTitle['users']))
        {
          foreach ($aTitle['users'] as &$aUser)
          {
            $aUser['newtabk'] = ($aUser['tabk'] - $aTitle['tabk']) + $aTitle['newtabk'];
            $aUser['sql'] = sprintf("UPDATE bord SET tabk=%d WHERE id=%d",$aUser['newtabk'],$aUser['id']);
            $statement = $conn->query($aUser['sql']);
          }
        }
      }
//      Var_dump::display($aTabs);
    }
  }

  function newTitle($conn)
  {
    $action = $_SERVER["PHP_SELF"];
    echo "<form name='frmNewtitle' method='POST' action='$action'>";
    echo "<input type='hidden' name='store' value='newtitle'>";
    echo "<table class='adduser'>";
    echo "<caption>Toevoegen van een titel</caption>";
    echo "<tr>";
    echo "<td class='adduser'>Titel:</td>";
    echo "<td class='adduser'><input type='text' name='titel' id='titel' class='data' ></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td colspan='2' class='bottomline'>";
    echo "<input type='submit' name='Opslaan' value='Opslaan' onclick='return checkFormData(frmNewtitle);'>";
    echo "&nbsp;&nbsp;";
    echo "<input type='submit' name='Opslaan' value='Annuleren'>";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</form>";
    echo "</div>";
  }

  function titleDown($conn)
  {
    $aTabs = array();
    $currID = intval($_POST['titledown'],10);
    unset($_POST["titledown"]);

    $statement = $conn->query("SELECT tabk,naam FROM bord WHERE id=$currID");
    $row = $statement->fetch(PDO::FETCH_OBJ);
    $currTabk = intval($row->tabk,10);
    $aTabs['from'] = array('id' => $currID, 'naam' => $row->naam, 'tabk' => $currTabk,'newtabk' => 0);

    $nextTabk = $currTabk + 1;
    $statement = $conn->query("SELECT id,tabk,naam FROM bord WHERE tabk > $nextTabk AND titel=1 ORDER BY tabk LIMIT 1");
    $row = $statement->fetch(PDO::FETCH_OBJ);
    if ($row)
      $aTabs['to'] = array('id' => intval($row->id,10), 'naam' => $row->naam, 'tabk' => intval($row->tabk,10), 'newtabk' => 0);

    switchTitles($conn,$aTabs);

    $action = $_SERVER["PHP_SELF"];
    echo "<form name='frmTitleDown' method='POST' action='$action'>";
    echo "</form>";
    echo "<script language='JavaScript'>frmTitleDown.submit();</script>";
  }

  function titleUp($conn)
  {
    $aTabs = array();
    $currID = intval($_POST['titleup'],10);
    unset($_POST["titleup"]);

    $statement = $conn->query("SELECT tabk,naam FROM bord WHERE id=$currID");
    $row = $statement->fetch(PDO::FETCH_OBJ);
    $currTabk = intval($row->tabk,10);
    $aTabs['from'] = array('id' => $currID, 'naam' => $row->naam, 'tabk' => $currTabk,'newtabk' => 0);

    $prevTabk = $currTabk - 1;
    $statement = $conn->query("SELECT id,tabk,naam FROM bord WHERE tabk < $prevTabk AND titel=1 ORDER BY tabk");
    while($row = $statement->fetch(PDO::FETCH_OBJ))
    {
      $aTabs['to'] = array('id' => intval($row->id,10), 'naam' => $row->naam, 'tabk' => intval($row->tabk,10), 'newtabk' => 0);
    }
    switchTitles($conn,$aTabs);
    $action = $_SERVER["PHP_SELF"];
    echo "<form name='frmTitleUp' method='POST' action='$action'>";
    echo "</form>";
    echo "<script language='JavaScript'>frmTitleUp.submit();</script>";
  }
?>

<!DOCTYPE HTML>

<html>

<head>
  <title>Configuratie in-out bord</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <link rel="stylesheet" type="text/css" href="css/inout_config.css" />
  <link rel="stylesheet" type="text/css" href="css/menu.css" />
  <script>
    function setAction(pAction)
    {
      var frm = parent.document.getElementById("frmMenu");
      frm.action = pAction;
    }

    function isNumberKey(evt)
    {
      var charCode = (evt.which) ? evt.which : event.keyCode
      if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
      return true;
    }

    function checkFormData(t)
    {
      var i;
      var element

      switch (t.name)
      {
        case 'frmAdd':
        case 'frmModify':
          for (i=0; i<t.elements.length; i++)
          {
            element = t.elements[i];
            switch (element.id)
            {
              case 'naam':
                if (element.value == '')
                {
                  alert("Naam is verplicht");
                  element.focus();
                  return false;
                }
                break;
              case 'initialen':
                if (element.value == '')
                {
                  alert("Initialen zijn verplicht");
                  element.focus();
                  return false;
                }
                break;
              case 'tel':
                if (element.value == '')
                {
                  alert("Telefoon nummer is verplicht");
                  element.focus();
                  return false;
                }
                break;
              case 'email':
                if (element.value == '')
                {
                  alert("Email is verplicht");
                  element.focus();
                  return false;
                }
                break;
            }
          }
          break;
        case 'frmNewtitle':
        case 'frmModifyTitle':
          for (i=0; i<t.elements.length; i++)
          {
            element = t.elements[i];
            switch (element.id)
            {
              case 'titel':
                if (element.value == '')
                {
                  alert("Titel is verplicht");
                  element.focus();
                  return false;
                }
                break;
            }
          }
          break;
      }
    }
  </script>
</head>

<body>
    <div class="container">
      <div class="header">
        Configuratie inout bord
      </div>
      <div class="menu">
        <?php
          $action = $_SERVER["PHP_SELF"];
          echo "<form name='frmMenu' id='frmMenu' method='POST' action=''>";
          foreach($menu as $option => $page)
          {
            echo "<div class='dropdown'>";
            switch (gettype($page))
            {
              case "string":
                echo "<input class='myButton' type='submit' name='$option' value='$option' onclick='setAction(\"$page\")'>";
                break;
              case "array":
                echo "<a class='myButton' href='#'>$option</a>";
                echo "<div class='dropdown-content'>";
                foreach ($page as $suboption => $subpage)
                {
                  echo "<input class='myButton' type='submit' name='$suboption' value='$suboption' onclick='setAction(\"$subpage\")'>";
                }
                echo "</div>";
                break;
            }
            echo "</div>";
          }
          echo "</form>";
        ?>
      </div>
      <div class="content">
      <?php
        $conn = connect();
        $todo     = isset($_GET["todo"]) ? $_GET['todo'] : "";
        $todo     = isset($_POST['add']) ? "add" : $todo;
        $todo     = isset($_POST['modify']) ? "modify" : $todo;
        $todo     = isset($_POST['delete']) ? "delete" : $todo;
        $todo     = isset($_POST['moveup']) ? "moveup" : $todo;
        $todo     = isset($_POST['movedown']) ? "movedown" : $todo;
        $todo     = isset($_POST['store']) ? "store" : $todo;
        $todo     = isset($_POST['titleup']) ? "titleup" : $todo;
        $todo     = isset($_POST['titledown']) ? "titledown" : $todo;
        $todo     = isset($_POST['modifytitle']) ? "modifytitle" : $todo;
        $todo     = isset($_POST['deletetitle']) ? "deletetitle" : $todo;
        switch ($todo)
        {
          case "newTitle":
            newTitle($conn);
            break;
          case "reorganize":
            reorganize($conn);
            break;
          case "add":
            Add($conn);
            break;
          case "modify":
            Modify($conn);
            break;
          case "delete":
            Delete($conn);
            break;
          case "moveup":
            MoveUp($conn);
            break;
          case "movedown":
            MoveDown($conn);
            break;
          case "store":
            Store($conn);
            break;
          case "titleup":
            titleUp($conn);
            break;
          case "titledown":
            titleDown($conn);
            break;
          case "modifytitle":
            modifyTitle($conn);
            break;
          case "deletetitle":
            deleteTitle($conn);
            break;
          default:
            list_users($conn);
            break;
        }
//        reorganize($conn);
      ?>
      </div>
    </div>
</body>

</html>