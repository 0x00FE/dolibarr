<?php
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003 Jean-Louis Bergamo   <jlb@j1b.org>
 * Copyright (C) 2004 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004 Sebastien Di Cintio  <sdicintio@ressource-toi.org>
 * Copyright (C) 2004 Benoit Mortier			  <benoit.mortier@opensides.be>
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

/*!	\file htdocs/admin/adherent.php
		\ingroup    adherent
		\brief      Page d'administration/configuration du module Adherent
		\version    $Revision$
*/

require("./pre.inc.php");

$langs->load("admin");
$langs->load("members");

if (!$user->admin)
  accessforbidden();


// positionne la variable pour le test d'affichage de l'icone

$main_use_mailman = ADHERENT_USE_MAILMAN;
$main_use_glasnost = ADHERENT_USE_GLASNOST;
$main_use_glasnost_auto = ADHERENT_USE_GLASNOST_AUTO;
$main_use_spip = ADHERENT_USE_SPIP;
$main_use_spip_auto = ADHERENT_USE_SPIP_AUTO;

$typeconst=array('yesno','texte','chaine');
$var=True;

if ($_POST["action"] == 'update' || $_POST["action"] == 'add')
{
  if (isset($_POST["consttype"]) && $_POST["consttype"] != '')
	{
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."const WHERE name = '".$_POST["constname"]."';";
	    $db->query($sql);
		$sql = '';
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."const (name,value,type,visible,note) VALUES ('".$_POST["constname"]."','".$_POST["constvalue"]."','".$typeconst[$_POST["consttype"]]."',0,'".$_POST["constnote"]."') ;";

  }
	else
	{
	    $sql = "DELETE FROM ".MAIN_DB_PREFIX."const WHERE name = '".$_POST["constname"]."';";
	    $db->query($sql);
		$sql ='';
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."const (name,value,visible,note) VALUES ('".$_POST["constname"]."','".$_POST["constvalue"]."',0,'".$_POST["constnote"]."') ;";
  }
  
  if ($db->query($sql))
    {
      Header("Location: adherent.php");
    }
  
}

if ($_GET["action"] == 'set')
{
   $sql = "DELETE FROM ".MAIN_DB_PREFIX." WHERE name = '".$_GET["name"]."' ;";
	 $db->query($sql);
	 
	 $sql ='';
	 $sql = "INSERT INTO ".MAIN_DB_PREFIX."const(name,value,visible) values ('".$_GET["name"]."','".$_GET["value"]."', 0);";

  if ($db->query($sql))
    {
      Header("Location: adherent.php");
    }
}

if ($_GET["action"] == 'unset')
{
  $sql = "DELETE FROM ".MAIN_DB_PREFIX."const WHERE name = '".$_GET["name"]."'";

  if ($db->query($sql))
    {
      Header("Location: adherent.php");
    }
}

llxHeader();

/*
 * Interface de configuration de certaines variables de la partie adherent
 */

print_titre("Gestion des adh�rents : Configurations de parametres");
print "<br>";

/*
 * Mailman
 */
if (defined("ADHERENT_USE_MAILMAN") && ADHERENT_USE_MAILMAN == 1)
{
  $lien='<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/tick.png" border="0"></a>';
  $lien.='<a href="adherent.php?action=unset&value=0&name=ADHERENT_USE_MAILMAN">'.$langs->trans("Disable").'</a>';
  // Edition des varibales globales rattache au theme Mailman 
  $constantes=array('ADHERENT_MAILMAN_LISTS',
		    'ADHERENT_MAILMAN_LISTS_COTISANT',
		    'ADHERENT_MAILMAN_ADMINPW',
		    'ADHERENT_MAILMAN_SERVER',
		    'ADHERENT_MAILMAN_UNSUB_URL',
		    'ADHERENT_MAILMAN_URL'
		    );
  print_fiche_titre("Mailman - Syst�me de mailing listes",$lien);
  form_constantes($constantes);
}
else
{
  $lien='<a href="adherent.php?action=set&value=1&name=ADHERENT_USE_MAILMAN">'.$langs->trans("Activate").'</a>';
  print_fiche_titre("Mailman - Syst�me de mailing listes",$lien);
}

print "<hr>\n";

/*
 * Gestion banquaire
 */
if (defined("ADHERENT_BANK_USE") && ADHERENT_BANK_USE == 1)
{
  $lien='<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/tick.png" border="0"></a>';
  $lien.='<a href="adherent.php?action=unset&value=0&name=ADHERENT_BANK_USE">'.$langs->trans("Disable").'</a>';
  // Edition des varibales globales rattache au theme Mailman 
  $constantes=array('ADHERENT_BANK_USE_AUTO',
		    'ADHERENT_BANK_ACCOUNT',
		    'ADHERENT_BANK_CATEGORIE'
		    );
  print_fiche_titre("Gestion banquaire des adherents",$lien);
  form_constantes($constantes);
}
else
{
  $lien='<a href="adherent.php?action=set&value=1&name=ADHERENT_BANK_USE">'.$langs->trans("Activate").'</a>';
  print_fiche_titre("Gestion banquaire des adherents",$lien);
}

print "<hr>\n";

/*
 * Spip
 */
