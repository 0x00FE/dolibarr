<?PHP
/* Copyright (C) 2001-2002 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 *
 * $Id$
 * $Source$
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
 */
require("../main.inc.php3");

function llxHeader($head = "", $urlp = "") {
  global $user, $conf;

  /*
   *
   *
   */
  top_menu($head);

  print "<TR><TD valign=\"top\" align=\"right\" class=\"barre\">";

  print "<TABLE border=\"1\" cellspacing=\"0\" width=\"100%\" cellpadding=\"3\">";

  print '<TR><TD class="barre" valign="top" align="right">';
  print "<div align=\"center\"><A href=\"".$urlp."index.php3\">Clients</A></div>\n";
  print "<A href=\"".$urlp."../soc.php3?&action=create\">Nouvelle soci�t�</A><BR>\n";
  print "<A href=\"".$urlp."contact.php3\">Contacts</A><BR><br>\n";
  print "<A href=\"".$urlp."recontact.php3\">A Recontacter</A><BR>\n";
  print "</TD></TR>";


  print '<TR><TD class="barre" valign="top" align="right">';
  print "<div align=\"center\"><A href=\"".$urlp."actioncomm.php3\">Actions</A></div>\n";
  print "</TD></TR>";
  /*
   *
   */
  print '<TR><TD class="barre_select" valign="top" align="right">';
  print '<div align="center"><A href="'.$urlp.'propal.php3">Propal</A></div>';
  print '<A href="'.$urlp.'propal.php3?viewstatut=0">Brouillon</A><br>';
  print '<A href="'.$urlp.'propal.php3?viewstatut=1">Ouvertes</A>';
  print "</TD></TR>";
  /*
   *
   */
  print '<TR><TD class="barre" valign="top" align="right">';
  print '<div align="center"><A href="'.$urlp.'../compta/">Factures</A></div>';
  print "</TD></TR>";
  /*
   *
   */
  print '<TR><TD class="barre" valign="top" align="right">';
  print '<div align="center"><A href="'.$urlp.'../product/">Produits</A></div>';
  print '<div align="center"><A href="'.$urlp.'../service/">Services</A></div>';
  print "</td></tr>";
  /*
   *
   */
  print '<TR><TD class="barre" valign="top" align="right">';
  print '<div align="center"><A href="projet/">Projets</A></div>';
  print "</td></tr>";
  /*
   *
   */
  print '<TR><TD class="barre" valign="top" align="right">';
  print "<CENTER><A href=\"".$urlp."index.php3\">Societes</A></CENTER>\n";
  print "<form action=\"index.php3\">";
  print '<input type="hidden" name="mode" value="search">';
  print '<input type="hidden" name="mode-search" value="soc">';
  print '<input type="text" name="socname" size="8">&nbsp;';
  print "<input type=\"submit\" value=\"go\">";
  print "</form>";

  print "<CENTER><A href=\"".$urlp."contact.php3\">Contacts</A></CENTER>\n";
  print "<form action=\"".$urlp."contact.php3\">";
  print '<input type="hidden" name="mode" value="search">';
  print '<input type="hidden" name="mode-search" value="contact">';
  print "<input type=\"text\" name=\"contactname\" size=\"8\">&nbsp;";
  print "<input type=\"submit\" value=\"go\">";
  print "</form>";

  print "</td></tr>";

  print "</table>";

  print "</td>";


  print "<TD valign=\"top\" width=\"85%\">\n";
}

/*
 * $Id$
 * $Source$
 */
?>
