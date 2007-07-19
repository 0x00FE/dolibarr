<?php
/* Copyright (C) 2003-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2007 Laurent Destailleur  <eldy@users.sourceforge.net>
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
	\file       htdocs/includes/modules/fichinter/mod_arctic.php
	\ingroup    fiche intervention
	\brief      Fichier contenant la classe du mod�le de num�rotation de r�f�rence de fiche intervention Arctic
	\version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT ."/includes/modules/fichinter/modules_fichinter.php");

/**
	\class      mod_commande_saphir
	\brief      Classe du mod�le de num�rotation de r�f�rence de fiche intervention Arctic
*/
class mod_arctic extends ModeleNumRefFicheinter
{
	var $prefix;
	var $matrice;
	var $numMatrice = Array();
	var $yy;
	var $mm;
	var $numbitcounter;
	var $searchLast;
  var $searchLastWithNoYear;
  var $searchLastWithPreviousYear;
	var $error = '';
	
	/**   \brief      Constructeur
   */
  function mod_arctic()
  {
    $this->nom = "arctic";
  }

    /**     \brief      Renvoi la description du modele de num�rotation
     *      \return     string      Texte descripif
     */
function info()
    {
    	global $conf,$langs;

		  $langs->load("bills");
		  
		  $form = new Form($db);
    	
      $texte = $langs->trans('ArcticNumRefModelDesc1')."<br>\n";
      $texte.= '<table class="nobordernopadding" width="100%">';
      
      // Param�trage de la matrice
      $texte.= '<tr><td>Matrice de disposition des objets (prefix,mois,ann�e,compteur...)</td>';
      $texte.= '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
      $texte.= '<input type="hidden" name="action" value="updateMatrice">';
      $texte.= '<td align="right"><input type="text" class="flat" size="30" name="matrice" value="'.$conf->global->FICHEINTER_NUM_MATRICE.'"></td>';
      $texte.= '<td align="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'" name="Button"></td>';
      $texte.= '<td aligne="center">'.$form->textwithhelp('',$langs->trans("MatriceOrderDesc"),1,1).'</td>';
      $texte.= '</tr></form>';
      
      // Param�trage du prefix des commandes
      $texte.= '<tr><td>Pr�fix des fiches d\'interventions</td>';
      $texte.= '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
      $texte.= '<input type="hidden" name="action" value="updatePrefix">';
      $texte.= '<td align="right"><input type="text" class="flat" size="30" name="prefix" value="'.$conf->global->FICHEINTER_NUM_PREFIX.'"></td>';
      $texte.= '<td align="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'" name="Button"></td>';
      $texte.= '<td aligne="center">'.$form->textwithhelp('',$langs->trans("PrefixOrderDesc"),1,1).'</td>';
      $texte.= '</tr></form>';
      
      // On d�termine un offset sur le compteur
      $texte.= '<tr><td>Appliquer un offset sur le compteur</td>';
      $texte.= '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
      $texte.= '<input type="hidden" name="action" value="setOffset">';
      $texte.= '<td align="right"><input type="text" class="flat" size="30" name="offset" value="'.$conf->global->FICHEINTER_NUM_DELTA.'"></td>';
      $texte.= '<td align="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'" name="Button"></td>';
      $texte.= '<td aligne="center">'.$form->textwithhelp('',$langs->trans("OffsetDesc"),1,1).'</td>';
      $texte.= '</tr></form>';
      
      // On d�fini le debut d'ann�e fiscale
      $texte.= '<tr><td>D�but d\'ann�e fiscale</td>';
      $texte.= '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
      $texte.= '<input type="hidden" name="action" value="setFiscalMonth">';
      $texte.= '<td align="right">';
      $texte.= $form->select_month($conf->global->SOCIETE_FISCAL_MONTH_START,'fiscalmonth',1);
      $texte.= '</td><td align="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'" name="Button"></td>';
      $texte.= '<td aligne="center">'.$form->textwithwarning('',$langs->trans("FiscalMonthStartDesc"),1).'</td>';
      $texte.= '</tr></form>';
   
      // On d�fini si le compteur se remet � zero en debut d'ann�e
      $texte.= '<tr><td>Le compteur se remet � z�ro en d�but d\'ann�e</td>';
      $texte.= '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
      $texte.= '<input type="hidden" name="action" value="setNumRestart">';
      $texte.= '<td align="right">';
      $texte.= $form->selectyesno('numrestart',$conf->global->FICHEINTER_NUM_RESTART_BEGIN_YEAR,1);
      $texte.= '</td><td align="left"><input type="submit" class="button" value="'.$langs->trans("Modify").'" name="Button"></td>';
      $texte.= '<td aligne="center">'.$form->textwithhelp('',$langs->trans("NumRestartDesc"),1,1).'</td>';
      $texte.= '</tr></form>';
      
      $texte.= '</table><br>';

      return $texte;
    }

