<?PHP
/* Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2002-2003 Jean-Louis Bergamo <jlb@j1b.org>
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
require($GLOBALS["DOCUMENT_ROOT"]."/adherent.class.php");
require($GLOBALS["DOCUMENT_ROOT"]."/adherent_type.class.php");
require($GLOBALS["DOCUMENT_ROOT"]."/cotisation.class.php");
require($GLOBALS["DOCUMENT_ROOT"]."/paiement.class.php");
require($GLOBALS["DOCUMENT_ROOT"]."/adherents/adherent_options.class.php");


$db = new Db();
$adho = new AdherentOptions($db);

llxHeader();

/* ************************************************************************** */
/*                                                                            */
/* Edition de la fiche                                                        */
/*                                                                            */
/*************************************************************************** */
// fetch optionals attributes and labels
$adho->fetch_optionals();
if ($rowid > 0)
{

  $adh = new Adherent($db);
  $adh->id = $rowid;
  $adh->fetch($rowid);
  $adh->fetch_optionals($rowid);

  print_titre("Fiche adh�rent de $adh->prenom $adh->nom");

  print '<table cellspacing="0" border="1" width="100%" cellpadding="3">';

  print "<tr><td>Type</td><td class=\"valeur\">$adh->type</td>\n";
  print '<td valign="top" width="50%">Commentaires</tr>';

  print '<tr><td>Personne</td><td class="valeur">'.$adh->morphy.'&nbsp;</td>';

  print '<td rowspan="13" valign="top" width="50%">';
  print nl2br($adh->commentaire).'&nbsp;</td></tr>';

  print '<tr><td width="15%">Pr�nom</td><td class="valeur" width="35%">'.$adh->prenom.'&nbsp;</td></tr>';

  print '<tr><td>Nom</td><td class="valeur">'.$adh->nom.'&nbsp;</td></tr>';
  

  print '<tr><td>Soci�t�</td><td class="valeur">'.$adh->societe.'&nbsp;</td></tr>';
  print '<tr><td>Adresse</td><td class="valeur">'.nl2br($adh->adresse).'&nbsp;</td></tr>';
  print '<tr><td>CP Ville</td><td class="valeur">'.$adh->cp.' '.$adh->ville.'&nbsp;</td></tr>';
  print '<tr><td>Pays</td><td class="valeur">'.$adh->pays.'&nbsp;</td></tr>';
  print '<tr><td>Email</td><td class="valeur">'.$adh->email.'&nbsp;</td></tr>';
  print '<tr><td>Date de Naissance</td><td class="valeur">'.$adh->naiss.'&nbsp;</td></tr>';
  if (isset($adh->photo) && $adh->photo !=''){
    print '<tr><td>URL Photo</td><td class="valeur">'."<A HREF=\"$adh->photo\"><IMG SRC=\"$adh->photo\"></A>".'&nbsp;</td></tr>';
  }
  //  foreach($adho->attribute_label as $key=>$value){
  //    print "<tr><td>$value</td><td>".$adh->array_options["options_$key"]."&nbsp;</td></tr>\n";
  //  }
  print '</table>';


}

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
