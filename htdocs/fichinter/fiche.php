<?php
/* Copyright (C) 2002-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 * $Id$
 * $Source$
 */

/** 	\file       htdocs/fichinter/fiche.php
		\brief      Fichier fiche intervention
		\ingroup    ficheinter
		\version    $Revision$
*/

require("./pre.inc.php");
require("./fichinter.class.php");
require("../project.class.php");

/*
 * S�curit� acc�s client
 */
if ($user->societe_id > 0) 
{
  $action = '';
  $socidp = $user->societe_id;
}

if ($_GET["socidp"])
{
  $sql = "SELECT s.nom, s.idp, s.prefix_comm FROM ".MAIN_DB_PREFIX."societe as s WHERE s.idp = ".$_GET["socidp"];

  $result = $db->query($sql);
  if ($result)
    {
      if ( $db->num_rows() )
	{
	  $objsoc = $db->fetch_object($result);
	}
      $db->free();
    }
  else {
    dolibarr_print_error($db); 
  }
}

llxHeader();


/*
 * Traitements des actions
 *
 *
 */

if ($_GET["action"] == 'valid')
{
  $fichinter = new Fichinter($db);
  $fichinter->id = $_GET["id"];
  $fichinter->valid($user->id, $conf->fichinter->outputdir);

}

if ($_POST["action"] == 'add')
{
  $fichinter = new Fichinter($db);

  $fichinter->date = $db->idate(mktime(12, 1 , 1, $_POST["pmonth"], $_POST["pday"], $_POST["pyear"]));
  $fichinter->socidp = $_POST["socidp"];
  $fichinter->duree = $_POST["duree"];
  $fichinter->projet_id = $_POST["projetidp"];
  $fichinter->author = $user->id;
  $fichinter->note = $_POST["note"];
  $fichinter->ref = $_POST["ref"];

  $id = $fichinter->create();
  $_GET["id"]=$id;      // Force raffraichissement sur fiche venant d'etre cr��e
}

if ($_POST["action"] == 'update')
{
  $fichinter = new Fichinter($db);

  $fichinter->date = $db->idate(mktime(12, 1 , 1, $_POST["remonth"], $_POST["reday"], $_POST["reyear"]));
  $fichinter->socidp = $_POST["socidp"];
  $fichinter->duree = $_POST["duree"];
  $fichinter->projet_id = $_POST["projetidp"];
  $fichinter->author = $user->id;
  $fichinter->note = $_POST["note"];
  $fichinter->ref = $_POST["ref"];

  $fichinter->update($_POST["id"]);
  $_GET["id"]=$_POST["id"];      // Force raffraichissement sur fiche venant d'etre cr��e
}

/*
 *
 *   Generation du pdf
 *
 */
if ($_GET["action"] == 'generate' && $_GET["id"])
{
  fichinter_pdf_create($db, $_GET["id"]);
  $mesg = "PDF g�n�r�";
}

/*
 *
 * Mode creation
 * Creation d'une nouvelle fiche d'intervention
 *
 */
