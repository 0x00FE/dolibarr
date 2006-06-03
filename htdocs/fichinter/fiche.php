<?php
/* Copyright (C) 2002-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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

/**
        \file       htdocs/fichinter/fiche.php
        \brief      Fichier fiche intervention
        \ingroup    ficheinter
        \version    $Revision$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/fichinter/fichinter.class.php");
require_once(DOL_DOCUMENT_ROOT."/includes/modules/fichinter/modules_fichinter.php");
require_once(DOL_DOCUMENT_ROOT."/project.class.php");
if (defined("FICHEINTER_ADDON") && is_readable(DOL_DOCUMENT_ROOT ."/includes/modules/ficheinter/".FICHEINTER_ADDON.".php"))
{
    require_once(DOL_DOCUMENT_ROOT ."/includes/modules/ficheinter/".FICHEINTER_ADDON.".php");
}

$langs->load("interventions");

$user->getrights("ficheinter");
if (!$user->rights->ficheinter->lire) accessforbidden();


if ($_GET["socidp"])
{
	$societe=new Societe($db);
	$societe->fetch($_GET["socidp"]);
}

// S�curit� acc�s client
if ($user->societe_id > 0)
{
    $action = '';
    $socidp = $user->societe_id;
}


/*
 * Traitements des actions
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
 * G�n�rer ou reg�n�rer le document PDF
 */
if ($_REQUEST['action'] == 'builddoc')	// En get ou en post
{
	$outputlangs = new Translate(DOL_DOCUMENT_ROOT ."/langs");
	$outputlangs->setDefaultLang($_REQUEST['lang_id']);
	$result=fichinter_pdf_create($db, $_REQUEST['id'], $_REQUEST['model'], $outputlangs);
    if ($result <= 0)
    {
    	dolibarr_print_error($db,$result);
        exit;
    }    
}



/*
 * Affichage page
 */

llxHeader();

$sel = new Form($db);

/*
 *
 * Mode creation
 * Creation d'une nouvelle fiche d'intervention
 *
 */
if ($_GET["action"] == 'create')
{
  print_titre($langs->trans("AddIntervention"));
  
  // \todo Utiliser un module de num�rotation
  $numpr = "FI".strftime("%y%m%d", time());
  
  $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."propal";
  $sql.= " WHERE ref like '${numpr}%'";
  
  $resql=$db->query($sql);
  if ($resql)
    {
      $num = $db->result(0, 0);
      $db->free($resql);
      if ($num > 0)
	{
	  $numpr .= "." . ($num + 1);
	}
    }
  
  $fix = new Fichinter($db);

    $obj = $conf->global->FICHEINTER_ADDON;

// \todo	Quand module numerotation fiche inter sera dispo
//    $modFicheinter = new $obj;
//    $numpr = $modFicheinter->getNextValue($soc);

  $numpr = $fix->get_new_num($societe);
  
  print "<form name='fichinter' action=\"fiche.php\" method=\"post\">";
  
  $smonth = 1;
  $syear = date("Y", time());
  print '<table class="border" width="100%">';
  
  print '<input type="hidden" name="socidp" value='.$_GET["socidp"].'>';
  print "<tr><td>".$langs->trans("Company")."</td><td>".$societe->getNomUrl(1)."</td></tr>";
  
  print "<tr><td>".$langs->trans("Date")."</td><td>";
  $sel->select_date(time(),"p",'','','','fichinter');
  print "</td></tr>";
  
  print "<input type=\"hidden\" name=\"action\" value=\"add\">";
  
  print "<tr><td>".$langs->trans("Ref")."</td><td><input name=\"ref\" value=\"$numpr\"></td></tr>\n";
  print "<tr><td>".$langs->trans("Duration")." (".$langs->trans("days").")</td><td><input name=\"duree\"></td></tr>\n";
  
  if ($conf->projet->enabled)
    {
      // Projet associe
      $langs->load("project");
      print '<tr><td valign="top">'.$langs->trans("Project").'</td><td><select name="projetidp">';
      print '<option value="0"></option>';
      
      $sql = 'SELECT p.rowid, p.title FROM '.MAIN_DB_PREFIX.'projet as p WHERE p.fk_soc = '.$_GET["socidp"];
      
      $resql=$db->query($sql);
      if ($resql)
	{
	  $i = 0 ;
	  $numprojet = $db->num_rows($resql);
	  while ($i < $numprojet)
	    {
	      $projet = $db->fetch_object($resql);
	      print "<option value=\"$projet->rowid\">$projet->title</option>";
	      $i++;
	    }
	  $db->free($resql);
	}
      print '</select>';
      
      if ($numprojet==0)
	{
	  print 'Cette soci�t� n\'a pas de projet.&nbsp;';

	  $user->getrights("projet");

	  if ($user->rights->projet->creer)
	    {
	      print '<a href='.DOL_URL_ROOT.'/projet/fiche.php?socidp='.$socidp.'&action=create>'.$langs->trans("Add").'</a>';
	    }
	}
      
    }
  print '</td></tr>';
  
  print '<tr><td valign="top">'.$langs->trans("Description").'</td>';
  print "<td><textarea name=\"note\" wrap=\"soft\" cols=\"60\" rows=\"15\"></textarea>";
  print '</td></tr>';
  
  print '<tr><td colspan="2" align="center">';
  print '<input type="submit" class="button" value="'.$langs->trans("CreateDaftIntervention").'">';
  print '</td></tr>';
  print '</table>';
  print '</form>';
          
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

    dolibarr_fiche_head($head, $a, $langs->trans("EditIntervention"));

    /*
     *   Initialisation de la liste des projets
     */
    print "<form name='update' action=\"fiche.php\" method=\"post\">";

    print "<input type=\"hidden\" name=\"action\" value=\"update\">";
    print "<input type=\"hidden\" name=\"id\" value=\"".$_GET["id"]."\">";

    print '<table class="border" width="100%">';

    print '<tr><td>'.$langs->trans("Ref").'</td><td>'.$fichinter->ref.'</td></tr>';

    print "<tr><td>".$langs->trans("Date")."</td><td>";
    $sel->select_date($fichinter->date,'','','','','update');
    print "</td></tr>";

    print '<tr><td>'.$langs->trans("Duration")." (".$langs->trans("days").')</td><td><input name="duree" value="'.$fichinter->duree.'"></td></tr>';

    if ($conf->projet->enabled)
      {
        // Projet associ�
        print '<tr><td valign="top">'.$langs->trans("Project").'</td><td>';
        $sel->select_projects($fichinter->societe_id,$fichinter->projet_id,"projetidp");
        print '</td></tr>';
      }

    print '<tr><td valign="top">'.$langs->trans("Description").'</td>';
    print '<td><textarea name="note" wrap="soft" cols="60" rows="12">';
    print $fichinter->note;
    print '</textarea>';
    print '</td></tr>';

    print '<tr><td colspan="2" align="center">';
    print '<input type="submit" class="button" value="'.$langs->trans("Save").'">';
    print '</td></tr>';
    print '</table>';

    print '</form>';

    print '</div>';
}

