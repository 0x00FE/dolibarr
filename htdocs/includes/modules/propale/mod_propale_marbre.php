<?php
/* Copyright (C) 2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005 Regis Houssin        <regis.houssin@cap-networks.com>
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
     	\file       htdocs/includes/modules/propale/mod_propale_marbre.php
		\ingroup    propale
		\brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de propale Marbre
		\version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT ."/includes/modules/propale/modules_propale.php");

/**	    \class      mod_propale_marbre
		\brief      Classe du mod�le de num�rotation de r�f�rence de propale Marbre
*/

class mod_propale_marbre extends ModeleNumRefPropales
{
    var $error='';
    
    /**   \brief      Constructeur
     */
    function mod_propale_marbre()
    {
      $this->nom = "Marbre";
    }
    
    /**     \brief      Renvoi la description du modele de num�rotation
     *      \return     string      Texte descripif
     */
    function info()
    {
      return "Renvoie le num�ro sous la forme PRyymm-nnnn o� yy est l'ann�e, mm le mois et nnnn un compteur s�quentiel sans rupture et sans remise � 0";
    }


    /**     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
        return "PR0501-0001";
    }


    /**     \brief      Test si les num�ros d�j� en vigueur dans la base ne provoquent pas de
     *                  de conflits qui empechera cette num�rotation de fonctionner.
     *      \return     boolean     false si conflit, true si ok
     */
    function canBeActivated()
    {
        $pryymm='';
        
        $sql = "SELECT MAX(ref)";
        $sql.= " FROM ".MAIN_DB_PREFIX."propal";
        $resql=$db->query($sql);
        if ($resql)
        {
            $row = $db->fetch_row($resql);
            if ($row) $pryymm = substr($row[0],0,6);
        }
        if (! $pryymm || eregi('PR[0-9][0-9][0-9][0-9]',$pryymm))
        {
            return true;
        }
        else
        {
            $this->error='Une propal commen�ant par $pryymm existe en base et est incompatible avec cette num�rotation. Supprimer la ou renommer la pour activer ce module.';
            return false;    
        }
    }

    /**     \brief      Renvoi prochaine valeur attribu�e
     *      \param      objsoc      Objet soci�t�
     *      \return     string      Valeur
     */
    function getNextValue($objsoc=0)
    {
        global $db;

        // D'abord on r�cup�re la valeur max (r�ponse imm�diate car champ ind�x�)
        $pryymm='';
        $sql = "SELECT MAX(ref)";
        $sql.= " FROM ".MAIN_DB_PREFIX."propal";
        $resql=$db->query($sql);
        if ($resql)
        {
            $row = $db->fetch_row($resql);
            if ($row) $pryymm = substr($row[0],0,6);
        }
    
        // Si au moins un champ respectant le mod�le a �t� trouv�e
        if (eregi('PR[0-9][0-9][0-9][0-9]',$pryymm))
        {
            // Recherche rapide car restreint par un like sur champ index�
            $posindice=8;
            $sql = "SELECT MAX(0+SUBSTRING(ref,$posindice))";
            $sql.= " FROM ".MAIN_DB_PREFIX."propal";
            $sql.= " WHERE ref like '${pryymm}%'";
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
        
        return  "PR$yymm-$num";
    }

}

?>
