<?php
/* Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org> 
 * Copyright (C) 2004 Laurent Destailleur  <eldy@users.sourceforge.net>
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
    \file       htdocs/install/index.php
    \brief      Test si le fichier conf est modifiable et si il n'existe pas, test la possibilit� de le cr�er
    \version    $Revision$
*/

include("./inc.php");
pHeader("Bienvenu dans Dolibarr", "licence");   // Etape suivante = license


print "Nous avons fait en sorte que l'installation soit le plus simple possible, vous n'avez qu'� suivre les �tapes une � une.";


$conf = "../conf/conf.php";


if (is_readable($conf))
{
  include ($conf);
}
else
{
  $fp = @fopen("$conf", "w");
  if($fp)
    {
      @fwrite($fp, '<?php');
      @fputs($fp,"\n");
      @fputs($fp,"?>");
      fclose($fp);
    }
}

if (!file_exists($conf))
{
  print "<br /><br />Le fichier de configuration <b>conf.php</b> n'existe pas !<br />";
  print "Vous devez cr�er un fichier <b>htdocs/conf/conf.php</b> et donner les droits d'�criture dans celui-ci au serveur Apache.<br />";

  $err++;
}
else
{
  if (!is_writable("../conf/conf.php"))
    {
      print "<br /><br />Le fichier de configuration <b>conf.php</b> existe.<br />";
      print "Le fichier <b>conf.php</b> n'est pas accessible en �criture, v�rifiez les droits sur celui-ci, le serveur Apache doit avoir le droit d'�crire dans ce fichier le temps de la configuration (chmod 666 par exemple)<br>";
      

      $err++;
    }
  else
    {
      print "<br /><br />Le fichier de configuration <b>conf.php</b> existe.<br />";
      print "Le fichier <b>conf.php</b> est accessible en �criture<br /><br />Vous pouvez continuer...";

    }
}

// Si pas d'erreur, on affiche le bouton pour passer � l'�tape suivante
if ($err == 0) pFooter();

?>