if ($_GET["action"] == 'create')
{
  print_titre("Cr�ation d'une fiche d'intervention");

  if ( $objsoc->prefix_comm )
    {
      $numpr = "FI-" . $objsoc->prefix_comm . "-" . strftime("%y%m%d", time());
      
      $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."propal WHERE ref like '$numpr%'";
      
      if ( $db->query($sql) )
	{
	  $num = $db->result(0, 0);
	  $db->free();
	  if ($num > 0)
	    {
	      $numpr .= "." . ($num + 1);
	    }
	}

      $fix = new Fichinter($db);
      $numpr = $fix->get_new_num($objsoc->prefix_comm);
    
      print "<form action=\"fiche.php\" method=\"post\">";
      
      $strmonth[1] = "Janvier";
      $strmonth[2] = "F&eacute;vrier";
      $strmonth[3] = "Mars";
      $strmonth[4] = "Avril";
      $strmonth[5] = "Mai";
      $strmonth[6] = "Juin";
      $strmonth[7] = "Juillet";
      $strmonth[8] = "Ao&ucirc;t";
      $strmonth[9] = "Septembre";
      $strmonth[10] = "Octobre";
      $strmonth[11] = "Novembre";
      $strmonth[12] = "D&eacute;cembre";
      
      $smonth = 1;
      $syear = date("Y", time());
      print '<table class="border" width="100%">';
      
      print '<input type="hidden" name="socidp" value='.$_GET["socidp"].'>';
      print "<tr><td>".$langs->trans("Company")."</td><td><b>".$objsoc->nom."</td></tr>";
      
      print "<tr><td>".$langs->trans("Date")."</td><td>";
      $cday = date("d", time());
      print "<select name=\"pday\">";    
      for ($day = 1 ; $day < $sday + 32 ; $day++)
	{
	  if ($day == $cday)
	    {
	      print "<option value=\"$day\" selected>$day";
	    }
	  else
	    {
	      print "<option value=\"$day\">$day";
	    }
	}
      print "</select>";
      $cmonth = date("n", time());
      print "<select name=\"pmonth\">";    
      for ($month = $smonth ; $month < $smonth + 12 ; $month++) {
	if ($month == $cmonth)
	  {
	    print "<option value=\"$month\" selected>" . $strmonth[$month];
	  }
	else
	  {
	    print "<option value=\"$month\">" . $strmonth[$month];
	  }
      }
      print "</select>";
    
      print "<select name=\"pyear\">";
    
      for ($year = $syear ; $year < $syear + 5 ; $year++)
	{
	  print "<option value=\"$year\">$year";
	}
      print "</select></td></tr>";
      
      print "<input type=\"hidden\" name=\"action\" value=\"add\">";
      
      print "<tr><td>".$langs->trans("Ref")."</td><td><input name=\"ref\" value=\"$numpr\"></td></tr>\n";
      print "<tr><td>".$langs->trans("Duration")." (".$langs->trans("days").")</td><td><input name=\"duree\"></td></tr>\n";
      
      if ($conf->projet->enabled) {
          // Projet associ�
          $langs->load("project");
          print '<tr><td valign="top">'.$langs->trans("Project").'</td><td><select name="projetidp">';
          print '<option value="0"></option>';
           
          $sql = "SELECT p.rowid, p.title FROM ".MAIN_DB_PREFIX."projet as p WHERE p.fk_soc = $socidp";
          
          if ( $db->query($sql) )
    	{
    	  $i = 0 ;
    	  $numprojet = $db->num_rows();
    	  while ($i < $numprojet)
    	    {
    	      $projet = $db->fetch_object();
    	      print "<option value=\"$projet->rowid\">$projet->title</option>";
    	      $i++;
    	    }
    	  $db->free();
    	}
          print '</select>';
      
          if ($numprojet==0) {
        	print 'Cette soci�t� n\'a pas de projet.&nbsp;';
        	print '<a href='.DOL_URL_ROOT.'/projet/fiche.php?socidp='.$socidp.'&action=create>'.$langs->trans("Add").'</a>';
          }

      }
      print '</td></tr>';
            
      print '<tr><td valign="top">'.$langs->trans("Description").'</td>';
      print "<td><textarea name=\"note\" wrap=\"soft\" cols=\"60\" rows=\"15\"></textarea>";
      print '</td></tr>';
      
      print '<tr><td colspan="2" align="center">';
      print "<input type=\"submit\" value=\"".$langs->trans("Add")."\">";
      print '</td></tr>';
      print '</table>';      
      print '</form>';
      
    }
  else
    {
      print "Vous devez d'abord associer un prefixe commercial a cette societe" ;
    }
}


/*
 *
 * Mode update
 * Mise a jour de la fiche d'intervention
 *
 */
if ($_GET["action"] == 'edit')
{

  $fichinter = new Fichinter($db);
  $fichinter->fetch($_GET["id"]);

  /*
   *   Initialisation de la liste des projets
   */
  $prj = new Project($db);
  $listeprj = $prj->liste_array($fichinter->societe_id);
  

  print_titre("Mettre � jour Fiche d'intervention");

  print "<form action=\"fiche.php\" method=\"post\">";

  print "<input type=\"hidden\" name=\"action\" value=\"update\">";
  print "<input type=\"hidden\" name=\"id\" value=\"".$_GET["id"]."\">";

  print '<table class="border" width="100%">';
  print "<tr><td>".$langs->trans("Date")."</td><td>";
  /*
   * set $reday, $remonth, $reyear
   */
  print_date_select($fichinter->date);

  print "</select></td></tr>";
  
  print '<tr><td>'.$langs->trans("Ref").'</td><td>'.$fichinter->ref.'</td></tr>';
  print '<tr><td>'.$langs->trans("Duration")." (".$langs->trans("days").')</td><td><input name="duree" value="'.$fichinter->duree.'"></td></tr>';
  
  if ($conf->projet->enabled) {
      // Projet associ�
      print '<tr><td valign="top">'.$langs->trans("Project").'</td><td>';
    
      $sel = new Form($db);
      $sel->select_array("projetidp",$listeprj,$fichinter->projet_id);
    
      if (sizeof($listeprj) == 0)
        {
          print 'Cette soci�t� n\'a pas de projet.&nbsp;';
          print '<a href='.DOL_URL_ROOT.'/comm/projet/fiche.php?socidp='.$socidp.'&action=create>Cr�er un projet</a>';
        }
      print '</td></tr>';
  }

  print '<tr><td valign="top">'.$langs->trans("Description").'</td>';
  print '<td><textarea name="note" wrap="soft" cols="60" rows="15">';
  print $fichinter->note;
  print '</textarea>';
  print '</td></tr>';

  print '<tr><td colspan="2" align="center">';
  print "<input type=\"submit\" value=\"".$langs->trans("Save")."\">";
  print '</td></tr>';
  print "</table>";  
    
  print "</form>";
    
}