    /**     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getExample()
    {
    	global $conf,$langs;
    	
    	$numExample = '';
      
      //On construit la matrice
      $buildResult = $this->buildMatrice();
        
      if ($buildResult == 1)
      {
      	// On r�cup�re le nombre de chiffres du compteur
    	  $arg = '%0'.$this->numbitcounter.'s';
        $num = sprintf($arg,$conf->global->FICHEINTER_NUM_DELTA?$conf->global->FICHEINTER_NUM_DELTA:1);
      	
      	//On construit le num�ro � partir de la matrice
      	foreach($this->numMatrice as $objetMatrice)
        {
        	if ($objetMatrice == '-') $numExample .= $objetMatrice;
        	if ($objetMatrice == '$prefix') $numExample .= $this->prefix;
        	if ($objetMatrice == '$yy') $numExample .= $this->yy;
        	if ($objetMatrice == '$mm') $numExample .= $this->mm;
        	if ($objetMatrice == '$num') $numExample .= $num;
        }
    	}
      else
      {
      	$numExample = $langs->trans('NotConfigured');
      }
      return $numExample;
    }

 /**		\brief      Renvoi prochaine valeur attribu�e
	*      	\param      objsoc      Objet soci�t�
	*      	\return     string      Valeur
	*/
    function getNextValue($objsoc=0,$ficheinter='')
    {
        global $db,$conf;
        
        //On construit la matrice
        $buildResult = $this->buildMatrice($objsoc,$ficheinter);
        
        if ($buildResult == 1)
        {
        	// On r�cup�re la valeur max (r�ponse imm�diate car champ ind�x�)
          $posindice  = $this->numbitcounter;
          $searchyy='';
          $sql = "SELECT MAX(ref)";
          $sql.= " FROM ".MAIN_DB_PREFIX."fichinter";
          if ($conf->global->FICHEINTER_NUM_RESTART_BEGIN_YEAR) $sql.= " WHERE ref REGEXP '^".$this->searchLast."'";
          $resql=$db->query($sql);
          if ($resql)
          {
            $row = $db->fetch_row($resql);
            if ($row) $searchyy = substr($row[0],0,-$posindice);
          }
          if ($conf->global->FICHEINTER_NUM_DELTA != '')
          {
        	  //on v�rifie si il y a une ann�e pr�c�dente
            //pour �viter que le delta soit appliqu� de nouveau sur la nouvelle ann�e
            $previousyy='';
            $sql = "SELECT MAX(ref)";
            $sql.= " FROM ".MAIN_DB_PREFIX."fichinter";
            $sql.= " WHERE ref REGEXP '^".$this->searchLastWithPreviousYear."'";
            $resql=$db->query($sql);
            if ($resql)
            {
              $row = $db->fetch_row($resql);
              if ($row) $previousyy = substr($row[0],0,-$posindice);
            }
          }

        // Si au moins un champ respectant le mod�le a �t� trouv�e
        if (eregi('^'.$this->searchLastWithNoYear.'',$searchyy))
        {
            // Recherche rapide car restreint par un like sur champ index�
            $sql = "SELECT MAX(0+SUBSTRING(ref,-".$posindice."))";
            $sql.= " FROM ".MAIN_DB_PREFIX."fichinter";
            $sql.= " WHERE ref REGEXP '^".$searchyy."'";
            $resql=$db->query($sql);
            if ($resql)
            {
                $row = $db->fetch_row($resql);
                $max = $row[0];
            }
        }
        else if ($conf->global->FICHEINTER_NUM_DELTA != '' && !eregi('^'.$this->searchLastWithPreviousYear.'',$previousyy))
        {
        	// on applique le delta une seule fois
        	$max=$conf->global->FICHEINTER_NUM_DELTA?$conf->global->FICHEINTER_NUM_DELTA-1:0;
        }
        else
        {
        	$max=0;
        }
    	  
    	  // On applique le nombre de chiffres du compteur
        $arg = '%0'.$this->numbitcounter.'s';
        $num = sprintf($arg,$max+1);
        $numFinal = '';
        
        foreach($this->numMatrice as $objetMatrice)
        {
        	if ($objetMatrice == '-') $numFinal .= $objetMatrice;
        	if ($objetMatrice == '$prefix') $numFinal .= $this->prefix;
        	if ($objetMatrice == '$yy') $numFinal .= $this->yy;
        	if ($objetMatrice == '$mm') $numFinal .= $this->mm;
        	if ($objetMatrice == '$num') $numFinal .= $num;
        } 
        
        dolibarr_syslog("mod_arctic::getNextValue return ".$numFinal);
        return  $numFinal;
    }
  }
    
  
    /**     \brief      Renvoie la r�f�rence de commande suivante non utilis�e
     *      \param      objsoc      Objet soci�t�
     *      \param      commande		Objet commande
     *      \return     string      Texte descripif
     */
    function getNumRef($objsoc=0,$ficheinter='')
    {
        return $this->getNextValue($objsoc,$ficheinter);
    }

  
 /**		\brief      Construction de la matrice de num�rotation
	*     \param      objsoc      Objet soci�t�
	*     \return     string      Valeur
	*/
    function buildMatrice($objsoc=0,$fichinter='')
    {
        global $conf;
        
        $this->prefix  = $conf->global->FICHEINTER_NUM_PREFIX;
        $this->matrice = $conf->global->FICHEINTER_NUM_MATRICE;
        $this->searchLast = '';
        $this->searchLastWithNoYear = '';
        $this->searchLastWithPreviousYear = '';
        
        if ($this->matrice != '')
        {
        	$resultatMatrice = Array();
        	
        	$matricePrefix   = "PREF|COM"; // PREF : prefix libre (ex: FI pour fiche intervention), COM : prefix du client
        	$matriceYear     = "[A]{2,4}"; // l'ann�e est sur 2 ou 4 chiffres
        	$matriceMonth    = "[M]{2}"; // le mois est sur 2 chiffres
        	$matriceCounter  = "[C]{1,}"; //le compteur a un nombre de chiffres libre
        	$matriceTiret    = "[-]{1}"; // on recherche si il y a des tirets de s�paration
        	
        	$matriceSearch   = Array('prefix'=>$matricePrefix,
        	                         'year'=>$matriceYear,
        	                         'month'=>$matriceMonth,
        	                         'counter'=>$matriceCounter
        	                         );
        	
        	// on d�termine l'emplacement des tirets
        	$resultTiret = preg_split('/'.$matriceTiret.'/',$this->matrice, -1, PREG_SPLIT_OFFSET_CAPTURE);
        	
        	$j = 0;
        	$k = 0;
        	
        	// on d�termine les objets de la matrice
        	for ($i = 0; $i < count($resultTiret); $i++)
        	{
        		foreach($resultTiret[$i] as $idResultTiret => $valueResultTiret)
        		{
        			// Ajout des tirets
        		  if ($j != $resultTiret[$i][1])
        		  {
        		  	$this->numMatrice[$k] = '-';
        		  	$this->searchLast .= '-';
        		  	$this->searchLastWithNoYear .= '-';
        		  	$this->searchLastWithPreviousYear .= '-';
        		  	$j = $resultTiret[$i][1];
        		  	$k++;
        		  }
        			foreach($matriceSearch as $idMatrice => $valueMatrice)
        			{
        			$resultCount = eregi(''.$valueMatrice.'',$valueResultTiret,$resultatMatrice);
        			if ($resultCount)
        			{
        				// On r�cup�re le pr�fix utilis�
        				if ($idMatrice == 'prefix')
        				{
        					if ($resultatMatrice[0] == 'COM')
        					{
        						if ($objsoc->prefix_comm)
        						{
        							$this->prefix = $objsoc->prefix_comm;
        						}
        						else
        					  {
        						  $this->prefix = 'COM';
        					  }
        					  $this->numMatrice[$k] = '$prefix';
        					  $this->searchLast .= $this->prefix;
        					  $this->searchLastWithNoYear .= $this->prefix;
        					  $this->searchLastWithPreviousYear .= $this->prefix;
        					  $k++;
        					}
        					else if ($resultatMatrice[0] == 'PREF')
        				  {
        					  $this->numMatrice[$k] = '$prefix';
        					  $this->searchLast .= $this->prefix;
        					  $this->searchLastWithNoYear .= $this->prefix;
        					  $this->searchLastWithPreviousYear .= $this->prefix;
        					  $k++;
        					}
        				}
        				else if ($idMatrice == 'year')
        				{
        					// On r�cup�re le nombre de chiffres pour l'ann�e
        					$numbityear = $resultCount;
        					// On d�fini le mois du d�but d'ann�e fiscale
        					$current_month = date("n");
        					
        					if (is_object($ficheinter) && $ficheinter->date)
                  {
        	          $create_month = strftime("%m",$ficheinter->date);
                  }
                  else
                  {
        	          $create_month = $current_month;
                  }

                  // On change d'ann�e fiscal si besoin
                  if($conf->global->SOCIETE_FISCAL_MONTH_START > 1 && $current_month >= $conf->global->SOCIETE_FISCAL_MONTH_START && $create_month >= $conf->global->SOCIETE_FISCAL_MONTH_START)
                  {
        	          $this->yy = substr(strftime("%Y",mktime(0,0,0,date("m"),date("d"),date("Y")+1)),$numbityear);
                  }
                  else
                  {
        	          $this->yy = substr(strftime("%Y",time()),$numbityear);
                  }
        					$this->numMatrice[$k] = '$yy';
        					$this->searchLast .= $this->yy;
        					for ($l = 1; $l <= $numbityear; $l++)
        					{
        						$this->searchLastWithNoYear .= '[0-9]';
        					}
        					$previousYear = substr(strftime("%Y",mktime(0,0,0,date("m"),date("d"),date("Y")-1)),$numbityear);
        					$this->searchLastWithPreviousYear .= $previousYear;
        					$k++;
        				}
        				else if ($idMatrice == 'month')
        				{
        					// On r�cup�re le mois si besoin
        					$this->mm = strftime("%m",time());
        					$this->numMatrice[$k] = '$mm';
        					$this->searchLast .= '[0-9][0-9]';
        					$this->searchLastWithNoYear .= '[0-9][0-9]';
        					$this->searchLastWithPreviousYear .= '[0-9][0-9]';
        					$k++;
        				}
        				else if ($idMatrice == 'counter')
        				{
        					// On r�cup�re le nombre de chiffres pour le compteur
        					$this->numbitcounter = $resultCount;
        					$this->numMatrice[$k] = '$num';
        					$k++;
        				}
        			}
        		}
        	}
        }
        return 1;
      }
      else
      {
      	return -3;
      }
    }
}    

?>