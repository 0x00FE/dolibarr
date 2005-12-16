<?PHP
/* Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
 *
 * $Id$
 * $Source$
 *
 */

function DevelPrenomAleatoire()
{
  $prenoms = array(
		   "A�sha","Arianne",
		   "Bachir","Boris","Bernard",		   
		   "Constance","Claudine","Charles","Christobald"
		   "Daniel",
		   "Edgar","Edouard",
		   "Fran�ois",
		   "Gaspard","Guiseppe",		   
		   "Hakim","Hocine",
		   "Igor","Ibrahim",
		   "Jos�","Joseph","Jos�phine","Jocelyne",
		   "Manuel",
		   "Li","Laure","Laurent",		   
		   "Mohamed","Michel","Marwan","Micka�l",
		   "Paulo",		   
		   "Olivier",		   
		   "Raoul","Romuald",		   		   
		   "Sylvain","Sylvie","Samir",
		   "Victoire","Vincente",
		   "Yann","Youssef",
		   "Zao","Zora");

  $x = rand(0,sizeof($prenoms));

  return $prenoms[$x];
}

?>