/*
 * Mode visu
 *
 */

if ($_GET["id"] && $_GET["action"] != 'edit')
{
  print_fiche_titre("Fiche d'intervention",$mesg);

  $fichinter = new Fichinter($db);
  if ($fichinter->fetch($_GET["id"]))
    {
      $fichinter->fetch_client();

      print '<table class="border" width="100%">';
      print '<tr><td>'.$langs->trans("Company").'</td><td><a href="../comm/fiche.php?socid='.$fichinter->client->id.'">'.$fichinter->client->nom.'</a></td></tr>';
      print '<tr><td width="20%">Date</td><td>'.strftime("%A %d %B %Y",$fichinter->date).'</td></tr>';
      print '<tr><td>'.$langs->trans("Ref").'</td><td>'.$fichinter->ref.'</td></tr>';
      print '<tr><td>'.$langs->trans("Duration").'</td><td>'.$fichinter->duree.'</td></tr>';
      if ($conf->projet->enabled) {
          $fichinter->fetch_projet();
          print '<tr><td valign="top">'.$langs->trans("Ref").'</td><td>'.$fichinter->projet.'</td></tr>';
      }
      print '<tr><td valign="top">'.$langs->trans("Description").'</td>';
      print '<td colspan="3">';
      print nl2br($fichinter->note);
      print '</td></tr>';
      
      print '</td></tr>';
      print "</table>";  


    /*
     * Barre d'actions
     *
     */
    print '<br>';
    print '<div class="tabsAction">';

      if ($user->societe_id == 0)
	{
      
	  if ($fichinter->statut == 0)
	    {
	      print '<a class="tabAction" href="fiche.php?id='.$_GET["id"].'&action=edit">'.$langs->trans("Edit").'</a>';
	    }
	  	  
	  $file = $conf->ficheinter->dir_output . "/$fichinter->ref/$fichinter->ref.pdf";
	  if ($fichinter->statut == 0 or !file_exists($file))
	    {
	      $langs->load("bills");
	      print '<a class="tabAction" href="fiche.php?id='.$_GET["id"].'&action=generate">'.$langs->trans("BuildPDF").'</a>';
	    }
	  
	  if ($fichinter->statut == 0)
	    {
	      print '<a class="tabAction" href="fiche.php?id='.$_GET["id"].'&action=valid">'.$langs->trans("Valid").'</a>';
	    }
	  
	}
    print '</div>';
      
      print '<br>';
  
      print '<table width="50%" cellspacing="2"><tr><td width="50%" valign="top">';
      print_titre("Documents g�n�r�s");
      print '<table width="100%" class="border">';
      
      $file = $conf->ficheinter->dir_output . "/$fichinter->ref/$fichinter->ref.pdf";
      $relativepath="$fichinter->ref/$fichinter->ref.pdf";
      
      $var=true;

      if (file_exists($file))
	{
	  print "<tr $bc[0]><td>Ficheinter PDF</a></td>";
	  print '<td><a href="'.DOL_URL_ROOT.'/document.php?modulepart=ficheinter&file='.urlencode($relativepath).'">'.$fichinter->ref.'.pdf</a></td>';
	  print '<td align="right">'.filesize($file). ' bytes</td>';
	  print '<td align="right">'.strftime("%d %b %Y %H:%M:%S",filemtime($file)).'</td></tr>';
	}  
      
      print "</table></td></tr></table>\n";
      
    }
  else
    {
      print "Fiche inexistante";
    }
}


$db->close();
llxFooter();
?>
