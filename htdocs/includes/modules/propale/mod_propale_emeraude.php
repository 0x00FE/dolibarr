<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2006 Regis Houssin        <regis.houssin@cap-networks.com>
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
     	\file       htdocs/includes/modules/propale/mod_propale_emeraude.php
		\ingroup    propale
		\brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de propale Emeraude
		\version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT ."/includes/modules/propale/modules_propale.php");

/**	    \class      mod_propale_emeraude
		\brief      Classe du mod�le de num�rotation de r�f�rence de propale Emeraude
*/

class mod_propale_emeraude extends ModeleNumRefPropales
{

  /**   \brief      Constructeur
   */
  function mod_commande_ivoire()
    {
      $this->nom = "Emeraude";
    }
    
    /**     \brief      Renvoi la description du modele de num�rotation
     *      \return     string      Texte descripif
     */
    function info()
    {
    	global $conf;
    	
      $texte = "Renvoie le num�ro sous la forme PRYYNNNNN o� YY est l'ann�e et NNNNN le num�ro d'incr�ment qui commence � 1.<br>\n";
      $texte.= "L'ann�e s'incr�mente de 1 et le num�ro d'incr�ment se remet � zero en d�but d'ann�e d'exercice.<br>\n";
      $texte.= "D�finir la variable SOCIETE_FISCAL_MONTH_START avec le mois du d�but d'exercice, ex: 9 pour septembre.<br>\n";
      $texte.= "Dans cette exemple nous aurons au 1er septembre 2006 une propale nomm�e PR0700001.<br>\n";
      
      if ($conf->global->SOCIETE_FISCAL_MONTH_START)
      {
      	$texte.= "SOCIETE_FISCAL_MONTH_START est d�finie et vaut: ".$conf->global->SOCIETE_FISCAL_MONTH_START."";
      }
      else
      {
      	$texte.= "SOCIETE_FISCAL_MONTH_START n'est pas d�finie.";
      }
      return $texte;
    }


    /**     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
        return "PR0500001";
    }


  /**     \brief      Renvoi prochaine valeur attribu�e
   *      \return     string      Valeur
   */
    function getNextValue($objsoc=0)
    {
        global $db,$conf;
        
        // D'abord on d�fini l'ann�e fiscale
        $prefix='PR';
        $current_month = date("n");
        if($conf->global->SOCIETE_FISCAL_MONTH_START && $current_month >= $conf->global->SOCIETE_FISCAL_MONTH_START)
        {
        	$yy = strftime("%y",mktime(0,0,0,date("m"),date("d"),date("Y")+1));
        }
        else
        {
        	$yy = strftime("%y",time());
        }
        
        // On r�cup�re la valeur max (r�ponse imm�diate car champ ind�x�)
        $fisc=$prefix.$yy;
        $pryy='';
        $sql = "SELECT MAX(ref)";
        $sql.= " FROM ".MAIN_DB_PREFIX."propal";
        $sql.= " WHERE ref like '${fisc}%'";
        $resql=$db->query($sql);
        if ($resql)
        {
            $row = $db->fetch_row($resql);
            if ($row) $pryy = substr($row[0],0,4);
        }

        // Si au moins un champ respectant le mod�le a �t� trouv�e
        if (eregi('PR[0-9][0-9]',$pryy))
        {
            // Recherche rapide car restreint par un like sur champ index�
            $date = strftime("%Y%m", time());
            $posindice=5;
            $sql = "SELECT MAX(0+SUBSTRING(ref,$posindice))";
            $sql.= " FROM ".MAIN_DB_PREFIX."propal";
            $sql.= " WHERE ref like '${pryy}%'";
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
        
        return  "PR$yy$num";
    }
    
}

?>
