<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      �ric Seigne          <erics@rycks.com>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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

/**
	    \file       htdocs/includes/boxes/box_external_rss.php
        \ingroup    external_rss
		\brief      Fichier de gestion d'une box pour le module external_rss
		\version    $Revision$
*/

require_once("includes/magpierss/rss_fetch.inc");

for($site = 0; $site < 1; $site++) {
  $info_box_head = array();
  $info_box_head[] = array('text' => "Les 5 derni�res infos du site " . @constant("EXTERNAL_RSS_TITLE_". $site));
  $info_box_contents = array();
  $rss = fetch_rss( @constant("EXTERNAL_RSS_URLRSS_" . $site) );
  for($i = 0; $i < 5 ; $i++){
    $item = $rss->items[$i];
    $href = $item['link'];
    $title = utf8_decode(urldecode($item['title']));
    $title=ereg_replace("([[:alnum:]])\?([[:alnum:]])","\\1'\\2",$title);   // G�re probl�me des apostrophes mal cod�e/d�cod�e par utf8
    $title=ereg_replace("^\s+","",$title);                                  // Supprime espaces de d�but
    $info_box_contents["$href"]="$title";
    $info_box_contents[$i][0] = array('align' => 'left',
				      'logo' => 'object_rss',
				      'text' => $title,
				      'url' => $href);
  } 
  new infoBox($info_box_head, $info_box_contents);
}

?>
