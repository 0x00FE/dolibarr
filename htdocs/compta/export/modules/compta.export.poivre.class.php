<?PHP
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
 *
 * $Id$
 * $Source$
 *
 */

require_once DOL_DOCUMENT_ROOT."/includes/php_writeexcel/class.writeexcel_workbook.inc.php";
require_once DOL_DOCUMENT_ROOT."/includes/php_writeexcel/class.writeexcel_worksheet.inc.php";

class ComptaExportPoivre
{
  function ComptaExportPoivre ($DB)
  {
    $this->db = $DB;
  }

  /**
   * Agr�gation des lignes de facture
   *
   *
   */

  function Agregate($line_in)
  {
    dolibarr_syslog("ComptaExportPoivre::Agregate");
    dolibarr_syslog("ComptaExportPoivre::Agregate " . sizeof($line_in) . " lignes en entr�es");
    $i = 0;
    $j = 0;
    $n = sizeof($line_in);

    // On commence par la ligne 0

    $this->line_out[$j] = $line_in[$i];

    //print "$j ".$this->line_out[$j][8] . "<br>";

    for ( $i = 1 ; $i < $n ; $i++)
      {
	// On agr�ge les lignes avec le m�me code comptable

	if ( ($line_in[$i][1] == $line_in[$i-1][1]) && ($line_in[$i][4] == $line_in[$i-1][4]) )
	  {
	    $this->line_out[$j][8] = ($this->line_out[$j][8] + $line_in[$i][8]);
	  }
	else
	  {
	    $j++;
	    $this->line_out[$j] = $line_in[$i];
	  }
	
	//	print "$j ".$this->line_out[$j][8] . "<br>";
    
      }

    dolibarr_syslog("ComptaExportPoivre::Agregate " . sizeof($this->line_out) . " lignes en sorties");

    return 0;
  }

