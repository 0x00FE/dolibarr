<?PHP
/* Copyright (C) 2001-2002 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 *                         Jean-Louis Bergamo <jlb@j1b.org>
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
require("../../adherent.class.php");
require("../../adherent_type.class.php");
require("../../cotisation.class.php");
require("../../paiement.class.php");


$db = new Db();

if ($HTTP_POST_VARS["action"] == 'add') 
{

  $adh = new Adherent($db);
  $adh->statut      = -1;
  $adh->prenom      = $prenom;
  $adh->nom         = $nom;  
  $adh->societe     = $societe;
  $adh->adresse     = $adresse;
  $adh->cp          = $cp;
  $adh->ville       = $ville;
  $adh->email       = $email;
  $adh->login       = $login;
  $adh->pass        = $pass;
  $adh->note        = $note;
  $adh->pays        = $pays;
  $adh->typeid      = $type;
  $adh->commentaire = $HTTP_POST_VARS["comment"];
  $adh->morphy      = $HTTP_POST_VARS["morphy"];
  
  if ($adh->create($user->id) ) 
    {	  
      if ($cotisation > 0)
	{     
	  $adh->cotisation(mktime(12, 0 , 0, $remonth, $reday, $reyear), $cotisation);
	}
      Header("Location: new.php");
    }
}

llxHeader("Nouvel Adherent");
// Header
/*
print "<HTML><HEAD>";
print '<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1">';
print '<LINK REL="stylesheet" TYPE="text/css" HREF="/'.$conf->css.'">';
print "\n";
print '<title>Dolibarr</title>';
print "\n";


print "</HEAD>\n";

print '<BODY TOPMARGIN="0" BOTTOMMARGIN="0" LEFTMARGIN="0" RIGHTMARGIN="0" MARGINHEIGHT="0" MARGINWIDTH="0">';

print '<TABLE border="0" width="100%" cellspacing="0" cellpadding="3">';

print '<tr><td valign="top" align="right">';
*/
/* ************************************************************************** */
/*                                                                            */
/* Cr�ation d'une fiche                                                       */
/*                                                                            */
/* ************************************************************************** */


  $sql = "SELECT s.nom,s.idp, f.amount, f.total, f.facnumber";
  $sql .= " FROM societe as s, llx_facture as f WHERE f.fk_soc = s.idp";
  $sql .= " AND f.rowid = $facid";

  $result = $db->query($sql);
  if ($result) {
    $num = $db->num_rows();
    if ($num) {
      $obj = $db->fetch_object( 0);

      $total = $obj->total;
    }
  }
  print_titre("Nouvel adh�rent");
  print "<form action=\"$PHP_SELF\" method=\"post\">\n";
  print '<table cellspacing="0" border="1" width="100%" cellpadding="3">';
  
  print '<input type="hidden" name="action" value="add">';

  $htmls = new Form($db);
  $adht = new AdherentType($db);

  print '<tr><td width="15%">Type</td><td width="35%">';
  $htmls->select_array("type",  $adht->liste_array());
  print "</td>\n";

  print '<td width="50%" valign="top">Commentaires :</td></tr>';

  $morphys["phy"] = "Physique";
  $morphys["mor"] = "Morale";

  print "<tr><td>Personne</td><td>\n";
  $htmls->select_array("morphy",  $morphys);
  print "</td>\n";
  
  print '<td valign="top" rowspan="11"><textarea name="comment" wrap="soft" cols="40" rows="25"></textarea></td></tr>';

  print '<tr><td>Pr�nom</td><td><input type="text" name="prenom" size="40"></td></tr>';  
  




  print '<tr><td>Nom</td><td><input type="text" name="nom" size="40"></td></tr>';
  print '<tr><td>Societe</td><td><input type="text" name="societe" size="40"></td></tr>';
  print '<tr><td>Adresse</td><td>';
  print '<textarea name="adresse" wrap="soft" cols="40" rows="3"></textarea></td></tr>';
  print '<tr><td>CP Ville</td><td><input type="text" name="cp" size="8"> <input type="text" name="ville" size="40"></td></tr>';
  print '<tr><td>Pays</td><td><input type="text" name="pays" size="40"></td></tr>';
  print '<tr><td>Email</td><td><input type="text" name="email" size="40"></td></tr>';
  print '<tr><td>Login</td><td><input type="text" name="login" size="40"></td></tr>';
  print '<tr><td>Password</td><td><input type="text" name="pass" size="40"></td></tr>';


  print '<tr><td colspan="2" align="center"><input type="submit" value="Enregistrer"></td></tr>';
  print "</form>\n";
  print "</table>\n";
  
      
$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
