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
 *
 */

class mod_codecompta_panicum
{

  function mod_codecompta_panicum()
  {
    $this->nom = "Panicum";
  }

  function info()
  {
    return "Renvoie un code compta identique � celui saisi. Module d�di� aux tests";
  }
  
  function get_code($db, $societe)
  {
    // Renvoie toujours ok
    $this->code = $societe->code_compta;
    return 0;
  }
}

?>
