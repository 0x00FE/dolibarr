<?PHP
/* Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
 *
 */
require("./pre.inc.php");
require("../contact.class.php");
require (DOL_DOCUMENT_ROOT."/lib/vcard/vcard.class.php");

if ($HTTP_POST_VARS["action"] == 'add') 
{
  $contact = new Contact($db);

  $contact->socid        = $HTTP_POST_VARS["socid"];

  $contact->name         = $HTTP_POST_VARS["name"];
  $contact->firstname    = $HTTP_POST_VARS["firstname"];

  $contact->poste        = $HTTP_POST_VARS["poste"];

  $contact->fax          = $HTTP_POST_VARS["fax"];
  $contact->note         = $HTTP_POST_VARS["note"];
  $contact->email        = $HTTP_POST_VARS["email"];
  $contact->phone_pro    = $HTTP_POST_VARS["phone_pro"];
  $contact->phone_perso  = $HTTP_POST_VARS["phone_perso"];
  $contact->phone_mobile = $HTTP_POST_VARS["phone_mobile"];  
  $contact->jabberid     = $HTTP_POST_VARS["jabberid"];

  $_GET["id"] =  $contact->create($user);
}

if ($_GET["action"] == 'delete') 
{
  $contact = new Contact($db);

  $contact->old_name      = $HTTP_POST_VARS["old_name"];
  $contact->old_firstname = $HTTP_POST_VARS["old_firstname"];

  $result = $contact->delete($_GET["id"]);

  Header("Location: index.php");
}


if ($action == 'update') 
{
  $contact = new Contact($db);

  $contact->old_name      = $HTTP_POST_VARS["old_name"];
  $contact->old_firstname = $HTTP_POST_VARS["old_firstname"];

  $contact->name          = $HTTP_POST_VARS["name"];
  $contact->firstname     = $HTTP_POST_VARS["firstname"];
  $contact->poste         = $HTTP_POST_VARS["poste"];

  $contact->phone_pro     = $HTTP_POST_VARS["phone_pro"];
  $contact->phone_perso   = $HTTP_POST_VARS["phone_perso"];
  $contact->phone_mobile  = $HTTP_POST_VARS["phone_mobile"];
  $contact->fax           = $HTTP_POST_VARS["fax"];
  $contact->note          = $HTTP_POST_VARS["note"];
  $contact->email         = $HTTP_POST_VARS["email"];
  $contact->jabberid      = $HTTP_POST_VARS["jabberid"];

  $contact->birthday = mktime(12, 1 , 1, 
			      $HTTP_POST_VARS["remonth"], 
			      $HTTP_POST_VARS["reday"], 
			      $HTTP_POST_VARS["reyear"]);

  $contact->birthday_alert = $HTTP_POST_VARS["birthday_alert"];

  $result = $contact->update($HTTP_POST_VARS["contactid"], $user);

}

if ($action == 'create_user') 
{
  $nuser = new User($db);
  $contact = new Contact($db);
  $nuser->nom = $contact->nom;
  $nuser->prenom = $contact->prenom;
  $result = $contact->fetch($contactid);
  $nuser->create_from_contact($contact);
}

/*
 *
 *
 */
llxHeader();

print '<div class="tabs">';
print '<a class="tab" href="fiche.php?id='.$_GET["id"].'">G�n�ral</a>';
print '<a class="tab" href="perso.php?id='.$_GET["id"].'" id="active">Informations personnelles</a>';
print '<a class="tab" href="vcard.php?id='.$_GET["id"].'">VCard</a>';
print '<a class="tab" href="info.php?id='.$_GET["id"].'">Info</a>';
print '</div>';
print '<div class="tabBar">';

if ($_GET["action"] == 'edit') 
{
  print_fiche_titre ("Edition d'un contact");

  $contact = new Contact($db);
  $contact->fetch($_GET["id"], $user);

  print '<form method="post" action="perso.php?id='.$_GET["id"].'">';
  print '<input type="hidden" name="action" value="update">';
  print '<input type="hidden" name="contactid" value="'.$contact->id.'">';

  if ($contact->socid > 0)
    {
      $objsoc = new Societe($db);
      $objsoc->fetch($contact->socid);

      print 'Soci�t� : '.$objsoc->nom.'<br>';
    }

  print 'Nom : '.$contact->name.' '.$contact->firstname ."<br>";

  print '<table class="border" cellpadding="3" celspacing="0" border="0" width="100%">';

  print '<tr><td>Date de naissance</td><td>';
  $html = new Form($db);
  print $html->select_date();
  print '</td><td>Alerte : ';
  if ($contact->birthday_alert)
    {
      print '<input type="checkbox" name="birthday_alert" checked></td></tr>';
    }
  else
    {
      print '<input type="checkbox" name="birthday_alert"></td></tr>';
    }

  print '<tr><td align="center" colspan="3"><input type="submit" value="Enregistrer"></td></tr>';
  print "</table>";

  print "</form>";
}
else
{
  /*
   * Visualisation de la fiche
   *
   */
    
  $contact = new Contact($db);
  $contact->fetch($_GET["id"], $user);
  
  print_fiche_titre ("Contact : ". $contact->firstname.' '.$contact->name);

  if ($contact->socid > 0)
    {
      $objsoc = new Societe($db);
      $objsoc->fetch($contact->socid);

      print 'Soci�t� : '.$objsoc->nom.'<br>';
    }

  print 'Nom : '.$contact->name.' '.$contact->firstname ."<br>";

  if ($contact->poste)
    print 'Poste : '.$contact->poste ."<br>";

  if ($contact->email)
    print 'Email : '.$contact->email ."<br>";

  if ($contact->birthday)
    print 'Date de naissance : '.strftime("%d %B %Y",$contact->birthday);

  if ($contact->birthday_alert)
    print ' (alerte)';

  print "<br>";

  print "</div>";

  if ($user->societe_id == 0)
    {
      print '<div class="tabsAction">';
      
      print '<a class="tabAction" href="perso.php?id='.$_GET["id"].'&amp;action=edit">Editer</a>';    

      print '<a class="tabAction" href="fiche.php?id='.$_GET["id"].'&amp;action=deleteWARNING">Supprimer</a>';
      
      print "</div>";      
    }
}
  $db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
