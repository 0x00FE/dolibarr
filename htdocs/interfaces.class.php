<?php
/* Copyright (C) 2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
   \file       htdocs/interfaces.class.php
   \ingroup    core
   \brief      Fichier de la classe de gestion des triggers
*/


/**
   \class      Interfaces
   \brief      Classe de la gestion des triggers
*/

class Interfaces
{
	var $dir;				// Directory with all trigger files
	var $errors=array();	// Array for errors
	
	/**
	*   \brief      Constructeur.
	*   \param      DB      handler d'acc�s base
	*/
	function Interfaces($DB)
	{
		$this->db = $DB ;
		$this->dir = DOL_DOCUMENT_ROOT . "/includes/triggers";
	}
	
	/**
	*   \brief      Fonction appel�e lors du d�clenchement d'un �v�nement Dolibarr.
	*               Cette fonction d�clenche tous les triggers trouv�s actifs.
	*   \param      action      Code de l'evenement
	*   \param      object      Objet concern
	*   \param      user        Objet user
	*   \param      lang        Objet lang
	*   \param      conf        Objet conf
	*   \return     int         Nb triggers d�clench�s si pas d'erreurs, -Nb en erreur sinon.
	*/
	function run_triggers($action,$object,$user,$lang,$conf)
	{
	
		$handle=opendir($this->dir);
		$modules = array();
		$nbtotal = $nbok = $nbko = 0;
	
		while (($file = readdir($handle))!==false)
		{
			if (is_readable($this->dir."/".$file) && eregi('interface_(.*).class.php$',$file,$reg))
			{
				$modName = "Interface".ucfirst($reg[1]);
				//print "file=$file"; print "modName=$modName"; exit;
				if ($modName)
				{
					if (in_array($modName,$modules))
					{
						dolibarr_syslog("Error: Trigger file with name '$modName' already launched. Remove duplicate file.");
					}
					else
					{
						include_once($this->dir."/".$file);
						$objMod = new $modName($this->db);
						if ($objMod)
						{
				            $modules[$i] = $modName;
							$result=$objMod->run_trigger($action,$object,$user,$lang,$conf);
							if ($result > 0)
							{
								// Action OK
								$nbtotal++;
								$nbok++;
							}
							if ($result == 0)
							{
								// Aucune action faite
								$nbtotal++;
							}
							if ($result < 0)
							{
								// Action KO
								$nbtotal++;
								$nbko++;
								$this->errors[]=$objMod->error;
							}
							$i++;
						}
					}
				}
			}
		}
		if ($nbko)
		{
			dolibarr_syslog("Interfaces::run_triggers Found: ".$nbtotal.", Done: ".$nbok.", Failed: ".$nbko);
			return -$nbko;
		}
		else
		{
			return $nbok;
		}
	} 
}
?>
