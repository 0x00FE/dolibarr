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
        \file       htdocs/user/ldap.php
        \ingroup    ldap
        \brief      Page fiche LDAP utilisateur
        \version    $Revision$
*/

require("./pre.inc.php");
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

$fuser = new User($db, $_GET["id"]);
$fuser->fetch();
$fuser->getrights();


/*
* Actions
*/

if ($_GET["action"] == 'dolibarr2ldap')
{
	$message="";

	$db->begin();

	$ldap=new Ldap();
	$ldap->connect_bind();

	$info=$fuser->_load_ldap_info();
	$dn=$fuser->_load_ldap_dn($info);
	
    $ret=$ldap->update($dn,$info,$user);	// Marche en creation LDAP et mise a jour

	if ($ret >= 0)
	{
		$message.='<div class="ok">'.$langs->trans("UserSynchronized").'</div>';
		$db->commit();
	}
	else
	{
		$message.='<div class="error">'.$ldap->error.'</div>';
		$db->rollback();
	}
}


/*
 *	Affichage page
 */

llxHeader();

$form = new Form($db);


/*
 * Affichage onglets
 */
$head = user_prepare_head($fuser);

dolibarr_fiche_head($head, 'ldap', $langs->trans("User").": ".$fuser->fullname);



/*
 * Fiche en mode visu
 */
print '<table class="border" width="100%">';

// Ref
print '<tr><td width="25%" valign="top">'.$langs->trans("Ref").'</td>';
print '<td>'.$fuser->id.'</td>';
print '</tr>';

// Nom
print '<tr><td width="25%" valign="top">'.$langs->trans("Lastname").'</td>';
print '<td>'.$fuser->nom.'</td>';
print "</tr>\n";

// Prenom
print '<tr><td width="25%" valign="top">'.$langs->trans("Firstname").'</td>';
print '<td>'.$fuser->prenom.'</td>';
print "</tr>\n";

// Login
print '<tr><td width="25%" valign="top">'.$langs->trans("Login").'</td>';
if ($fuser->ldap_sid)
{
	print '<td class="warning">'.$langs->trans("LoginAccountDisableInDolibarr").'</td>';
}
else
{
	print '<td>'.$fuser->login.'</td>';
}
print '</tr>';

$langs->load("admin");

// LDAP DN
print '<tr><td>LDAP '.$langs->trans("LDAPUserDn").'</td><td class="valeur">'.$conf->global->LDAP_USER_DN."</td></tr>\n";

// LDAP Cl�
print '<tr><td>LDAP '.$langs->trans("LDAPNamingAttribute").'</td><td class="valeur">'.$conf->global->LDAP_KEY_USERS."</td></tr>\n";

// LDAP Server
print '<tr><td>LDAP '.$langs->trans("LDAPPrimaryServer").'</td><td class="valeur">'.$conf->global->LDAP_SERVER_HOST."</td></tr>\n";
print '<tr><td>LDAP '.$langs->trans("LDAPSecondaryServer").'</td><td class="valeur">'.$conf->global->LDAP_SERVER_HOST_SLAVE."</td></tr>\n";
print '<tr><td>LDAP '.$langs->trans("LDAPServerPort").'</td><td class="valeur">'.$conf->global->LDAP_SERVER_PORT."</td></tr>\n";

print '</table>';

print '</div>';


if ($message) { print $message; }


/*
 * Barre d'actions
 */

print '<div class="tabsAction">';

if ($conf->global->LDAP_SYNCHRO_ACTIVE == 'dolibarr2ldap')
{
	print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$fuser->id.'&amp;action=dolibarr2ldap">'.$langs->trans("ForceSynchronize").'</a>';
}

print "</div>\n";
print "<br>\n";



// Affichage attributs LDAP
print_titre($langs->trans("LDAPInformationsForThisUser"));

print '<table width="100%" class="noborder">';

print '<tr class="liste_titre">';
print '<td>'.$langs->trans("LDAPAttributes").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '</tr>';

// Lecture LDAP
$ldap=new Ldap();
$result=$ldap->connect_bind();
if ($result > 0)
{
	$info=$fuser->_load_ldap_info();
	$dn=$fuser->_load_ldap_dn($info,1);
	$search = "(".$fuser->_load_ldap_dn($info,2).")";
	$result=$ldap->search($dn,$search);
	if ($result < 0)
	{
		dolibarr_print_error($db,$ldap->error);
	}
	
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