/*
* Mode visu
*
*/

if ($_GET["id"] && $_GET["action"] != 'edit')
{
    if ($mesg) print $mesg."<br>";
    
    dolibarr_fiche_head($head, $a, $langs->trans("InterventionCard"));

    $fichinter = new Fichinter($db);
    $result=$fichinter->fetch($_GET["id"]);
    if (! $result > 0)
    {
        dolibarr_print_error($db);
        exit;
    }
    
    $fichinter->fetch_client();

    print '<table class="border" width="100%">';
    print '<tr><td>'.$langs->trans("Ref").'</td><td>'.$fichinter->ref.'</td></tr>';
    print '<tr><td>'.$langs->trans("Company").'</td><td><a href="../comm/fiche.php?socid='.$fichinter->client->id.'">'.$fichinter->client->nom.'</a></td></tr>';
    print '<tr><td width="20%">'.$langs->trans("Date").'</td><td>'.dolibarr_print_date($fichinter->date,"%A %d %B %Y").'</td></tr>';
    print '<tr><td>'.$langs->trans("Duration").'</td><td>'.$fichinter->duree.'</td></tr>';

    if ($conf->projet->enabled)
    {
        $fichinter->fetch_projet();
        print '<tr><td valign="top">'.$langs->trans("Project").'</td><td>'.$fichinter->projet.'</td></tr>';
    }
    print '<tr><td>'.$langs->trans("Status").'</td><td>'.$fichinter->getLibStatut(4).'</td></tr>';

    print '<tr><td valign="top">'.$langs->trans("Description").'</td>';
    print '<td>';
    print nl2br($fichinter->note);
    print '</td></tr>';

    print '</td></tr>';
    print "</table>";
    print '</div>';
    

    /**
     * Barre d'actions
     *
     */
    print '<div class="tabsAction">';

    if ($user->societe_id == 0)
    {

        if ($fichinter->statut == 0)
        {
            print '<a class="tabAction" href="fiche.php?id='.$_GET["id"].'&action=edit">'.$langs->trans("Edit").'</a>';
        }

        if ($fichinter->statut == 0)
        {
            print '<a class="tabAction" href="fiche.php?id='.$_GET["id"].'&action=valid">'.$langs->trans("Valid").'</a>';
        }

        if ($fichinter->statut == 0)
        {
            $langs->load("bills");
            print '<a class="tabAction" href="fiche.php?id='.$_GET["id"].'&action=builddoc">'.$langs->trans("BuildPDF").'</a>';
        }

        if ($fichinter->statut >= 0)
        {
            $langs->load("bills");
            print '<a class="tabAction" href="fiche.php?id='.$_GET["id"].'&action=builddoc">'.$langs->trans("RebuildPDF").'</a>';
        }

    }
    print '</div>';

    print '<table width="100%"><tr><td width="50%" valign="top">';

    /*
     * Documents g�n�r�s
     */
    $filename=sanitize_string($fichinter->ref);
    $filedir=$conf->fichinter->dir_output . "/".$fichinter->ref;
    $urlsource=$_SERVER["PHP_SELF"]."?id=".$fichinter->id;
    //$genallowed=$user->rights->fichinter->creer;
    //$delallowed=$user->rights->fichinter->supprimer;
    $genallowed=1;
    $delallowed=0;
    
    $var=true;
    
    print "<br>\n";
    $sel->show_documents('ficheinter',$filename,$filedir,$urlsource,$genallowed,$delallowed,$ficheinter->modelpdf);

  
    print "</td><td>";
    
    print "&nbsp;";
    
    print "</tr></table>\n";

}

$db->close();
llxFooter();
?>