$var=!$var;
if (defined("ADHERENT_USE_SPIP") && ADHERENT_USE_SPIP == 1)
{
  $lien='<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/tick.png" border="0"></a>';
  $lien.='<a href="adherent.php?action=unset&value=0&name=ADHERENT_USE_SPIP">'.$langs->trans("Disable").'</a>';
  // Edition des varibales globales rattache au theme Mailman 
  $constantes=array('ADHERENT_USE_SPIP_AUTO',
		    'ADHERENT_SPIP_SERVEUR',
		    'ADHERENT_SPIP_DB',
		    'ADHERENT_SPIP_USER',
		    'ADHERENT_SPIP_PASS'
		    );
  print_fiche_titre("SPIP - Syst�me de publication en ligne",$lien);
  form_constantes($constantes);
}
else
{
  $lien='<a href="adherent.php?action=set&value=1&name=ADHERENT_USE_SPIP">'.$langs->trans("Activate").'</a>';
  print_fiche_titre("SPIP - Syst�me de publication en ligne",$lien);
}

print "<hr>\n";

/*
 * Glasnost
 */
$var=!$var;
if (defined("ADHERENT_USE_GLASNOST") && ADHERENT_USE_GLASNOST == 1)
{
  $lien='<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/tick.png" border="0"></a>';
  $lien.='<a href="adherent.php?action=unset&value=0&name=ADHERENT_USE_GLASNOST">'.$langs->trans("Disable").'</a>';
  // Edition des varibales globales rattache au theme Mailman 
  $constantes=array('ADHERENT_USE_GLASNOST_AUTO',
		    'ADHERENT_GLASNOST_SERVEUR',
		    'ADHERENT_GLASNOST_USER',
		    'ADHERENT_GLASNOST_PASS'
		    );
  print_fiche_titre("Glasnost - Syst�me de vote en ligne",$lien);
  form_constantes($constantes);
}
else
{
  $lien='<a href="adherent.php?action=set&value=1&name=ADHERENT_USE_GLASNOST">'.$langs->trans("Activate").'</a>';
  print_fiche_titre("Glasnost - Syst�me de vote en ligne",$lien);
}

print "<hr>\n";
$var=!$var;
/*
 * Edition des varibales globales non rattache a un theme specifique 
 */
$constantes=array('ADHERENT_TEXT_NEW_ADH',
		  'ADHERENT_MAIL_COTIS_SUBJECT',
		  'ADHERENT_MAIL_COTIS',
		  'ADHERENT_MAIL_EDIT_SUBJECT',
		  'ADHERENT_MAIL_EDIT',
		  'ADHERENT_MAIL_NEW_SUBJECT',
		  'ADHERENT_MAIL_NEW',
		  'ADHERENT_MAIL_RESIL_SUBJECT',
		  'ADHERENT_MAIL_RESIL',
		  'ADHERENT_MAIL_VALID_SUBJECT',
		  'ADHERENT_MAIL_VALID',
		  'ADHERENT_MAIL_FROM',
		  'ADHERENT_CARD_HEADER_TEXT',
		  'ADHERENT_CARD_TEXT',
		  'ADHERENT_CARD_FOOTER_TEXT',
		  'ADHERENT_ETIQUETTE_TYPE'
		  );
print_fiche_titre("Autres variables globales");
form_constantes($constantes);


$db->close();

llxFooter();


function form_constantes($tableau){
  // Variables globales
  global $db,$bc,$langs;
  $form = new Form($db);
  print '<table class="noborder">';
  print '<tr class="liste_titre">';
  print '<td>'.$langs->trans("Description").'</td>';
  print '<td>'.$langs->trans("Value").'</td>';
  print '<td>'.$langs->trans("Type").'</td>';
  print '<td>'.$langs->trans("Action").'</td>';
  print "</tr>\n";
  $var=True;
  
  foreach($tableau as $const){
    $sql = "SELECT rowid, name, value, type, note FROM ".MAIN_DB_PREFIX."const WHERE name='$const'";
    $result = $db->query($sql);
    if ($result && ($db->num_rows() == 1)) {
      $obj = $db->fetch_object($result);
      $var=!$var;
      print '<form action="adherent.php" method="POST">';
      print '<input type="hidden" name="action" value="update">';
      print '<input type="hidden" name="rowid" value="'.$rowid.'">';
      print '<input type="hidden" name="constname" value="'.$obj->name.'">';
      print '<input type="hidden" name="constnote" value="'.stripslashes(nl2br($obj->note)).'">';
      
      print "<tr $bc[$var] class=value><td>".stripslashes(nl2br($obj->note))."</td>\n";
      
      print '<td>';
      if ($obj->type == 'yesno')
	{
	  $form->selectyesnonum('constvalue',$obj->value);
	  print '</td><td>';
	  $form->select_array('consttype',array('yesno','texte','chaine'),0);
	}
      elseif ($obj->type == 'texte')
	{
	  print '<textarea name="constvalue" cols="35" rows="5"wrap="soft">';
	  print $obj->value;
	  print "</textarea>\n";
	  print '</td><td>';
	  $form->select_array('consttype',array('yesno','texte','chaine'),1);
	}
      else
	{
	  print '<input type="text" size="30" name="constvalue" value="'.stripslashes($obj->value).'">';
	  print '</td><td>';
	  $form->select_array('consttype',array('yesno','texte','chaine'),2);
	}
      print '</td><td>';
      
      //      print '<input type="text" size="15" name="constnote" value="'.stripslashes(nl2br($obj->note)).'">';
      //      print '</td><td>';
      print '<input type="Submit" value="Update" name="Button"> &nbsp;';
      print '<a href="adherent.php?name='.$const.'&action=unset">'.img_delete().'</a>';
      print "</td></tr>\n";
      
      print '</form>';
      $i++;
    }    
  }
  print '</table>';
}
?>
