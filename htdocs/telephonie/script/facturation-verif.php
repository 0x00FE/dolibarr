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
 *
 * Script de v�rification avant facture
 */

require ("../../master.inc.php");

require_once (DOL_DOCUMENT_ROOT."/societe.class.php");
require_once (DOL_DOCUMENT_ROOT."/telephonie/lignetel.class.php");
require_once (DOL_DOCUMENT_ROOT."/telephonie/facturetel.class.php");
require_once (DOL_DOCUMENT_ROOT."/telephonie/telephonie-tarif.class.php");
require_once (DOL_DOCUMENT_ROOT."/telephonie/communication.class.php");


$error = 0;

$sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."telephonie_import_cdr";
  
if ( $db->query($sql) )
{
  $row = $db->fetch_row();
  print $row[0]." lignes de communications\n";
}


/*******************************************************************************
 *
 * Verifie la pr�sence des tarifs adequat
 *
 */

$tarif_achat = new TelephonieTarif($db, 1, "achat");
$tarif_vente = new TelephonieTarif($db, 1, "vente");

$sql = "SELECT distinct(num) FROM ".MAIN_DB_PREFIX."telephonie_import_cdr";
  
if ( $db->query($sql) )
{
  $nums = $db->num_rows();

  $i = 0;

  while($i < $nums)
    {
      $row = $db->fetch_row();

      $numero = $row[0];

      /* Reformatage du num�ro */

      if (substr($numero,0,2) == '00') /* International */
	{
	}     
      elseif (substr($numero,0,2) == '06') /* Telephones Mobiles */
	{	
	  $numero = "0033".substr($numero,1);
	}
      elseif (substr($numero,0,4) == substr($objp->client,0,4) ) /* Tarif Local */
	{
	  $numero = "0033999".substr($numero, 1);
	}
      else
	{
	  $numero = "0033".substr($numero, 1);
	}	  

      /* Recherche du tarif */
      
      if (! $tarif_achat->cout($numero, $x, $y, $z))
	{
	  print "\nTarif achat manquant pour $numero\n";
	  exit(1);
	}
      
      if (! $tarif_vente->cout($numero, $x, $y, $z))
	{
	  print "\nTarif vente manquant pour $numero\n";
	  exit(1);
	}

      print ".";
      $i++;
    }
  $db->free();
}
print "\n";

unset ($nums, $row, $tarif_achat, $tarif_vente);

?>
