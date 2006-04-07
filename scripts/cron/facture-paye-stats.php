<?PHP
/* Copyright (C) 2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
 *
 * Statistiques sur le statut paye des factures
 *
 */
require ("../../htdocs/master.inc.php");

$verbose = 0;

for ($i = 1 ; $i < sizeof($argv) ; $i++)
{
  if ($argv[$i] == "-v")
    {
      $verbose = 1;
    }
  if ($argv[$i] == "-vv")
    {
      $verbose = 2;
    }
  if ($argv[$i] == "-vvv")
    {
      $verbose = 3;
    }
}

/*
 * Lecture des lignes a r�silier
 *
 */
$sql = "SELECT paye, count(*)";
$sql .= " FROM ".MAIN_DB_PREFIX."facture";
$sql .= " GROUP BY paye";

$resql = $db->query($sql);

if ($resql)
{
  while ($row = $db->fetch_row($resql))
    {
      $sqli = "INSERT INTO ".MAIN_DB_PREFIX."facture_stats";
      $sqli .= " VALUES (now(),now(),'$row[0]',$row[1])";
     
      $resqli = $db->query($sqli);
    }
}

?>
