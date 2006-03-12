<?php
/* Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
        \file       htdocs/includes/modules/societe/mod_codeclient_leopard.class.php
        \ingroup    societe
        \brief      Fichier de la classe des gestion leopard des codes clients
        \version    $Revision$
*/


/**
        \class 		mod_codeclient_leopard
        \brief 		Classe permettant la gestion leopard des codes clients
*/

class mod_codeclient_leopard
{

  /*
   * Attention ce module est utilis� par d�faut si aucun module n'a 
   * �t� d�finit dans la configuration
   *
   * Le fonctionnement de celui-ci doit dont rester le plus ouvert
   * possible
   */

  function mod_codeclient_leopard()
  {
    $this->nom = "L�opard";
    $this->code_modifiable = 1;

    $this->code_modifiable_invalide = 1; // code modifiable si il est invalide

    $this->code_modifiable_null = 1; // code modifiable si il est null

    $this->code_null = 1;


  }

  function info()
  {
    return "Renvoie toujours ok, pour ceux qui ne veulent pas faire de test.";
  }
  
  function verif($db, $code)
  {
    // Renvoie toujours ok
    return 0;
  }
}

?>
