<?php
/* Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
        \file       htdocs/includes/modules/societe/mod_codeclient_zebre.class.php
        \ingroup    societe
        \brief      Fichier de la classe des gestion zebre des codes clients
        \version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT."/includes/modules/societe/modules_societe.class.php");


/**
        \class 		mod_codeclient_zebre
        \brief 		Classe permettant la gestion zebre des codes tiers
*/

class mod_codeclient_zebre extends ModeleThirdPartyCode
{

  function mod_codeclient_zebre()
  {
    $this->nom = "Z�bre";

    $this->code_modifiable = 0; // code modifiable

    $this->code_modifiable_invalide = 0; // code modifiable si il est invalide

    $this->code_modifiable_null = 1; // code modifiable si il est null

    $this->code_null = 0; // Saisi vide interdite

  }
  /*!     \brief      Renvoi la description du module
   *      \return     string      Texte descripif
   */
  function info($langs)
    {
      return "V�rifie si le code client est de la forme ABCD5600. Les quatres premi�res lettres �tant une repr�sentation mn�motechnique, suivi du code postal en 2 chiffres et un num�ro d'ordre pour la prise en compte des doublons.";
    }

  /**
   * V�rifie la validit� du code
   *
   *
   */

  function verif($db, &$code, $socid=0)
    { 
      $code = strtoupper(trim($code));

      if ($this->verif_syntax($code) == 0)
	{	  
	  $i = 1;

	  $is_dispo = $this->verif_dispo($db, $code, $socid);

	  while ( $is_dispo <> 0 && $i < 99)
	    {
	      $arr = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	      
	      $code = substr($code,0,6) . substr("00".$i, -2);
	      
	      $is_dispo = $this->verif_dispo($db, $code);
	      
	      $i++;
	    }

	  if ($is_dispo <> 0)
	    {
	      return -3;
	    }
	}
      else
	{
	  if (strlen(trim($code)) == 0)
	    {
	      return -2;
	    }
	  else
	    {
	      return -1;
	    }
	}
    }

  function get_correct($db, &$code)
    { 
      $code = strtoupper(trim($code));

      if ($this->verif_syntax($code) == 0)
	{	  
	  $i = 1;

	  $is_dispo = $this->verif_dispo($db, $code);

	  while ( $is_dispo <> 0 && $i < 99)
	    {
	      $arr = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	      
	      $code = substr($code,0,6) . substr("00".$i, -2);
	      
	      $is_dispo = $this->verif_dispo($db, $code);
	      
	      $i++;
	    }

	  return $is_dispo;

	}
      else
	{
	  return -1;
	}

    }

  function verif_dispo($db, $code)
  {
    $code = strtoupper(trim($code));

    $sql = "SELECT code_client FROM ".MAIN_DB_PREFIX."societe";
    $sql .= " WHERE code_client = '".$code."'";

    if ($db->query($sql))
      {
	if ($db->num_rows() == 0)
	  {
	    return 0;
	  }
	else
	  {
	    return -1;
	  }
      }
    else
      {
	return -2;
      }

  }


  function verif_syntax(&$code)
  {
    $res = 0;
    
    $code = strtoupper(trim($code));
    
    if (strlen($code) <> 8)
      {
	$res = -1;
      }
    else
      {
	if ($this->is_alpha(substr($code,0,4)) == 0 && $this->is_num(substr($code,4,4)) == 0 )
	  {
	    $res = 0;	      
	  }
	else
	  {
	    $res = -2; 
	  }
	
      }
    return $res;
  }


  function is_alpha($str)
  {
    $ok = 0;
    // Je n'ai pas trouv� de fonction pour tester une chaine alpha sans les caract�re accentu�s
    // dommage
    $alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';      

    for ($i = 0 ; $i < 4 ; $i++)
      {
	if (strpos($alpha, substr($str,$i, 1)) === false)
	{
	  $ok++;
	}
      }
    
    return $ok;
  }

  function is_num($str)
  {
    $ok = 0;

    $alpha = '0123456789';

    for ($i = 0 ; $i < 4 ; $i++)
      {
	if (strpos($alpha, substr($str,$i, 1)) === false)
	{
	  $ok++;
	}
      }
    
    return $ok;
  }

}

?>
