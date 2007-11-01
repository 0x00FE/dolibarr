<?php
/* Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Regis Houssin        <regis@dolibarr.fr>
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
        \file       htdocs/includes/modules/facture/terre/terre.modules.php
		\ingroup    facture
		\brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de facture Terre
		\version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT ."/includes/modules/facture/modules_facture.php");

/**	    \class      mod_facture_terre
		\brief      Classe du mod�le de num�rotation de r�f�rence de facture Terre
*/

class mod_facture_terre extends ModeleNumRefFactures
{
	var $version='dolibarr';		// 'development', 'experimental', 'dolibarr'
	var $prefixinvoice='FA';
	var $prefixcreditnote='AV';
	var $error='';
	
    /**     \brief      Renvoi la description du modele de num�rotation
     *      \return     string      Texte descripif
     */
    function info()
    {
	 	global $langs;

		$langs->load("bills");
		
    	return $langs->trans('TerreNumRefModelDesc1',$this->prefixinvoice,$this->prefixcreditnote);
    }

    /**     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
        return $this->prefixinvoice."0501-0001";
    }

    /**     \brief      Test si les num�ros d�j� en vigueur dans la base ne provoquent pas de
     *                  de conflits qui empechera cette num�rotation de fonctionner.
     *      \return     boolean     false si conflit, true si ok
     */
    function canBeActivated()
    {
        global $langs;

		$langs->load("bills");
		  
		// Check invoice num
		$fayymm='';
        
        $sql = "SELECT MAX(facnumber)";
        $sql.= " FROM ".MAIN_DB_PREFIX."facture";
		$sql.= " WHERE facnumber like '".$this->prefixinvoice."%'";
        $resql=$db->query($sql);
        if ($resql)
        {
            $row = $db->fetch_row($resql);
            if ($row) $fayymm = substr($row[0],0,6);
        }
        if ($fayymm && ! eregi($this->prefixinvoice.'[0-9][0-9][0-9][0-9]',$fayymm))
        {
            $this->error=$langs->trans('TerreNumRefModelError');
            return false;    
        }

		// Check credit note num
		$fayymm='';
        
        $sql = "SELECT MAX(facnumber)";
        $sql.= " FROM ".MAIN_DB_PREFIX."facture";
		$sql.= " WHERE facnumber like '".$this->prefixcreditnote."%'";
        $resql=$db->query($sql);
        if ($resql)
        {
            $row = $db->fetch_row($resql);
            if ($row) $fayymm = substr($row[0],0,6);
        }
        if ($fayymm && ! eregi($this->prefixcreditnote.'[0-9][0-9][0-9][0-9]',$fayymm))
        {
            $this->error=$langs->trans('TerreNumRefModelError');
            return false;    
        }

        return true;
    }

    /**     \brief      Renvoi prochaine valeur attribu�e
     *      \param      objsoc		Objet societe
     *      \param      facture		Objet facture
     *      \return     string      Valeur
     */
    function getNextValue($objsoc,$facture)
    {
        global $db;

		if ($facture->type == 2) $prefix=$this->prefixcreditnote;
		else $prefix=$this->prefixinvoice;

        // D'abord on r�cup�re la valeur max (r�ponse imm�diate car champ ind�x�)
        $fayymm='';
        $sql = "SELECT MAX(facnumber)";
        $sql.= " FROM ".MAIN_DB_PREFIX."facture";
		$sql.= " WHERE facnumber like '".$prefix."%'";

        $resql=$db->query($sql);
        if ($resql)
        {
            $row = $db->fetch_row($resql);
            if ($row) $fayymm = substr($row[0],0,6);
        }
        else
        {
        	dolibarr_syslog("mod_facture_terre::getNextValue sql=".$sql);
        	return -1;
        }

        // Si champ respectant le mod�le a �t� trouv�e
        if (eregi('^'.$prefix.'[0-9][0-9][0-9][0-9]',$fayymm))
        {
            // Recherche rapide car restreint par un like sur champ index�
            $posindice=8;
            $sql = "SELECT MAX(0+SUBSTRING(facnumber,$posindice))";
            $sql.= " FROM ".MAIN_DB_PREFIX."facture";
            $sql.= " WHERE facnumber like '${fayymm}%'";
            $resql=$db->query($sql);
            if ($resql)
            {
                $row = $db->fetch_row($resql);
                $max = $row[0];
            }
        }
        else
        {
            $max=0;
        }        
        $yymm = strftime("%y%m",time());
        $num = sprintf("%04s",$max+1);
        
        dolibarr_syslog("mod_facture_terre::getNextValue return ".$prefix."$yymm-$num");
        return $prefix."$yymm-$num";
    }
    
    /**     \brief      Renvoie la r�f�rence de facture suivante non utilis�e
     *      \param      objsoc      Objet soci�t�
     *      \param      facture		Objet facture
     *      \return     string      Texte descripif
     */
    function getNumRef($objsoc=0,$facture)
    { 
        return $this->getNextValue($objsoc,$facture);
    }
    
}

?>
