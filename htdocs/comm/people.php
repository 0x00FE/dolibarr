<?PHP
/* Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

if ($sortorder == "") 
{
  $sortfield="lower(s.nom)";
  $sortorder="ASC";
}

if ($action == 'add') 
{
  $email = trim($email);

  if (strlen(trim($name)) + strlen(trim($firstname)) > 0) 
    {
      $sql = "INSERT INTO llx_socpeople (datec, fk_soc,name, firstname, poste, phone,fax,email) ";
      $sql .= " VALUES (now(),$socid,'$name','$firstname','$poste','$phone','$fax','$email')";
      $result = $db->query($sql);
      if ($result) 
	{
	  Header("Location: fiche.php?socid=$socid");
      }
    }
}
if ($action == 'update') 
{
  if (strlen(trim($name)) + strlen(trim($firstname)) > 0) 
    {    
      $contact = new Contact($db);
      $contact->name = $name;
      $contact->firstname = $firstname;
      $contact->poste = $poste;
      $contact->phone = $phone;
      $contact->fax = $fax;
      $contact->note = $note;
      $contact->email = $email;

      $result = $contact->update($contactid);
      if ($result) 
	{
	  Header("Location: fiche.php?socid=$socid");
	}
    }
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

if ($socid > 0) 
{

  $sql = "SELECT s.idp, s.nom, ".$db->pdate("s.datec")." as dc, s.tel, s.fax, st.libelle as stcomm, s.fk_stcomm, s.url,s.cp,s.ville, s.note FROM llx_societe as s, c_stcomm as st ";
  $sql .= " WHERE s.fk_stcomm=st.id";
  $sql .= " AND s.idp = $socid";


  $result = $db->query($sql);

  if ($result) 
    {
      $objsoc = $db->fetch_object( 0);

      /*
       *
       *
       */
      print_fiche_titre ("Contact soci�t� : <a href=\"fiche.php?socid=$objsoc->idp\">$objsoc->nom</a>");
      /*
       *
       */

      if ($objsoc->note)
	{
	  print '<table border=0 width="100%" cellspacing="0">';
	  print "<tr><td>".nl2br($objsoc->note)."</td></tr>";
	  print "</table>";
	}

    }
  else
    {
      print $db->error();
    }
  
  print "<P><table class=\"tablefuser\" width=\"100%\" cellspacing=0 border=1 cellpadding=2>";
  
  print "<tr><td><b>Pr�nom Nom</b></td>";
  print "<td><b>Poste</b></td><td><b>Tel</b></td>";
  print "<td><b>Fax</b></td><td><b>Email</b></td>";
  
  $sql = "SELECT p.name, p.firstname, p.poste, p.phone, p.fax, p.email, p.fk_user ";
  $sql .= " FROM llx_socpeople as p WHERE p.fk_soc = $objsoc->idp";
  
  if ($contactid) 
    {
      $sql .= " AND p.idp = $contactid";
    }

  $sql .= "   ORDER by p.datec";
  $result = $db->query($sql);
  $i = 0 ; $num = $db->num_rows(); $tag = True;

  while ($i < $num) 
    {
      $obj = $db->fetch_object( $i);
      print "<tr>";
      print "<td>$obj->firstname $obj->name</td>";
      print "<td>$obj->poste&nbsp;</td>";
      print "<td>$obj->phone&nbsp;</td>";
      print "<td>$obj->fax&nbsp;</td>";
      print "<td><a href=\"mailto:$obj->email\">$obj->email</a>&nbsp;</td>";
      print "</tr>\n";
      $i++;
      $tag = !$tag;
    }
  if ($contactid)
    {
      if ($obj->fk_user)
	{
	  print '<tr><td>Login</td><td colspan="4"><a href="'.DOL_URL_ROOT.'/user/fiche.php?id='.$obj->fk_user.'">Fiche</a></td></tr>';
	}
      else
	{
	  print '<tr><td>Login</td><td colspan="3">Pas de compte</td>';
	  print '<td align="center"><a href="people.php?contactid='.$contactid.'&socid='.$socid.'&action=create_user">Cr�er un compte</td></tr>';
	}
    }

  print "</table>";
  
  
  if ($action == 'addcontact') 
    {
      print "<form method=\"post\" action=\"people.php?socid=$socid\">";
      print "<input type=\"hidden\" name=\"action\" value=\"add\">";
      print "<table class=\"tablefuser\" border=0>";
      print "<tr><td>Nom</td><td><input name=\"name\" type=\"text\" size=\"20\" maxlength=\"80\"></td>";
      print "<td>Prenom</td><td><input name=\"firstname\" type=\"text\" size=\"15\" maxlength=\"80\"></td></tr>";
      print "<tr><td>Poste</td><td colspan=\"3\"><input name=\"poste\" type=\"text\" size=\"50\" maxlength=\"80\"></td></tr>";
      print "<tr><td>Tel</td><td><input name=\"phone\" type=\"text\" size=\"18\" maxlength=\"80\"></td>";
      print "<td>Fax</td><td><input name=\"fax\" type=\"text\" size=\"18\" maxlength=\"80\"></td></tr>";
      print "<tr><td>Email</td><td colspan=\"3\"><input name=\"email\" type=\"text\" size=\"50\" maxlength=\"80\"></td></tr>";
      print "</table>";
      print "<input type=\"submit\" value=\"Ajouter\">";
      print "</form>";
    }
  /*
   *
   * Edition du contact
   *
   */
  if ($action == 'editcontact') 
    {
      $sql = "SELECT p.idp, p.name, p.firstname, p.poste, p.phone, p.fax, p.email, p.note";
      $sql .= " FROM llx_socpeople as p WHERE p.idp = $contactid";
      $result = $db->query($sql);
      $num = $db->num_rows();

      if ( $num >0 ) 
	{
	  $obj = $db->fetch_object( 0);
	}
  
      print "<form method=\"post\" action=\"people.php?socid=$socid\">";
      print '<input type="hidden" name="action" value="update">';
      print "<input type=\"hidden\" name=\"contactid\" value=\"$contactid\">";
      print '<br><table class="tablefuser" border="1" cellpadding="4" cellspacing="0">';
      print "<tr><td>Nom</td><td><input name=\"name\" type=\"text\" size=\"20\" maxlength=\"80\" value=\"$obj->name\"></td>";
      print "<td>Prenom</td><td><input name=\"firstname\" type=\"text\" size=\"15\" maxlength=\"80\" value=\"$obj->firstname\"></td></tr>";
      print "<tr><td>Poste</td><td colspan=\"3\"><input name=\"poste\" type=\"text\" size=\"50\" maxlength=\"80\" value=\"$obj->poste\"></td></tr>";
      print "<tr><td>Tel</td><td><input name=\"phone\" type=\"text\" size=\"18\" maxlength=\"80\" value=\"$obj->phone\"></td>";
      print "<td>Fax</td><td><input name=\"fax\" type=\"text\" size=\"18\" maxlength=\"80\" value=\"$obj->fax\"></td></tr>";
      print "<tr><td>Email</td><td colspan=\"3\"><input name=\"email\" type=\"text\" size=\"50\" maxlength=\"80\" value=\"$obj->email\"></td></tr>";
      print '<tr><td valign="top">Note</td><td colspan="3"><textarea wrap="soft" cols="40" rows="6" name="note">'.$obj->note.'</textarea></td></tr>';
      print '<tr><td align="center" colspan="5"><input type="submit" value="Modifier"></td></tr>';

      print "</table>";
      print "</form>";
    }
  
  /*
   *
   *
   *
   *if (defined("MAIN_MODULE_FICHEINTER") && MAIN_MODULE_FICHEINTER)
   * {
   *   print "<p>";
   *   print_titre("Fiche d'intervention");
   * }
   *
   *
   *
   */

  print '<P><table width="100%" cellspacing="0" border="0" cellpadding="2">';
  
  print "<tr class=\"liste_titre\"><td>Action</td>";
  print "<td>Fax</td><td>Email</td>";

  $sql = "SELECT a.id, ".$db->pdate("a.datea")." as da, c.libelle, u.code, a.propalrowid, a.fk_user_author, fk_contact, u.rowid ";
  $sql .= " FROM llx_actioncomm as a, c_actioncomm as c, llx_user as u ";
  $sql .= " WHERE a.fk_soc = $objsoc->idp ";
  $sql .= " AND u.rowid = a.fk_user_author";
  $sql .= " AND c.id=a.fk_action ";

  if ($contactid) 
    {
      $sql .= " AND fk_contact = $contactid";
    }
  $sql .= " ORDER BY a.datea DESC, a.id DESC";

  if ( $db->query($sql) ) 
    {
      $i = 0 ; $num = $db->num_rows(); $tag = True;
      while ($i < $num) 
	{
	  $obj = $db->fetch_object( $i);
	  $var=!$var;
	  print "<tr $bc[$var]>";
	  
	  print "<td>".  strftime("%d %b %Y %H:%M", $obj->da)  ."</td>";
	  if ($obj->propalrowid) 
	    {
	      print "<td><a href=\"propal.php?propalid=$obj->propalrowid\">$obj->libelle</a></td>";
	    } 
	  else 
	    {
	      print "<td>$obj->libelle</td>";
	    }
	  
	  print "<td>$obj->code&nbsp;</td>";
	  print "</tr>\n";
	  $i++;
	  $tag = !$tag;
	}
    }
  else 
    {
      print '<tr><td>' . $db->error() . '</td></tr>';
    }
  print "</table>";

} 
else 
{  
  print "Error";
}

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
