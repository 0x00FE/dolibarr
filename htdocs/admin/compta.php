<?PHP
/* Copyright (C) 2004 Laurent Destailleur  <eldy@users.sourceforge.net>
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
require("./pre.inc.php");

if (!$user->admin)
  accessforbidden();


llxHeader();


$compta_mode = defined("COMPTA_MODE")?COMPTA_MODE:"RECETTES-DEPENSES";

if ($action == 'setcomptamode')
{
  $compta_mode = $HTTP_POST_VARS["compta_mode"];
  dolibarr_set_const($db, "COMPTA_MODE",$compta_mode);
}


print_titre("Module de comptabilit�");

print '<table class="noborder" cellpadding="3" cellspacing="0" width=\"100%\">';

print '<form action="'.$PHP_SELF.'" method="post">';
print '<input type="hidden" name="action" value="setcomptamode">';
print '<TR class="liste_titre">';
print '<td>Option de tenue de comptabilit�</td><td>Description</td>';
print '<td><input type="submit" value="Modifier"></td>';
print "</TR>\n";
print "<tr ".$bc[True]."><td width=\"200\"><input type=\"radio\" name=\"compta_mode\" value=\"RECETTES-DEPENSES\"".($compta_mode != "CREANCES-DETTES"?" checked":"")."> Option Recettes-D�penses</td>";
print "<td colspan=\"2\">Dans ce mode, le CA est calcul� sur la base des factures � l'�tat pay�.\nLa validit� des chiffres n'est donc assur�e que si la tenue de la comptabilit� passe rigoureusement par des entr�es/sorties sur les comptes via des factures.\nDe plus, dans cette version, Dolibarr utilise la date de passage de la facture � l'�tat 'Valid�' et non la date de passage � l'�tat 'Pay�'.</td></tr>\n";
print "<tr ".$bc[False]."><td width=\"200\"><input type=\"radio\" name=\"compta_mode\" value=\"CREANCES-DETTES\"".($compta_mode == "CREANCES-DETTES"?" checked":"")."> Option Cr�ances-Dettes</td>";
print "<td colspan=\"2\">Dans ce mode, le CA est calcul� sur la base des factures valid�es. Qu'elles soient ou non pay�s, d�s lors qu'elles sont dues, elles apparaissent dans le r�sultat.</td></tr>\n";
print "</form>";
print "</table>";

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
