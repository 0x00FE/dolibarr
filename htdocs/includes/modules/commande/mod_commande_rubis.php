<?php
/* Copyright (C) 2005       Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2007  Regis Houssin        <regis@dolibarr.fr>
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
    \file       htdocs/includes/modules/commande/mod_commande_rubis.php
    \ingroup    commande
    \brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de commande Rubis
    \version    $Revision$
*/

include_once("modules_commande.php");


/**
   \class      mod_commande_rubis
   \brief      Classe du mod�le de num�rotation de r�f�rence de commande Rubis
*/

class mod_commande_rubis extends ModeleNumRefCommandes
{

  /**   \brief      Constructeur
   */
  function mod_commande_rubis()
  {
    $this->nom = "Rubis";
  }


  /**     \brief      Renvoi la description du modele de num�rotation
   *      \return     string      Texte descripif
   */
  function info()
    {
    	global $conf,$langs;
    	
    	$langs->load("orders");
      
      $texte = $langs->trans('RubisNumRefModelDesc1')."<br>\n";
      $texte.= $langs->trans('RubisNumRefModelDesc2')."<br>\n";
      $texte.= $langs->trans('RubisNumRefModelDesc3')."<br>\n";
      $texte.= $langs->trans('RubisNumRefModelDesc4')."<br>\n";
      
      if ($conf->global->SOCIETE_FISCAL_MONTH_START)
      {
      	$texte.= ' ('.$langs->trans('DefinedAndHasThisValue').' : '.monthArrayOrSelected($conf->global->SOCIETE_FISCAL_MONTH_START).')';
      }
      else
      {
      	$texte.= ' ('.$langs->trans('IsNotDefined').')';
      }
      return $texte;
    }
    
   /**     \brief      Renvoi un exemple de num�rotation
   *      \return     string      Example
   */
   function getExample()
   {
       return "C0600001";
   }

  
  /**     \brief      Renvoi prochaine valeur attribu�e
   *      \return     string      Valeur
   */
    function getNextValue()
    {
        global $db,$conf;
        
        // D'abord on d�fini l'ann�e fiscale
        $prefix='C';
        $current_month = date("n");
        if($conf->global->SOCIETE_FISCAL_MONTH_START > 1 && $current_month >= $conf->global->SOCIETE_FISCAL_MONTH_START)
        {
        	$yy = strftime("%y",dolibarr_mktime(0,0,0,date("m"),date("d"),date("Y")+1));
        }
        else
        {
        	$yy = strftime("%y",time());
        }
        
        // On r�cup�re la valeur max (r�ponse imm�diate car champ ind�x�)
        $cyy='';
        $sql = "SELECT MAX(ref)";
        $sql.= " FROM ".MAIN_DB_PREFIX."commande";
        $resql=$db->query($sql);
        if ($resql)
        {
            $row = $db->fetch_row($resql);
            if ($row) $cyy = substr($row[0],0,3);
        }

        // Si au moins un champ respectant le mod�le a �t� trouv�e
        if (eregi('C[0-9][0-9]',$cyy))
        {
            // Recherche rapide car restreint par un like sur champ index�
            $posindice=4;
            $sql = "SELECT MAX(0+SUBSTRING(ref,$posindice))";
            $sql.= " FROM ".MAIN_DB_PREFIX."commande";
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
        
        $num = sprintf("%05s",$max+1);
        
        return  "C$yy$num";
    }
    
  
    /**     \brief      Renvoie la r�f�rence de commande suivante non utilis�e
     *      \param      objsoc      Objet soci�t�
     *      \return     string      Texte descripif
     */
    function commande_get_num($objsoc=0)
    {
        return $this->getNextValue();
    }
}
?>
