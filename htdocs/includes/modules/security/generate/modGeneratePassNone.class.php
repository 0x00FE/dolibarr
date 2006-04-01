<?php
/* Copyright (C) 2006 Laurent Destailleur <eldy@users.sourceforge.net>
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
        \file       htdocs/includes/modules/security/generate/modGeneratePassNone.class.php
        \ingroup    core
        \brief      Fichier de gestion de la generation de mot de passe selon r�gle standard
*/

class modGeneratePassNone
{
	var $id;
	var $length;

	var $db;
	var $conf;
	var $lang;
	var $user;

	
	/**
	 *		\brief      Constructeur
	 *		\param		db			Handler d'acc�s base
	 *		\param		conf		Handler de conf
	 *		\param		lang		Handler de langue
	 *		\param		user		Handler du user connect�
	 */
	function modGeneratePassNone($db, $conf, $langs, $user)
	{
		$this->id = "none";
		$this->length = 0;

		$this->db=$db;
		$this->conf=$conf;
		$this->langs=$langs;
		$this->user=$user;
	}

	/**
	 *		\brief      Renvoi la description du module
 	 *      \return     string      Texte descripif
	 */
	function getDescription()
	{
		return "Ne propose pas de mots de passe g�n�r�s. Le mot de passe est � saisir manuellement.";
	}

	/**
	 * 		\brief		Renvoie exemple de mot de passe g�n�r� par cette r�gle
 	 *      \return     string      Exemple
	 */
	function getExample()
	{
		return $this->langs->trans("None");
	}

	/**
	 * 		\brief		G�n�re le mot de passe
 	 *      \return     string      Renvoi mot de passe g�n�r�
	 */
	function getNewGeneratedPassword()
	{
		return "";
	}

}

?>
