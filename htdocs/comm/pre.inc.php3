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

$root = "/$PREFIX";

require ("/$GLJ_WWW_ROOT/conf/$GLJ_PREFIX.$GLJ_COUNTRY.inc.php3");
require ("/$GLJ_WWW_ROOT/../www/lib/db.lib.php3");
require("../main.inc.php3");

function llxHeader($head = "", $urlp = "") {
  global $PREFIX, $user;

  print "<HTML>\n<HEAD>$head\n</HEAD>\n";
  ?>
  <BODY BGCOLOR="#c0c0c0" TOPMARGIN="0" BOTTOMMARGIN="0" LEFTMARGIN="0" RIGHTMARGIN="0" MARGINHEIGHT="0" MARGINWIDTH="0">
  <?PHP

  print "<TABLE border=\"0\" width=\"100%\">\n";
  print "<TR bgcolor=\"".$GLOBALS["TOPBAR_BGCOLOR"]."\">";
  
  print "<TD width=\"20%\" bgcolor=\"#e0e0e0\"><B>" . $GLOBALS["MAIN_TITLE"] . "</B></TD>";
  print "<TD width=\"20%\" bgcolor=\"#e0e0e0\" align=\"center\"><A href=\"".$urlp."../tech/\">Technique</A></TD>";
  print "<TD width=\"20%\" align=\"center\"><A href=\"".$urlp."../comm/\">Commercial</A></TD>";
  print "<TD width=\"20%\" bgcolor=\"#e0e0e0\" align=\"center\"><A href=\"".$urlp."../compta/\">Compta</A></TD>";
  print "<TD width=\"20%\" bgcolor=\"#e0e0e0\" align=\"center\"><A href=\"".$urlp."../stats/\">Stats</A></TD>";
  print "</TR></TABLE>\n";

  print "<TABLE border=\"1\" width=\"100%\" cellpadding=\"0\">";
  print "<TR><TD valign=\"top\" align=\"right\">";

  print "<TABLE border=\"1\" cellspacing=\"0\" width=\"100%\" cellpadding=\"3\">";
  print "<TR><TD valign=\"top\" align=\"right\">";
  print "<center><b>" . $GLOBALS["dbname"] . " - " . $user->code ."</B></center>";
  print "<A href=\"".$urlp."../\">Accueil</A><br>";
  print "<A href=\"".$urlp."bookmark.php3\">Bookmark</A>";
  print "</td></tr>";
  print "<tr><td valign=\"top\" align=\"right\">";

  print "<CENTER><A href=\"".$urlp."index.php3\">Societe</A></CENTER>\n";
  print "<A href=\"".$urlp."index.php3?stcomm=1\">A contacter</A><BR>\n";
  print "<A href=\"".$urlp."index.php3?stcomm=0\">Jamais contact�e</A><BR>\n";
  print "<A href=\"".$urlp."index.php3?stcomm=-1\">Ne pas contacter</A><BR>\n";
  print "<A href=\"".$urlp."index.php3?stcomm=2\">Contact en cours</A><BR>\n";
  print "<A href=\"".$urlp."index.php3?stcomm=3\">Contact�e</A><p>\n";
  print "<A href=\"".$urlp."relance.php3\">A relancer</A><BR>\n";
  print "<A href=\"".$urlp."recontact.php3\">A recontacter</A><BR>\n";
  print "<A href=\"".$urlp."index.php3?aclasser=1\">A classer</A><p>\n";

  print "<A href=\"".$urlp."topcontact.php3\">Gourmands</A><BR>\n";
  print "<A href=\"".$urlp."contact.php3\">Contact</A><BR>\n";
  print "</TD></TR>";


  print "<TR><TD valign=\"top\" align=\"right\">";
  print "<div align=\"center\"><A href=\"".$urlp."actioncomm.php3\">Actions</A></div>\n";
  print "<A href=\"".$urlp."actioncomm.php3?type=9\">Factures</A><BR>\n";
  print "<A href=\"".$urlp."actioncomm.php3?type=5\">Propal FE</A><BR>\n";
  print "<A href=\"".$urlp."actioncomm.php3?type=11\">Cl&ocirc;ture</A><p>\n";
  print "</TD></TR>";
  /*
   *
   */
  print "<TR><TD valign=\"top\" align=\"right\">";
  print "<div align=\"center\"><A href=\"".$urlp."propal.php3\">Propal</A></div>\n";
  print "<A href=\"".$urlp."analyse.php3\">Recap</A><br>\n";
  print "</TD></TR>";
  /*
   *
   */
  print "<TR><TD valign=\"top\" align=\"right\" bgcolor=\"#e0e0e0\">";

  print "<A href=\"".$urlp."../compta/\">Factures</A><BR>\n";
  print "<center><A href=\"".$urlp."ventes.php3\">Ventes</A></center>\n";
  print "<A href=\"".$urlp."ventes_soc.php3\">Par soci�t�s</A><BR>\n";
  print "<A href=\"".$urlp."product.php3\">Produits</A><BR>\n";

  print "</td></tr>";

  print "<tr><td align=\"right\" valign=\"top\">";
  print "<A href=\"projet/\">Projets</A><BR>\n";
  print "</td></tr>";


  print "<tr><td align=\"right\" valign=\"top\">";
  print "<A href=\"".$urlp."stats/\">Stats</A><BR>\n";
  print "</td></tr>";

  print "<tr><td align=\"right\" valign=\"top\">";
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


  print "<form action=\"index.php3\">";
  print "Soc : <input type=\"text\" name=\"socid\" size=\"5\">";
  print "<input type=\"submit\" value=\"id\">";
  print "</form>";
  print "</td></tr>";

  print "</table>";

  print "</td>";


  print "<TD valign=\"top\" width=\"85%\">\n";
}

function llxFooter($foot='') {
  print "</TD></TR>";
  /*
   *
   */
  print "</TABLE>\n";
  print "$foot</BODY></HTML>";
}
/*
 * $Id$
 * $Source$
 */
?>
