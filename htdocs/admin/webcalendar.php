<?PHP
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003 �ric Seigne          <erics@rycks.com>
 * Copyright (C) 2004 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004 Benoit Mortier 			 <benoit.mortier@opensides.be>
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
require("../lib/webcal.class.php");

if (!$user->admin)
  accessforbidden();


llxHeader();

print_titre("Configuration du lien vers le calendrier partag�");
print '<br>';

$def = array();

// positionne la variable pour le test d'affichage de l'icone

if ($action == 'save')
{
	if(trim($phpwebcalendar_pass) == trim($phpwebcalendar_pass2))
		{
			$conf = new Conf();
      $conf->db->host = $phpwebcalendar_host;
      $conf->db->name = $phpwebcalendar_dbname;
      $conf->db->user = $phpwebcalendar_user;
      $conf->db->pass = $phpwebcalendar_pass;

	  	//print $conf->db->host.",".$conf->db->name.",".$conf->db->user.",".$conf->db->pass;

			$webcal = new DoliDb();

	  	if ($webcal->connected == 1)
				{
    			$sql = "REPLACE INTO ".MAIN_DB_PREFIX."const SET name =
					'PHPWEBCALENDAR_URL',value='".$phpwebcalendar_url."', visible=0";

					$sql1 = "REPLACE INTO ".MAIN_DB_PREFIX."const SET name =
					'PHPWEBCALENDAR_HOST',value='".$phpwebcalendar_host."', visible=0";

					$sql2 = "REPLACE INTO ".MAIN_DB_PREFIX."const SET name = 'PHPWEBCALENDAR_DBNAME',
					value='".$phpwebcalendar_dbname."', visible=0";

					$sql3 = "REPLACE INTO ".MAIN_DB_PREFIX."const SET name = 'PHPWEBCALENDAR_USER',
					value='".$phpwebcalendar_user."', visible=0";

					$sql4 = "REPLACE INTO ".MAIN_DB_PREFIX."const SET name = 'PHPWEBCALENDAR_PASS',
					value='".$phpwebcalendar_pass."', visible=0";

					if ($db->query($sql) && $db->query($sql1) && $db->query($sql2) && $db->query($sql3) &&
					$db->query($sql4))
      			{

						// la constante qui a �t� lue en avant du nouveau set
						// on passe donc par une variable pour avoir un affichage coh�rent

						define("PHPWEBCALENDAR_URL",  $phpwebcalendar_url);
						define("PHPWEBCALENDAR_HOST",  $phpwebcalendar_host);
						define("PHPWEBCALENDAR_DBNAME",  $phpwebcalendar_dbname);
						define("PHPWEBCALENDAR_USER",  $phpwebcalendar_user);
						define("PHPWEBCALENDAR_PASS",  $phpwebcalendar_pass);

						print "<p>la connection � la base de donn�es webcalendar $phpwebcalendar_dbname �
						r�ussi</p><br>";
		      	}
    			else
						print "<p>erreur d'enregistement dans la base de donn�es $db !</p><br>";
				}
			else
					print "<p>la connection � la base de donn�es webcalendar $phpwebcalendar_dbname �
					�chou�</p><br>";
  	}
  else
   	{
			print "<p>le mot de passe n'est pas identique, veuillez le reintroduire</p><br>\n";
 		}
}


/**
	* Affichage du formulaire de saisie
	*/

print "\n<form name=\"phpwebcalendarconfig\" action=\"" . $PHP_SELF . "\" method=\"post\">
<table class=\"noborder\" cellpadding=\"3\" cellspacing=\"1\">
<tr class=\"liste_titre\">
<td>Param�tre</td>
<td>Valeur</td>
</tr>
<tr class=\"impair\">
  <td>Adresse URL d'acc�s au calendrier</td>
  <td><input type=\"text\" name=\"phpwebcalendar_url\" value=\"". PHPWEBCALENDAR_URL . "\" size=\"45\"></td>
</tr>
<tr class=\"pair\">
  <td>Serveur o� la base du calendrier est h�berg�e</td>
  <td><input type=\"text\" name=\"phpwebcalendar_host\" value=\"". PHPWEBCALENDAR_HOST . "\" size=\"45\"></td>
</tr>
<tr class=\"impair\">
  <td>Nom de la base de donn�es</td>
  <td><input type=\"text\" name=\"phpwebcalendar_dbname\" value=\"". PHPWEBCALENDAR_DBNAME . "\" size=\"45\"></td>
</tr>
<tr class=\"pair\">
  <td>Identifiant d'acc�s � la base</td>
  <td><input type=\"text\" name=\"phpwebcalendar_user\" value=\"". PHPWEBCALENDAR_USER . "\" size=\"45\"></td>
</tr>
<tr class=\"impair\">
  <td>Mot de passe d'acc�s � la base</td>
  <td><input type=\"password\" name=\"phpwebcalendar_pass\" value=\"" . PHPWEBCALENDAR_PASS . "\" size=\"45\"></td>
</tr>
<tr class=\"pair\">
  <td>Mot de passe (v�rification)</td>
  <td><input type=\"password\" name=\"phpwebcalendar_pass2\" value=\"" . PHPWEBCALENDAR_PASS ."\" size=\"45\"></td>
</tr>
<tr class=\"impair\">
<td colspan=\"2\"><input type=\"submit\" name=\"envoyer\" value=\"Enregistrer\"></td>
</tr>\n";

  clearstatcache();
  
  print "
</table>
<input type=\"hidden\" name=\"action\" value=\"save\">
</form>\n";


/*
 *
 *
 */


$db->close();

llxFooter();
?>
