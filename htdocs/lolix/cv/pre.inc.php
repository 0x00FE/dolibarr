<?PHP
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
require("../../main.inc.php");
require("./cv.class.php");

function llxHeader($head = "", $urlp = "",  $title="")
{
  global $user, $conf, $db;

  /*
   *
   *
   */
  top_menu($head, $title);

  $menu = new Menu();

  $menu->add(DOL_URL_ROOT."/lolix/index.php", "Lolix");

  $menu->add_submenu(DOL_URL_ROOT."/lolix/cv/liste.php", "Liste des CV");
  left_menu($menu->liste);
}

Function fiche_header($id)
{
  $h = 0;
  $head[0][0] = DOL_URL_ROOT.'/lolix/offre.php?id='.$id;
  $head[0][1] = "Offre";
  $h++;

  for($i = 0 ; $i < sizeof($head) ; $i++)
    {
      if (strstr($head[$i][0], $GLOBALS["SCRIPT_URL"]) )
	{
	  $a = $i;
	  // sort de la boucle
	  $i = sizeof($head);
	}
    }

  dolibarr_fiche_head($head, $a);
}
?>
