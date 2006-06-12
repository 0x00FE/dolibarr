<?php
/* Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004 Benoit Mortier       <benoit.mortier@opensides.be>
 * Copyright (C) 2005 Regis Houssin        <regis.houssin@cap-networks.com>
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
 * or see http://www.gnu.org/
 *
 * $Id$
 * $Source$
 */

/**
		\file 		htdocs/lib/ldap.lib.php
		\brief 		Librairie contenant les fonctions pour acc�der au serveur ldap.
		\author 	Rodolphe Quiedeville.
		\author		Benoit Mortier.
		\version 	$Revision$

		Ensemble des fonctions permettant d'acc�der � un serveur LDAP.
*/

class Ldap
{
	var $err; // erreur ldap
	
	
	/**
	*    \brief  Constructeur de la classe
	*/
	function Ldap()
	{
		$this->err = "";
	}


/**
		\brief 		Ouverture d'une connection vers le serveur LDAP
		\return		resource
*/
function dolibarr_ldap_connect()
{
	global $conf;
	
	if (ereg('^ldap',$conf->global->LDAP_SERVER_HOST))
	{
		// ex url:		ldaps://ldap.example.com/
		$ldapconnect = ldap_connect($conf->global->LDAP_SERVER_HOST);
	}
	else
	{
		// ex serveur: 	localhost
		// ex port: 	389
		$ldapconnect = ldap_connect($conf->global->LDAP_SERVER_HOST,$conf->global->LDAP_SERVER_PORT);
	}
	
	if ($ldapconnect)
	{
		ldap_set_option($ldapconnect, LDAP_OPT_PROTOCOL_VERSION, $conf->global->LDAP_SERVER_PROTOCOLVERSION);
		return $ldapconnect;
	}
	else
	{
		$this->err = ldap_error($ldapconnect);
	}
}


/**
		\brief 	Se connecte au serveur LDAP avec user et mot de passe
		\param	ds
		\return	bool
*/
function dolibarr_ldap_bind($ds)
{
	global $conf;
	
	if (defined("LDAP_ADMIN_PASS") && $conf->global->LDAP_ADMIN_DN && $conf->global->LDAP_ADMIN_PASS)
    {
    	$ldapbind = ldap_bind($ds, $conf->global->LDAP_ADMIN_DN, $conf->global->LDAP_ADMIN_PASS);
    }
  
  if ($ldapbind)
  {
  	return $ldapbind;
  }
  else
  {
  	$this->err = ldap_error($ds);
  }
}

/**
		\brief unbind du serveur ldap.
		\param	ds
		\return	bool
*/
function dolibarr_ldap_unbind($ds)
{
	$ldapunbind = ldap_unbind($ds);

	return $ldapunbind;
}

/**
		\brief verification de la version du serveur ldap.
		\param	ds
		\return	version
*/
function dolibarr_ldap_getversion($ds)
{
	global $conf;
	
	$version = 0;
	
	ldap_get_option($ds, LDAP_OPT_PROTOCOL_VERSION, $version);
	
	return $version;
}

/**
		\brief changement de la version du serveur ldap.
		\param	ds
		\param	version
		\return	version
*/
function dolibarr_ldap_setversion($ds,$version)
{
	global $conf;
	
	$ldapsetversion = ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $version);
  
	return $ldapsetversion;
}

/**
		\brief permet d'enlever les accents d'une chaine.
		\param	str
		\return	string
*/
function dolibarr_ldap_unacc($str)
{
	$stu = ereg_replace("�","e",$str);
	$stu = ereg_replace("�","e",$stu);
	$stu = ereg_replace("�","e",$stu);
	$stu = ereg_replace("�","a",$stu);
	$stu = ereg_replace("�","c",$stu);
	$stu = ereg_replace("�","i",$stu);
	$stu = ereg_replace("�","a",$stu);
	return $stu;
}
}

?>
