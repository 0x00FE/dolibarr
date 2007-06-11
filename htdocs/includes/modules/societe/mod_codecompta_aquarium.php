<?php
/* Copyright (C) 2004-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005 Eric Seigne          <eric.seigne@ryxeo.com>
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
   \file       htdocs/includes/modules/societe/mod_codecompta_aquarium.class.php
   \ingroup    societe
   \brief      Fichier de la classe des gestion aquarium des codes compta des societes clientes
   \version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT."/includes/modules/societe/modules_societe.class.php");

/**
   \class 		mod_codecompta_aquarium
   \brief 		Classe permettant la gestion aquarium des codes compta des societes clients
*/

class mod_codecompta_aquarium extends ModeleAccountancyCode
{
  var $nom;
  
  function mod_codecompta_aquarium()
  {
    $this->nom = "Aquarium";
  }
  
  function info($langs)
  {
    return $langs->trans("ModuleCompanyCode".$this->nom);
  }
  
  
  /**
     \brief      Renvoi code compta d'une societe
     \param      DB              Handler d'acc�s base
     \param      societe         Objet societe
     \param      type			Type de tiers ('customer' ou 'supplier')
     \return	int				>=0 ok, <0 ko
   */
  function get_code($DB, $societe, $type)
  {
    $prefixcodecomptacustomer='411';
    $prefixcodecomptasupplier='401';
    
    $i = 0;
    $this->db = $DB;

    dolibarr_syslog("mod_codecompta_aquarium::get_code search code for type=".$type." company=".$societe->nom);
    
    // Regle gestion compte compta
    $codetouse='';
    if ($type == 'customer') $codetouse = $prefixcodecomptacustomer;
    if ($type == 'supplier') $codetouse = $prefixcodecomptasupplier;
    if ($type == 'customer') $codetouse.=$societe->code_client;
    if ($type == 'supplier') $codetouse.=$societe->code_fournisseur;
    $codetouse=eregi_replace('[^0-9]','',$codetouse);
    
    $is_dispo = $this->verif($DB, $codetouse, $societe, $type);
    if (! $is_dispo)
      {	
	/*
	// On tente ajout suffix
	while ($is_dispo == 0 && $i < 37)
	{
	$arr = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$altcodetouse = $codetouse . substr($arr, $i, 1);
	
	$is_dispo = $this->verif($DB, $altcodetouse, $societe, $type);
	
	$i++;
	}
	*/
	// Pour retour
	//			$this->code=$altcodetouse;
	$this->code=$codetouse;
      }
    else
      {
	// Pour retour
	$this->code=$codetouse;
      }
    dolibarr_syslog("mod_codecompta_aquarium::get_code found code=".$this->code);
    return $is_dispo;
  }
  
  
  /**
     \brief		Renvoi si un code compta est dispo
     \return		int			0 non dispo, >0 dispo, <0 erreur
   */
  function verif($db, $code, $societe, $type)
  {
    $sql = "SELECT ";
    if ($type == 'customer') $sql.= "code_compta";
    if ($type == 'supplier') $sql.= "code_compta_fournisseur";
    $sql.= " FROM ".MAIN_DB_PREFIX."societe";
    $sql.= " WHERE ";
    if ($type == 'customer') $sql.= "code_compta";
    if ($type == 'supplier') $sql.= "code_compta_fournisseur";
    $sql.= " = '".$code."'";
    $sql.= " AND rowid != ".$societe->id;
    
    $resql=$db->query($sql);
    if ($resql)
      {
	if ($db->num_rows($resql) == 0)
	  {
	    dolibarr_syslog("mod_codecompta_aquarium::verif code '".$code."' available");
	    return 1;	// Dispo
	  }
	else
	  {
	    dolibarr_syslog("mod_codecompta_aquarium::verif code '".$code."' not available");
	    return 0;	// Non dispo
	  }
      }
    else
      {
	$this->error=$db->error()." sql=".$sql;
	dolibarr_syslog("mod_codecompta_aquarium::verif error".$this->error);
	return -1;		// Erreur
      }
  }
  
}

?>
