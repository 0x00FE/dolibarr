<?PHP
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

function venus_get_num_explain()
{

  return '
Renvoie le num�ro de facture sous la forme, F-PR-030202, o� PR est le pr�fixe commercial de la soci�t�, et est suivi de la date sur un format de 6 digits avec Ann�e, Mois et Jour';

}

function pluton_get_num_explain()
{

  return '
Renvoie le num�ro de facture sous une forme num�rique simple, la premi�re facture porte le num�ro 1, la douzi�me facture ayant le num�ro 12.';

}

?>
