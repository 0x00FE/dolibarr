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
        \file       htdocs/adherents/ldap.php
        \ingroup    ldap
        \brief      Page fiche LDAP adherent
        \version    $Revision$
*/

require("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/lib/member.lib.php");
require_once(DOL_DOCUMENT_ROOT."/lib/ldap.class.php");
require_once(DOL_DOCUMENT_ROOT."/adherents/adherent.class.php");
require_once(DOL_DOCUMENT_ROOT."/adherents/adherent_type.class.php");

$user->getrights('commercial');

$langs->load("companies");
$langs->load("members");
$langs->load("ldap");

// Protection quand utilisateur externe
$rowid = isset($_GET["id"])?$_GET["id"]:'';

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


$adh = new Adherent($db);
$adh->id = $rowid;
$result=$adh->fetch($rowid);
if (! $result)
{
	dolibarr_print_error($db,"Failed to get adherent: ".$adh->error);
	exit;
}
$adh->fetch_optionals($rowid);

$adht = new AdherentType($db);
$result=$adht->fetch($adh->typeid);
if (! $result)
{
	dolibarr_print_error($db,"Failed to get type of adherent: ".$adht->error);
	exit;
}



/*
 * Affichage onglets
 */
$head = member_prepare_head($adh);

dolibarr_fiche_head($head, 'ldap', $langs->trans("Member"));

$result=$adh->load_previous_next_id($adh->next_prev_filter);
if ($result < 0) dolibarr_print_error($db,$adh->error);
$previous_id = $adh->id_previous?'<a href="'.$_SERVER["PHP_SELF"].'?id='.urlencode($adh->id_previous).'">'.img_previous().'</a>':'';
$next_id     = $adh->id_next?'<a href="'.$_SERVER["PHP_SELF"].'?id='.urlencode($adh->id_next).'">'.img_next().'</a>':'';


/*
 * Fiche en mode visu
 */
print '<table class="border" width="100%">';

// Ref
print '<tr><td width="20%">'.$langs->trans("Ref").'</td>';
print '<td class="valeur">';
if ($previous_id || $next_id) print '<table class="nobordernopadding" width="100%"><tr class="nobordernopadding"><td class="nobordernopadding">';
print $adh->id;
if ($previous_id || $next_id) print '</td><td class="nobordernopadding" align="center" width="20">'.$previous_id.'</td><td class="nobordernopadding" align="center" width="20">'.$next_id.'</td></tr></table>';
print '</td></tr>';

// Nom
print '<tr><td>'.$langs->trans("Lastname").'</td><td class="valeur">'.$adh->nom.'&nbsp;</td>';
print '</tr>';

// Prenom
print '<tr><td width="15%">'.$langs->trans("Firstname").'</td><td class="valeur">'.$adh->prenom.'&nbsp;</td>';
print '</tr>';

// Login
print '<tr><td>'.$langs->trans("Login").'</td><td class="valeur">'.$adh->login.'&nbsp;</td></tr>';

// Type
print '<tr><td>'.$langs->trans("Type").'</td><td class="valeur">'.$adh->type."</td></tr>\n";

$langs->load("admin");

// LDAP DN
print '<tr><td>LDAP '.$langs->trans("LDAPMemberDn").'</td><td class="valeur">'.$conf->global->LDAP_MEMBER_DN."</td></tr>\n";

// LDAP Cl�
print '<tr><td>LDAP '.$langs->trans("LDAPNamingAttribute").'</td><td class="valeur">'.$conf->global->LDAP_KEY_MEMBERS."</td></tr>\n";

// LDAP Server
print '<tr><td>LDAP '.$langs->trans("Type").'</td><td class="valeur">'.$conf->global->LDAP_SERVER_TYPE."</td></tr>\n";
print '<tr><td>LDAP '.$langs->trans("Version").'</td><td class="valeur">'.$conf->global->LDAP_SERVER_PROTOCOLVERSION."</td></tr>\n";
print '<tr><td>LDAP '.$langs->trans("LDAPPrimaryServer").'</td><td class="valeur">'.$conf->global->LDAP_SERVER_HOST."</td></tr>\n";
print '<tr><td>LDAP '.$langs->trans("LDAPSecondaryServer").'</td><td class="valeur">'.$conf->global->LDAP_SERVER_HOST_SLAVE."</td></tr>\n";
print '<tr><td>LDAP '.$langs->trans("LDAPServerPort").'</td><td class="valeur">'.$conf->global->LDAP_SERVER_PORT."</td></tr>\n";

print '</table>';

print '</div>';

print '<br>';


print_titre($langs->trans("LDAPInformationsForThisMember"));

// Affichage attributs LDAP
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
	$info=$adh->_load_ldap_info();
	$dn=$adh->_load_ldap_dn($info,1);
	$search = "(".$adh->_load_ldap_dn($info,2).")";
	$records=$ldap->search($dn,$search);

	// Affichage arbre
	if (sizeof($records))
	{
		if (! is_array($records))
		{
			print '<tr '.$bc[false].'><td colspan="2"><font class="error">'.$langs->trans("ErrorFailedToReadLDAP").'</font></td></tr>';	
		}
		else
		{
			$html=new Form($db);
			$result=$html->show_ldap_content($records,0,0,true);
		}
	}
	else
	{
		print '<tr '.$bc[false].'><td colspan="2">'.$langs->trans("LDAPRecordNotFound").' (dn='.$dn.' - search='.$search.')</td></tr>';
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
