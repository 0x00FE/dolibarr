<?php
/* Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org> 
 * Copyright (C) 2004 Benoit Mortier       <benoit.mortier@opensides.be>
 * Copyright (C) 2004 Sebastien DiCintio   <sdicintio@ressource-toi.org>
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
        \file       htdocs/install/etape4.php
        \brief      Demande le login et mode de passe de l'administrateur Dolibarr
        \version    $Revision$
*/


include_once("./inc.php");

$setuplang=isset($_POST["selectlang"])?$_POST["selectlang"]:(isset($_GET["selectlang"])?$_GET["selectlang"]:$langcode);
$langs->defaultlang=$setuplang;
$langs->load("admin");
$langs->load("install");

pHeader($langs->trans("AdminAccountCreation"),"etape5");

if (file_exists($conffile))
{
  include_once($conffile);
}

if($dolibarr_main_db_type == "mysql")
	require ($dolibarr_main_document_root . "/lib/mysql.lib.php");		
else
	require ($dolibarr_main_document_root . "/lib/pgsql.lib.php");
			


require ($dolibarr_main_document_root . "/conf/conf.class.php");

print '<table cellspacing="0" cellpadding="4" border="1" width="100%">';

$err=0;

$conf = new Conf();
$conf->db->type = $dolibarr_main_db_type;
$conf->db->host = $dolibarr_main_db_host;
$conf->db->name = $dolibarr_main_db_name;
$conf->db->user = $dolibarr_main_db_user;
$conf->db->pass = $dolibarr_main_db_pass;

$db = new DoliDb($conf->db->type,$conf->db->host,$conf->db->user,$conf->db->pass,$conf->db->name);
$ok = 0;
if ($db->ok == 1)
{
  
  print '<tr><td>'.$langs->trans("DolibarrAdminLogin").' :</td><td>';
  print '<input name="login"></td></tr>';
  print '<tr><td>'.$langs->trans("Password").' :</td><td>';
  print '<input type="password" name="pass"></td></tr>';
  print '<tr><td>'.$langs->trans("PasswordAgain").' :</td><td>';
  print '<input type="password" name="pass_verif"></td></tr>';
  print '</table>';

  if (isset($_GET["error"]) && $_GET["error"] == 1)
    {
      print '<div class="error">'.$langs->trans("PasswordsMismatch").'</div>';
    }

  if (isset($_GET["error"]) && $_GET["error"] == 2)
    {
      print '<div class="error">';
      print $langs->trans("PleaseTypePassword");
      print '</div>';
    }

  if (isset($_GET["error"]) && $_GET["error"] == 3)
    {
      print '<div class="error">'.$langs->trans("PleaseTypeALogin").'</div>';
    }

}

$db->close();

pFooter($err);

?>
