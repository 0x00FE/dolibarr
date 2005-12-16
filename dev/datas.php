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
		   "A�sha","Arianne","Angie","Arnon","Armando",
		   "Bachir","Boris","Bernard",		   
		   "Constance","Claudine","Charles","Christobald",
		   "Daniel","Diego","Dimitri",
		   "Edgar","Edouard","Edmond","Ernest","Emilie","Emerito",
		   "Fran�ois","Farid","Florence",
		   "Gaspard","Guiseppe","Gavin","Griselle",
		   "Hakim","Hocine","Hernan","H�l�ne",
		   "Igor","Ibrahim","Isidore","Ingrid",
		   "Jos�","Joseph","Jos�phine","Jocelyne","James","Juan","Juliette","Javier",
		   "Kevin",
		   "Li","Laure","Laurent","Luka",
		   "Martin","Manuel","Moshe","Mao","Mohamed","Michel","Marwan","Micka�l","Miguel","Medhi","Mustapha",
		   "Norbert","No�mie","Nicole","Nadia",
		   "Olivier","Oscar","Orlando",   
		   "Paulo","Peter","Pablo",
		   "Quentin",
		   "Raoul","Rom�o","Romuald","Rafael","Rosa","Rosalind","Rogelio","Ra�ssa","Rodrigue",
		   "Sylvain","Sylvie","Samir","Susie","Samantha",
		   "Th�odore",
		   "Ursule",
		   "Victoire","Vincente","Victor",
		   "Yann","Youssef","Yahcine",
		   "Zao","Zora","Za�ra");

  $x = rand(0,sizeof($prenoms));

  return $prenoms[$x];
}

?>
