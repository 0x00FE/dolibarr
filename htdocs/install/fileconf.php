<?PHP
/* Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org> 
 * Copyright (C) 2004 �ric Seigne <eric.seigne@ryxeo.com>
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

include("./inc.php");
pHeader("Fichier de configuration","fileconf");


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
      @fwrite($fp, '<?PHP');
      @fputs($fp,"\n");
      @fputs($fp,"?>");
      fclose($fp);
    }
}

$docurl = '<a href="doc/dolibarr-install.html">documentation</a>';

if (!file_exists("../conf/conf.php"))
{
  print '<div class="error">';
  print "Le fichier <b>conf.php</b> n'existe pas reportez-vous � la ".$docurl." pour cr�er ce fichier<br>";
  print '</div>';
  $err++;
}
else
{

  if (!is_writable("../conf/conf.php"))
    {
      print '<div class="error">';
      print "Le fichier <b>conf.php</b> n'est pas accessible en �criture, v�rififiez les droits sur celui-ci, reportez-vous � la ".$docurl."<br>";
      
      print '</div>';
      $err++;
    }
}

if ($err == 0)
{
?>



<table border="0" cellpadding="4" cellspacing="0">
<tr>
<td valign="top" class="label">
<?php print "R�pertoire d'installation"; ?>
</td><td  class="label" valign="top"><input type="text" size="60" value="
<?PHP

if(strlen($dolibarr_main_url_root) == 0)
{
$dolibarr_main_document_root = dirname($_SERVER["SCRIPT_FILENAME"]);
}
 print $dolibarr_main_document_root 
?>
" name="main_dir">
</td><td class="label">
Sans le slash "/" � la fin<br>
exemple : /var/www/dolibarr/htdocs

</td>
</tr>

<tr>
<td valign="top" class="label">
URL Racine</td><td valign="top" class="label"><input type="text" size="60" name="main_url" value="
<?PHP 
if(strlen($main_url) > 0)
  $dolibarr_main_url_root=$main_url;
if(strlen($dolibarr_main_url_root) == 0)
{
  $dolibarr_main_url_root = substr($_SERVER["SCRIPT_URI"],0,strlen($_SERVER["SCRIPT_URI"])-9);
}

print $dolibarr_main_url_root ;

?>">
</td><td class="label">
exemples : 
<ul>
<li>http://dolibarr.lafrere.net</li>
<li>http://www.lafrere.net/dolibarr</li>
</ul>
</tr>

<tr>
<td colspan="3" align="center"><h2>Base de donn�es<h2></td>
</tr>

<tr>
<td valign="top" class="label">Serveur</td>
<td valign="top" class="label"><input type="text" name="db_host" value="<?PHP isset($db_host) ? print $db_host : print $dolibarr_main_db_host ; ?>"></td>
<td class="label"><div class="comment">Nom du serveur de base de donn�es, g�n�ralement 'localhost' quand le serveur est install� sur la m�me machine que le serveur web</div></td>
</tr>

<tr>
<td class="label">Nom de la base</td>
<td class="label" valign="top"><input type="text" name="db_name" value="<?PHP isset($db_name) ? print $db_name : print $dolibarr_main_db_name ; ?>"></td>
<td class="label"><div class="comment">Nom de votre base de donn�es</div></td>
</tr>

<tr class="bg1">
<td class="label" valign="top">Login</td>
<td class="label"><input type="text" name="db_user" value="<?PHP isset($db_user) ? print $db_user : print $dolibarr_main_db_user ; ?>"></td>
<td class="label"><div class="comment">Laisser vide si vous vous connectez en anonyme</div></td>
</tr>

<tr>
<td class="label" valign="top">Mot de passe</td>
<td class="label"><input type="text" name="db_pass" value="<?PHP isset($db_pass) ? print $db_pass : print $dolibarr_main_db_pass ; ?>"></td>
<td class="label"><div class="comment">Laisser vide si vous vous connectez en anonyme</div>
</td>
</tr>

<tr>
<td class="label" valign="top">Cr�er l'utilisateur</td>
<td class="label"><input type="checkbox" name="db_create_user" <?PHP if(isset($db_create_user)) print 'checked';?> ></td>
<td class="label"><div class="comment">Cocher cette option si l'utilisateur doit-�tre cr��</div>
</td>
</tr>



<tr>
<td colspan="3" align="center"><h2>Base de donn�es - Acc�s super utilisateur</h2></td></tr>

<tr>
<td class="label" valign="top">Login</td>
<td class="label"><input type="text" name="db_user_root" value="<?PHP if(isset($db_user_root)) print $db_user_root; ?>"></td>
<td class="label"><div class="comment">Login de l'utilisateur ayant les droits de cr�ation de la base de donn�es, inutile si vous �tes chez un h�bergeur, votre base de donn�es est d�j� cr��e. Laisser vide si vous vous connectez en anonymous</div>
</td>
</tr>

<tr>
<td class="label" valign="top">Mot de passe</td>
<td class="label"><input type="text" name="db_pass_root" value="<?PHP if(isset($db_pass_root)) print $db_pass_root; ?>"></td>
<td class="label"><div class="comment">Laisser vide si l'utilisateur n'a pas de mot de passe</div>
</td>
</tr>

</table>
</div>
</div>
<?PHP
}
pFooter($err);
?>
