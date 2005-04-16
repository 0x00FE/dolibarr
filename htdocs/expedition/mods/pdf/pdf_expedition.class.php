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
 * or see http://www.gnu.org/
 *
 * $Id$
 * $Source$
 *
 */

require_once DOL_DOCUMENT_ROOT.'/includes/fpdf/DolibarrPdfBarCode.class.php';

Class pdf_expedition extends DolibarrPdfBarCode
{

  function Header()
    {
      $this->rect(5, 5, 200, 30);

      $this->Code39(8, 8, $this->expe->ref);

      $this->SetFont('Arial','', 14);
      $this->Text(105, 12, "Bordereau d'exp�dition : ".$this->expe->ref);
      $this->Text(105, 18, "Date : " . strftime("%d %b %Y", $this->expe->date));
      $this->Text(105, 24, "Page : ". $this->PageNo() ."/{nb}", 0);


      $this->rect(5, 40, 200, 250);

      $this->tableau_top = 40;

      $this->SetFont('Arial','', 10);
      $a = $this->tableau_top + 5;
      $this->Text(10, $a, "Produit");
      $this->Text(166, $a, "Quantit�e");
      $this->Text(166, $a+4, "Command�e");
      $this->Text(190, $a, "Livr�e");

    }

}

?>