  function Export($linec, $linep)
  {
    $error = 0;

    dolibarr_syslog("ComptaExportPoivre::Export");
    dolibarr_syslog("ComptaExportPoivre::Export " . sizeof($linec) . " lignes en entr�es");

    $this->Agregate($linec);

    $this->db->begin();

    $dt = strftime('EC%y%m', time());

    $sql = "SELECT count(ref) FROM ".MAIN_DB_PREFIX."export_compta";
    $sql .= " WHERE ref like '$dt%'";

    if ($this->db->query($sql))
      {
	$row = $this->db->fetch_row();
	$cc = $row[0];
      }
    else
      {
	$error++;
	dolibarr_syslog("ComptaExportPoivre::Export Erreur Select");
      }


    if (!$error)
      {
	$ref = $dt . substr("000".$cc, -2);
		
	$sql = "INSERT INTO ".MAIN_DB_PREFIX."export_compta (ref, date_export)";
	$sql .= " VALUES ('$ref', now())";
	
	if ($this->db->query($sql))
	  {
	    $ecid = $this->db->last_insert_id();
	  }
	else
	  {
	    $error++;
	    dolibarr_syslog("ComptaExportPoivre::Export Erreur INSERT");
	  }
      }


    if (!$error)
      {
	dolibarr_syslog("ComptaExportPoivre::Export ref : $ref");

	$this->id = $this->db->last_insert_id();

	$fxname = DOL_DATA_ROOT."/compta/export/".$ref.".xls";

	$workbook = &new writeexcel_workbook($fxname);

	$page = &$workbook->addworksheet('Export');

	$page->set_column(0,0,8); // A
	$page->set_column(1,1,6); // B
	$page->set_column(2,2,9); // C
	$page->set_column(3,3,14); // D
	$page->set_column(4,4,40); // E

    
	// Pour les factures

	// Date Op�ration 040604 pour 4 juin 2004
	// VE -> ventilation
	// code Compte g�n�ral
	// code client
	// Intitul�
	// Num�ro de pi�ce
	// Montant
	// Type op�ration D pour D�bit ou C pour Cr�dit
	// Date d'�ch�ance, = � la date d'op�ration si pas d'�ch�ance
	// EUR pour Monnaie en Euros
    
	// Pour les paiements

	$i = 0;
	$j = 0;
	$n = sizeof($this->line_out);

	$oldfacture = 0;

	for ( $i = 0 ; $i < $n ; $i++)
	  {
	    if ( $oldfacture <> $this->line_out[$i][1])
	      {
		// Ligne client
		$page->write_string($j, 0, strftime("%d%m%y",$this->line_out[$i][0]));
		$page->write_string($j, 1,  "VE");
		$page->write_string($j, 2,  "41100000");
		$page->write_string($j, 3, stripslashes($this->line_out[$i][2]));
		$page->write_string($j, 4, stripslashes($this->line_out[$i][3])." Facture");
		$page->write_string($j, 5, $this->line_out[$i][9]); // Num�ro de facture
		$page->write($j, 6, ereg_replace(",",".",$this->line_out[$i][7]));
		$page->write_string($j, 7, 'D' ); // D pour d�bit

		$j++;

		// Ligne TVA
		$page->write_string($j, 0, strftime("%d%m%y",$this->line_out[$i][0]));
		$page->write_string($j, 1, "VE");
		$page->write_string($j, 2, '4457119');

		$page->write_string($j, 4, stripslashes($this->line_out[$i][3])." Facture");
		$page->write_string($j, 5, $this->line_out[$i][9]); // Num�ro de facture
		$page->write($j, 6, ereg_replace(",",".",$this->line_out[$i][6])); // Montant de TVA
		$page->write_string($j, 7, 'C'); // C pour cr�dit
	    
		$oldfacture = $this->line_out[$i][1];
		$j++;
	      }
	    $page->write_string($j, 0, strftime("%d%m%y",$this->line_out[$i][0]));
	    $page->write_string($j, 1, 'VE');
	    $page->write_string($j, 2, $this->line_out[$i][4]); // Code Comptable
	    $page->write_string($j, 4, $this->line_out[$i][3]." Facture");
	    $page->write_string($j, 5, $this->line_out[$i][9]);                  // Num�ro de facture
	    $page->write($j, 6, ereg_replace(",",".",round($this->line_out[$i][8], 2)));  // Montant de la ligne
	    $page->write_string($j, 7, 'C');                     // C pour cr�dit

	    $j++;
	  }

	// Tag des lignes de factures
	$n = sizeof($linec);
	for ( $i = 0 ; $i < $n ; $i++)

	  {
	    $sql = "UPDATE ".MAIN_DB_PREFIX."facturedet";
	    $sql .= " SET fk_export_compta=".$ecid;
	    $sql .= " WHERE rowid = ".$linec[$i][10];

	    if (!$this->db->query($sql))
	      {
		$error++;
	      }
	  }

	// Pour les paiements

	// Date Op�ration 040604 pour 4 juin 2004
	// CE -> caisse d'epargne
	// code Compte g�n�ral
	// code client
	// Intitul�
	// Num�ro de pi�ce
	// Montant
	// Type op�ration D pour D�bit ou C pour Cr�dit
	// Date d'�ch�ance, = � la date d'op�ration si pas d'�ch�ance
	// EUR pour Monnaie en Euros
    
	$i = 0;
	//    $j = 0;
	$n = sizeof($linep);

	$oldfacture = 0;

	for ( $i = 0 ; $i < $n ; $i++)
	  {
	    /*
	     * En cas de rejet ou paiement en n�gatif on inverse debit et credit
	     *
	     *
	     */
	    if ($linep[$i][5] >= 0)
	      {
		$debit = "D";
		$credit = "C";
	      }
	    else
	      {
		$debit = "C";
		$credit = "D";
	      }

	    $page->write_string($j,0, strftime("%d%m%y",$linep[$i][0]));
	    $page->write_string($j,1, 'CE');

	    $page->write_string($j,2, '5122000');

	    if ($linep[$i][6] == 'Pr�l�vement')
	      {
		$linep[$i][6] = 'Prelevement';
	      }

	    $page->write_string($j,4, stripslashes($linep[$i][3])." ".stripslashes($linep[$i][6])); // 
	    $page->write_string($j,5, $linep[$i][7]);                  // Num�ro de facture
	    $page->write($j,6, ereg_replace(",",".",round(abs($linep[$i][5]), 2)));  // Montant de la ligne
	    $page->write_string($j,7,$debit);
	
	    $j++;

	    $page->write_string($j,0, strftime("%d%m%y",$linep[$i][0]));
	    $page->write_string($j,1, 'CE');

	    $page->write_string($j,2, '41100000');
	    $page->write_string($j,3, $linep[$i][2]);
	    $page->write_string($j,4, stripslashes($linep[$i][3])." ".stripslashes($linep[$i][6])); // 
	    $page->write_string($j,5, $linep[$i][7]);                  // Num�ro de facture
	    $page->write($j,6, ereg_replace(",",".",round(abs($linep[$i][5]), 2)));  // Montant de la ligne
	    $page->write_string($j,7, $credit);
	
	    $j++;

	  }
	$workbook->close();    

	// Tag des lignes de factures
	$n = sizeof($linep);
	for ( $i = 0 ; $i < $n ; $i++)

	  {
	    $sql = "UPDATE ".MAIN_DB_PREFIX."paiement";
	    $sql .= " SET fk_export_compta=".$ecid;
	    $sql .= " WHERE rowid = ".$linep[$i][1];

	    if (!$this->db->query($sql))
	      {
		$error++;
	      }
	  }

      }

    if (!$error)
      {
	$this->db->commit();
	dolibarr_syslog("ComptaExportPoivre::Export COMMIT");
      }
    else
      {
	$this->db->rollback();
	dolibarr_syslog("ComptaExportPoivre::Export ROLLBACK");
      }

    return 0;
  }
}
