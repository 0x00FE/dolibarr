<?PHP
/* Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       scripts/adherents/sync_member_dolibarr2ldap.php
        \ingroup    ldap adherent
        \brief      Script de mise a jour des adherents dans LDAP depuis base Dolibarr
*/

// Test si mode batch
$sapi_type = php_sapi_name();
$script_file=__FILE__; 
if (eregi('([^\\\/]+)$',$script_file,$reg)) $script_file=$reg[1];

if (substr($sapi_type, 0, 3) == 'cgi') {
    echo "Erreur: Vous utilisez l'interpreteur PHP pour le mode CGI. Pour executer $script_file en ligne de commande, vous devez utiliser l'interpreteur PHP pour le mode CLI.\n";
    exit;
}


// Main
$version='$Revision$';
$path=eregi_replace($script_file,'',$_SERVER["PHP_SELF"]);
@set_time_limit(0);
$error=0;

require_once($path."../../htdocs/master.inc.php");
require_once(DOL_DOCUMENT_ROOT."/lib/ldap.class.php");
require_once(DOL_DOCUMENT_ROOT."/adherents/adherent.class.php");

$langs->load("main");


print "***** $script_file ($version) *****\n";

if (! isset($argv[1]) || ! $argv[1]) {
    print "Usage: $script_file now\n";   
    exit;
}
$now=$argv[1];

print "\n";
print "----- Synchronize all records from Dolibarr database:\n";
print "host=".$conf->db->host."\n";
print "port=".$conf->db->port."\n";
print "login=".$conf->db->user."\n";
print "pass=".eregi_replace('.','*',$conf->db->pass)."\n";
print "database=".$conf->db->name."\n";
print "\n";
print "----- To LDAP database:\n";
print "host=".$conf->global->LDAP_SERVER_HOST."\n";
print "port=".$conf->global->LDAP_SERVER_PORT."\n";
print "login=".$conf->global->LDAP_ADMIN_DN."\n";
print "pass=".eregi_replace('.','*',$conf->global->LDAP_ADMIN_PASS)."\n";
print "DN target=".$conf->global->LDAP_MEMBER_DN."\n";
print "\n";
print "Press a key to confirm...\n";
$input = trim(fgets(STDIN));

/*
if (! $conf->global->LDAP_MEMBER_ACTIVE)
{
	print $langs->trans("LDAPSynchronizationNotSetupInDolibarr");
	exit 1;	
}
*/

$sql = "SELECT rowid";
$sql .= " FROM ".MAIN_DB_PREFIX."adherent";

$resql = $db->query($sql);
if ($resql)
{
	$num = $db->num_rows($resql);
	$i = 0;

	$ldap=new Ldap();
	$ldap->connect_bind();

	while ($i < $num)
	{
		$ldap->error="";
		
		$obj = $db->fetch_object($resql);

		$member = new Adherent($db);
		$result=$member->fetch($obj->rowid);
		if ($result < 0)
		{
			dolibarr_print_error($db,$member->error);
			exit;
		}
		$result=$member->fetch_subscriptions();
		if ($result < 0)
		{
			dolibarr_print_error($db,$member->error);
			exit;
		}
		
		print $langs->transnoentities("UpdateMember")." rowid=".$member->id." ".$member->fullname;

		$info=$member->_load_ldap_info();
		$dn=$member->_load_ldap_dn($info);
		
		$result=$ldap->update($dn,$info,$user);
		if ($result > 0)
		{
			print " - ".$langs->transnoentities("OK");
		}
		else
		{
			$error++;
			print " - ".$langs->transnoentities("KO").' - '.$ldap->error;
		}
		print "\n";

		$i++;
	}
	
	$ldap->unbind();
	$ldap->close();
}
else
{
	dolibarr_print_error($db);
}

return $error;
?>
