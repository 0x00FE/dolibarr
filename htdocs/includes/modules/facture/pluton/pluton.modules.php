<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2007 Regis Houssin        <regis.houssin@cap-networks.com>
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
 *
 */

/**
	\file       htdocs/includes/modules/facture/pluton/pluton.modules.php
	\ingroup    facture
	\brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de facture Pluton
	\version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT ."/includes/modules/facture/modules_facture.php");

/**
	\class      mod_facture_pluton
	\brief      Classe du mod�le de num�rotation de r�f�rence de facture Pluton
*/
class mod_facture_pluton extends ModeleNumRefFactures
{

    /**     \brief      Renvoi la description du modele de num�rotation
     *      \return     string      Texte descripif
     */
function info()
    {
    	global $conf,$langs;

		$langs->load("bills");
    	
      $texte = $langs->trans('PlutonNumRefModelDesc1')."<br>\n";
      
      $texte.= 'D�but ann�e fiscale';
      if ($conf->global->SOCIETE_FISCAL_MONTH_START)
      {
      	$texte.= ' ('.$langs->trans('DefinedAndHasThisValue').' : '.$conf->global->SOCIETE_FISCAL_MONTH_START.')<br>';
      }
      else
      {
      	$texte.= ' ('.$langs->trans('IsNotDefined').')<br>';
      }
      
      $texte.= 'Pr�fix de la facture';
      if ($conf->global->FACTURE_NUM_PREFIX)
      {
      	$texte.= ' ('.$langs->trans('DefinedAndHasThisValue').' : '.$conf->global->FACTURE_NUM_PREFIX.')<br>';
      }
      else
      {
      	$texte.= ' ('.$langs->trans('IsNotDefined').')<br>';
      }
      
      $texte.= 'Nombre de chiffres sur le compteur';
      if ($conf->global->FACTURE_NUM_QUANTIFY_METER)
      {
      	$texte.= ' ('.$langs->trans('DefinedAndHasThisValue').' : '.$conf->global->FACTURE_NUM_QUANTIFY_METER.')<br>';
      }
      else
      {
      	$texte.= ' ('.$langs->trans('IsNotDefined').')<br>';
      }
      
      $texte.= 'Le compteur se remet � z�ro en d�but d\'ann�e';
      if ($conf->global->FACTURE_NUM_RESTART_BEGIN_YEAR)
      {
      	$texte.= ' ('.$langs->trans('DefinedAndHasThisValue').' : '.$conf->global->FACTURE_NUM_RESTART_BEGIN_YEAR.')<br>';
      }
      else
      {
      	$texte.= ' ('.$langs->trans('IsNotDefined').')<br>';
      }
      
      return $texte;
    }

    /**     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
    	global $conf;
    	
    	$prefix = $conf->global->FACTURE_NUM_PREFIX;
    	$numExample = $prefix."0600001";
    	return $numExample;           
    }

	/**		\brief      Renvoi prochaine valeur attribu�e
	*      	\param      objsoc      Objet soci�t�
	*      	\param      facture		Objet facture
	*      	\return     string      Valeur
	*/
    function getNextValue($objsoc,$facture)
    {
        global $db,$conf;
        
        // On d�fini l'ann�e fiscale
        $prefix = $conf->global->FACTURE_NUM_PREFIX;
        $current_month = date("n");
        
        if (is_object($facture) && $facture->date)
        {
        	$create_month = strftime("%m",$facture->date);
        }
        else
        {
        	$create_month = $current_month;
        }
        
        if($conf->global->SOCIETE_FISCAL_MONTH_START && $current_month >= $conf->global->SOCIETE_FISCAL_MONTH_START && $create_month >= $conf->global->SOCIETE_FISCAL_MONTH_START)
        {
        	$yy = strftime("%y",mktime(0,0,0,date("m"),date("d"),date("Y")+1));
        }
        else
        {
        	$yy = strftime("%y",time());
        }
        
        // On r�cup�re la valeur max (r�ponse imm�diate car champ ind�x�)
        $numFigure = ($conf->global->FACTURE_NUM_QUANTIFY_METER - 1);
        $fisc=$prefix.$yy;
        $fayy='';
        $sql = "SELECT MAX(facnumber)";
        $sql.= " FROM ".MAIN_DB_PREFIX."facture";
        if ($conf->global->FACTURE_NUM_RESTART_BEGIN_YEAR == 1) $sql.= " WHERE facnumber like '${fisc}%'";
        $resql=$db->query($sql);
        if ($resql)
        {
            $row = $db->fetch_row($resql);
            if ($row) $fayy = substr($row[0],0,$numFigure);
        }

        // Si au moins un champ respectant le mod�le a �t� trouv�e
        if (eregi('FA[0-9][0-9]',$fayy))
        {
            // Recherche rapide car restreint par un like sur champ index�
            $date = strftime("%Y%m", time());
            $posindice = $conf->global->FACTURE_NUM_QUANTIFY_METER;
            $sql = "SELECT MAX(0+SUBSTRING(facnumber,$posindice))";
            $sql.= " FROM ".MAIN_DB_PREFIX."facture";
            $sql.= " WHERE facnumber like '${fayy}%'";
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
        
        $arg = '%0'.$conf->global->FACTURE_NUM_QUANTIFY_METER.'s';
        $num = sprintf($arg,$max+1);
        
        return  "$prefix$yy$num";
    }
    
  
    /**     \brief      Renvoie la r�f�rence de commande suivante non utilis�e
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
