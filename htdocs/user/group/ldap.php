<?php
/* Copyright (C) 2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2006 Regis Houssin        <regis.houssin@cap-networks.com>
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

/**
        \file       htdocs/user/group/ldap.php
        \ingroup    ldap
        \brief      Page fiche LDAP groupe
        \version    $Revision$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/contact.class.php");
require_once(DOL_DOCUMENT_ROOT."/lib/usergroups.lib.php");
require_once(DOL_DOCUMENT_ROOT."/lib/ldap.class.php");

$user->getrights('commercial');

$langs->load("companies");
$langs->load("ldap");

// Protection quand utilisateur externe
$contactid = isset($_GET["id"])?$_GET["id"]:'';

$socid=0;
if ($user->societe_id > 0)
{
    $socid = $user->societe_id;
}



/*
 *	Affichage page
 */

llxHeader();

$form = new Form($db);

$fgroup = new Usergroup($db, $_GET["id"]);
$fgroup->fetch($_GET["id"]);
$fgroup->getrights();


/*
 * Affichage onglets
 */
$head = group_prepare_head($fgroup);

dolibarr_fiche_head($head, 'ldap', $langs->trans("Group").": ".$fgroup->nom);



/*
 * Fiche en mode visu
 */
print '<table class="border" width="100%">';

// Nom
print '<tr><td width="25%" valign="top">'.$langs->trans("Name").'</td>';
print '<td width="75%" class="valeur">'.$fgroup->nom.'</td>';
print "</tr>\n";

// Note
print '<tr><td width="25%" valign="top">'.$langs->trans("Note").'</td>';
print '<td class="valeur">'.nl2br($fgroup->note).'&nbsp;</td>';
print "</tr>\n";

$langs->load("admin");

// LDAP DN
print '<tr><td>LDAP '.$langs->trans("LDAPGroupDn").'</td><td class="valeur">'.$conf->global->LDAP_GROUP_DN."</td></tr>\n";

// LDAP Cl�
print '<tr><td>LDAP '.$langs->trans("LDAPNamingAttribute").'</td><td class="valeur">'.$conf->global->LDAP_KEY_GROUPS."</td></tr>\n";

// LDAP Server
print '<tr><td>LDAP '.$langs->trans("LDAPPrimaryServer").'</td><td class="valeur">'.$conf->global->LDAP_SERVER_HOST."</td></tr>\n";
print '<tr><td>LDAP '.$langs->trans("LDAPSecondaryServer").'</td><td class="valeur">'.$conf->global->LDAP_SERVER_HOST_SLAVE."</td></tr>\n";
print '<tr><td>LDAP '.$langs->trans("LDAPServerPort").'</td><td class="valeur">'.$conf->global->LDAP_SERVER_PORT."</td></tr>\n";

print "</table>\n";

print '</div>';

print '<br>';


print_titre($langs->trans("LDAPInformationsForThisGroup"));

// Affichage attributs LDAP
print '<table width="100%" class="noborder">';

print '<tr class="liste_titre">';
print '<td>'.$langs->trans("LDAPAttributes").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '</tr>';

// Lecture LDAP
$ldap=new Ldap();
$result=$ldap->connect();
if ($result)
{
	$bind='';
	if ($conf->global->LDAP_ADMIN_DN && $conf->global->LDAP_ADMIN_PASS)
	{
		dolibarr_syslog("ldap.php: authBind user=".$conf->global->LDAP_ADMIN_DN,LOG_DEBUG);
		$bind=$ldap->authBind($conf->global->LDAP_ADMIN_DN,$conf->global->LDAP_ADMIN_PASS);
	}
	if (! $bind)	// Si pas de login ou si connexion avec login en echec, on tente en anonyme
	{
		dolibarr_syslog("ldap.php: bind",LOG_DEBUG);
		$bind=$ldap->bind();
	}

	if ($bind)
	{
		$info["cn"] = trim($fgroup->nom);

		$dn = $conf->global->LDAP_GROUP_DN;
//		$dn = "cn=".$info["cn"].",".$dn;
//		$dn = "uid=".$info["uid"].",".$dn
		$search = "(cn=".$info["cn"].")";
		//$search = "(uid=".$info["uid"].")";

		$result=$ldap->search($dn,$search);

		// Affichage arbre
		if (sizeof($result))
		{
			$html=new Form($db);
			$html->show_ldap_content($result,0,0,true);
		}
		else
		{
			print '<tr><td colspan="2">'.$langs->trans("LDAPRecordNotFound").'</td></tr>';
		}

		$ldap->unbind();
	}
	else
	{
		dolibarr_print_error('',$ldap->error);
	}
	$ldap->close();
}
else
{
	dolibarr_print_error('',$ldap->error);
}

print '</table>';




$db->close();

llxFooter('$Date$ - $Revision$');
?>
