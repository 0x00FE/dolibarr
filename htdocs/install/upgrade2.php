<?php
/* Copyright (C) 2005      Marc Barilley / Oc�bo <marc@ocebo.com>
 * Copyright (C) 2005-2006 Laurent Destailleur   <eldy@users.sourceforge.net>
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
 */

/**
	\file       htdocs/install/upgrade2.php
	\brief      Effectue la migration de donn�es diverses
	\version    $Revision$
*/

include_once('./inc.php');
if (file_exists($conffile)) include_once($conffile);
if (! isset($dolibarr_main_db_prefix) || ! $dolibarr_main_db_prefix) $dolibarr_main_db_prefix='llx_'; 
define('MAIN_DB_PREFIX',$dolibarr_main_db_prefix);
require_once($dolibarr_main_document_root . "/lib/databases/".$dolibarr_main_db_type.".lib.php");
require_once($dolibarr_main_document_root . "/conf/conf.class.php");
include_once('../facture.class.php');
include_once('../propal.class.php');
include_once('../commande/commande.class.php');
include_once('../lib/price.lib.php');

$grant_query='';
$etape = 2;
$error = 0;


// Cette page peut etre longue. On augmente le d�lai autorise.
// Ne fonctionne que si on est pas en safe_mode.
$err=error_reporting();
error_reporting(0);
@set_time_limit(60);
error_reporting($err);

$setuplang=isset($_POST['selectlang'])?$_POST['selectlang']:(isset($_GET['selectlang'])?$_GET['selectlang']:'auto');
$langs->setDefaultLang($setuplang);

$langs->load('admin');
$langs->load('install');
$langs->load("bills");
$langs->load("suppliers");

if ($dolibarr_main_db_type == 'mysql')  $choix=1;
if ($dolibarr_main_db_type == 'mysqli') $choix=1;
if ($dolibarr_main_db_type == 'pgsql')  $choix=2;


dolibarr_install_syslog("upgrade2: Entering upgrade2.php page");


pHeader($langs->trans('DataMigration'),'etape5','upgrade');


if (isset($_POST['action']) && $_POST['action'] == 'upgrade')
{
	print '<h2>'.$langs->trans('DataMigration').'</h2>';

	print '<table cellspacing="0" cellpadding="1" border="0" width="100%">';
	
	// on d�code le mot de passe de la base si besoin
	require_once(DOL_DOCUMENT_ROOT ."/lib/functions.inc.php");
	if ($dolibarr_main_db_encrypted_pass) $dolibarr_main_db_pass = dolibarr_decode($dolibarr_main_db_encrypted_pass);

	$conf = new Conf();// on pourrait s'en passer
	$conf->db->type = $dolibarr_main_db_type;
	$conf->db->host = $dolibarr_main_db_host;
	$conf->db->name = $dolibarr_main_db_name;
	$conf->db->user = $dolibarr_main_db_user;
	$conf->db->pass = $dolibarr_main_db_pass;

	$db = new DoliDb($conf->db->type,$conf->db->host,$conf->db->user,$conf->db->pass,$conf->db->name);
	if ($db->connected != 1)
	{
		print '<tr><td colspan="4">'.$langs->trans("ErrorFailedToConnectToDatabase",$dolibarr_main_db_name).'</td><td align="right">'.$langs->trans('Error').'</td></tr>';
		$error++;
	}

	if (! $error)
	{
		if($db->database_selected == 1)
		{
			dolibarr_install_syslog('upgrade2: Database connection successfull : '.$dolibarr_main_db_name);
		}
		else
		{
			$error++;
		}
	}

	// Chargement config
	define('DOL_DOCUMENT_ROOT', $dolibarr_main_document_root);
	$conf->setValues($db);


	/*
	 * Pour utiliser d'autres versions des librairies externes que les
	 * versions embarqu�es dans Dolibarr, d�finir les constantes adequates:
	 * Pour FPDF:           FPDF_PATH
	 * Pour Pear:           PEAR_PATH
	 * Pour PHP_WriteExcel: PHP_WRITEEXCEL_PATH
	 * Pour MagpieRss:      MAGPIERSS_PATH
	 * Pour PHPlot:         PHPLOT_PATH
	 * Pour JPGraph:        JPGRAPH_PATH
	 * Pour NuSOAP:         NUSOAP_PATH
	 * Pour TCPDF:          TCPDF_PATH
	 */
	if (! defined('FPDF_PATH'))           { define('FPDF_PATH',          DOL_DOCUMENT_ROOT .'/includes/fpdf/fpdf/'); }
	if (! defined('PEAR_PATH'))           { define('PEAR_PATH',          DOL_DOCUMENT_ROOT .'/includes/pear/'); }
	if (! defined('PHP_WRITEEXCEL_PATH')) { define('PHP_WRITEEXCEL_PATH',DOL_DOCUMENT_ROOT .'/includes/php_writeexcel/'); }
	if (! defined('MAGPIERSS_PATH'))      { define('MAGPIERSS_PATH',     DOL_DOCUMENT_ROOT .'/includes/magpierss/'); }
	if (! defined('PHPLOT_PATH'))         { define('PHPLOT_PATH',        DOL_DOCUMENT_ROOT .'/includes/phplot/'); }
	if (! defined('JPGRAPH_PATH'))        { define('JPGRAPH_PATH',       DOL_DOCUMENT_ROOT .'/includes/jpgraph/'); }
	if (! defined('NUSOAP_PATH'))         { define('NUSOAP_PATH',        DOL_DOCUMENT_ROOT .'/includes/nusoap/lib/'); }
	if (! defined('TCPDF_PATH'))          { define('TCPDF_PATH',         DOL_DOCUMENT_ROOT .'/includes/fpdf/tcpdf/'); }
	// Les autres path
	if (! defined('FPDF_FONTPATH'))       { define('FPDF_FONTPATH',      FPDF_PATH . 'font/'); }
	if (! defined('MAGPIE_DIR'))          { define('MAGPIE_DIR',         MAGPIERSS_PATH); }
	if (! defined('MAGPIE_CACHE_DIR'))    { define('MAGPIE_CACHE_DIR',   DOL_DATA_ROOT .'/rss/temp'); }


	/***************************************************************************************
	*
	* Migration des donn�es
	*
	***************************************************************************************/
	if (! $error)
	{
	
		$db->begin();
		
		print '<tr><td colspan="4" nowrap="nowrap">&nbsp;</td></tr>';

        // Chaque action de migration doit renvoyer une ligne sur 4 colonnes avec
        // dans la 1ere colonne, la description de l'action a faire
        // dans la 4eme colonne, le texte 'OK' si fait ou 'AlreadyDone' si rien n'est fait ou 'Error'

        migrate_paiements($db,$langs,$conf);

        migrate_contracts_det($db,$langs,$conf);

        migrate_contracts_date1($db,$langs,$conf);

        migrate_contracts_date2($db,$langs,$conf);

        migrate_contracts_date3($db,$langs,$conf);

        migrate_contracts_open($db,$langs,$conf);

        migrate_modeles($db,$langs,$conf);

		migrate_price_propal($db,$langs,$conf);

		migrate_price_commande($db,$langs,$conf);

		migrate_price_facture($db,$langs,$conf);

        migrate_paiementfourn_facturefourn($db,$langs,$conf);

		migrate_delete_old_files($db,$langs,$conf);
		
    	// On commit dans tous les cas.
    	// La proc�dure etant con�ue pour pouvoir passer plusieurs fois quelquesoit la situation.
    	$db->commit();
    	
    	$db->close();
    	
	}

	print '</table>';
	
}
else
{
	print '<div class="error">'.$langs->trans('ErrorWrongParameters').'</div>';
	$error++;
}


pFooter($error,$setuplang);




/**
 * Mise a jour des paiements (lien n-n paiements factures)
 */
function migrate_paiements($db,$langs,$conf)
{
 	print '<tr><td colspan="4">';
 
    print '<br>';
    print '<b>'.$langs->trans('MigrationPaymentsUpdate')."</b><br>\n";
    
    $sql = "SELECT p.rowid, p.fk_facture, p.amount";
    $sql .= " FROM ".MAIN_DB_PREFIX."paiement as p";
    $sql .= " WHERE p.fk_facture > 0";
    $resql = $db->query($sql);
    
    if ($resql) 
    {
      $i = 0;
      $row = array();
      $num = $db->num_rows($resql);
      
      while ($i < $num)
        {
          $obj = $db->fetch_object($resql);
          $row[$i][0] = $obj->rowid ;
          $row[$i][1] = $obj->fk_facture;
          $row[$i][2] = $obj->amount;
          $i++;
        }
    }
    else {
        dolibarr_print_error($db);   
    }
    
    if ($num)
    {
        print $langs->trans('MigrationPaymentsNumberToUpdate', $num)."<br>\n";
        if ($db->begin())
        {
          $res = 0;
          for ($i = 0 ; $i < sizeof($row) ; $i++)
            {
              $sql = "INSERT INTO ".MAIN_DB_PREFIX."paiement_facture (fk_facture, fk_paiement, amount)";
              $sql .= " VALUES (".$row[$i][1].",".$row[$i][0].",".$row[$i][2].")";
              
              $res += $db->query($sql);
              
              $sql = "UPDATE ".MAIN_DB_PREFIX."paiement SET fk_facture = 0 WHERE rowid = ".$row[$i][0];
              
              $res += $db->query($sql);
        
              print $langs->trans('MigrationProcessPaymentUpdate', $row[$i])."<br>\n";
            } 
        }
        
        if ($res == (2 * sizeof($row)))
        {
          $db->commit();
          print $langs->trans('MigrationSuccessfullUpdate')."<br>";
        }
        else
        {
          $db->rollback();
          print $langs->trans('MigrationUpdateFailed').'<br>';
        }
    }
    else
    {
        print $langs->trans('MigrationPaymentsNothingToUpdate')."<br>\n";
    }

	print '</td></tr>';
}


/*
 * Mise a jour des contrats (gestion du contrat + detail de contrat)
 */
function migrate_contracts_det($db,$langs,$conf)
{
 	print '<tr><td colspan="4">';

    $nberr=0;
    
    print '<br>';
    print '<b>'.$langs->trans('MigrationContractsUpdate')."</b><br>\n";
    
    $sql = "SELECT c.rowid as cref, c.date_contrat, c.statut, c.mise_en_service, c.fin_validite, c.date_cloture, c.fk_product, c.fk_facture, c.fk_user_author,";
    $sql.= " p.ref, p.label, p.description, p.price, p.tva_tx, p.duration, cd.rowid";
    $sql.= " FROM ".MAIN_DB_PREFIX."contrat as c";
    $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product as p";
    $sql.= " ON c.fk_product = p.rowid";
    $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."contratdet as cd";
    $sql.= " ON c.rowid=cd.fk_contrat";
    $sql.= " WHERE cd.rowid IS NULL AND p.rowid IS NOT NULL";
    $resql = $db->query($sql);
    
    if ($resql) 
    {
        $i = 0;
        $row = array();
        $num = $db->num_rows($resql);
    
        if ($num)
        {
            print $langs->trans('MigrationContractsNumberToUpdate', $num)."<br>\n";
            $db->begin();
            
            while ($i < $num)
            {
                $obj = $db->fetch_object($resql);
                
                $sql = "INSERT INTO ".MAIN_DB_PREFIX."contratdet (";
                $sql.= "fk_contrat, fk_product, statut, label, description,";
                $sql.= "date_ouverture_prevue, date_ouverture, date_fin_validite, tva_tx, qty,";
                $sql.= "subprice, price_ht, fk_user_author, fk_user_ouverture)";
                $sql.= " VALUES (";
                $sql.= $obj->cref.",".($obj->fk_product?$obj->fk_product:0).",";
                $sql.= ($obj->mise_en_service?"4":"0").",";
                $sql.= "'".addslashes($obj->label)."', null,";
                $sql.= ($obj->mise_en_service?"'".$obj->mise_en_service."'":($obj->date_contrat?"'".$obj->date_contrat."'":"null")).",";
                $sql.= ($obj->mise_en_service?"'".$obj->mise_en_service."'":"null").",";
                $sql.= ($obj->fin_validite?"'".$obj->fin_validite."'":"null").",";
                $sql.= "'".$obj->tva_tx."', 1,";
                $sql.= "'".$obj->price."', '".$obj->price."',".$obj->fk_user_author.",";
                $sql.= ($obj->mise_en_service?$obj->fk_user_author:"null");
                $sql.= ")";
    
                if ($db->query($sql)) 
                {
                    print $langs->trans('MigrationContractsLineCreation', $obj->cref)."<br>\n";
                }
                else 
                {            
                    dolibarr_print_error($db);
                    $nberr++;
                }
    
                $i++;
            }
    
            if (! $nberr)
            {
            //      $db->rollback();
                  $db->commit();
						print $langs->trans('MigrationSuccessfullUpdate')."<br>";
            }
            else
            {
                  $db->rollback();
						print $langs->trans('MigrationUpdateFailed').'<br>';
            }
        }
        else {
            print $langs->trans('MigrationContractsNothingToUpdate')."<br>\n";
        }
    }
    else
    {
        print $langs->trans('MigrationContractsFieldDontExist')."<br>\n";
    //    dolibarr_print_error($db);   
    }

	print '</td></tr>';
}


/*
 * Mise a jour des date de contrats non renseign�es
 */
function migrate_contracts_date1($db,$langs,$conf)
{
 	print '<tr><td colspan="4">';

    print '<br>';
    print '<b>'.$langs->trans('MigrationContractsEmptyDatesUpdate')."</b><br>\n";
    
    $sql="update llx_contrat set date_contrat=tms where date_contrat is null";
    $resql = $db->query($sql);
    if (! $resql) dolibarr_print_error($db);
    if ($db->affected_rows($resql) > 0) 
	 	print $langs->trans('MigrationContractsEmptyDatesUpdateSuccess')."<br>\n";
    else
	 	print $langs->trans('MigrationContractsEmptyDatesNothingToUpdate')."<br>\n";
    
    $sql="update llx_contrat set datec=tms where datec is null";
    $resql = $db->query($sql);
    if (! $resql) dolibarr_print_error($db);
    if ($db->affected_rows($resql) > 0) 
	 	print $langs->trans('MigrationContractsEmptyCreationDatesUpdateSuccess')."<br>\n";
    else
	 	print $langs->trans('MigrationContractsEmptyCreationDatesNothingToUpdate')."<br>\n";

	print '</td></tr>';
}


/*
 * Mise a jour date contrat avec date min effective mise en service si inf�rieur
 */
function migrate_contracts_date2($db,$langs,$conf)
{
 	print '<tr><td colspan="4">';

    $nberr=0;
    
    print '<br>';
    print '<b>'.$langs->trans('MigrationContractsInvalidDatesUpdate')."</b><br>\n";
    
    $sql = "SELECT c.rowid as cref, c.datec, c.date_contrat, MIN(cd.date_ouverture) as datemin";
    $sql.= " FROM ".MAIN_DB_PREFIX."contrat as c,";
    $sql.= " ".MAIN_DB_PREFIX."contratdet as cd";
    $sql.= " WHERE c.rowid=cd.fk_contrat AND cd.date_ouverture IS NOT NULL";
    $sql.= " GROUP BY c.rowid, c.date_contrat";
    $resql = $db->query($sql);
    
    if ($resql) 
    {
        $i = 0;
        $row = array();
        $num = $db->num_rows($resql);
    
        if ($num)
        {
            $nbcontratsmodifie=0;
            $db->begin();
            
            while ($i < $num)
            {
                $obj = $db->fetch_object($resql);
                if ($obj->date_contrat > $obj->datemin) 
                {
                    print $langs->trans('MigrationContractsInvalidDateFix', $obj->cref, $obj->date_contrat, $obj->datemin)."<br>\n";
                    $sql ="UPDATE ".MAIN_DB_PREFIX."contrat";
                    $sql.=" SET date_contrat='".$obj->datemin."'";
                    $sql.=" WHERE rowid=".$obj->cref;
                    $resql2=$db->query($sql);
                    if (! $resql2) dolibarr_print_error($db);
                    
                    $nbcontratsmodifie++;
                }
                $i++;
            }
    
            $db->commit();
    
            if ($nbcontratsmodifie)
					print $langs->trans('MigrationContractsInvalidDatesNumber', $nbcontratsmodifie)."<br>\n";
            else
					print  $langs->trans('MigrationContractsInvalidDatesNothingToUpdate')."<br>\n";
        }
    }
    else
    {
        dolibarr_print_error($db);
    }
    
	print '</td></tr>';
}


/*
 * Mise a jour des dates de cr�ation de contrat
 */
function migrate_contracts_date3($db,$langs,$conf)
{
 	print '<tr><td colspan="4">';

    print '<br>';
    print '<b>'.$langs->trans('MigrationContractsIncoherentCreationDateUpdate')."</b><br>\n";
    
    $sql="update llx_contrat set datec=date_contrat where datec is null or datec > date_contrat";
    $resql = $db->query($sql);
    if (! $resql) dolibarr_print_error($db);
    if ($db->affected_rows() > 0)
	 	print $langs->trans('MigrationContractsIncoherentCreationDateUpdateSuccess')."<br>\n";
    else
	 	print $langs->trans('MigrationContractsIncoherentCreationDateNothingToUpdate')."<br>\n";

	print '</td></tr>';
}


/*
 * Reouverture des contrats qui ont au moins une ligne non ferm�e
 */
function migrate_contracts_open($db,$langs,$conf)
{
 	print '<tr><td colspan="4">';

    print '<br>';
    print '<b>'.$langs->trans('MigrationReopeningContracts')."</b><br>\n";
    
    $sql = "SELECT c.rowid as cref FROM llx_contrat as c, llx_contratdet as cd";
    $sql.= " WHERE cd.statut = 4 AND c.statut=2 AND c.rowid=cd.fk_contrat";
    $resql = $db->query($sql);
    if (! $resql) dolibarr_print_error($db);
    if ($db->affected_rows() > 0) {
        $i = 0;
        $row = array();
        $num = $db->num_rows($resql);
    
        if ($num)
        {
            $nbcontratsmodifie=0;
            $db->begin();
            
            while ($i < $num)
            {
                $obj = $db->fetch_object($resql);
    
                print $langs->trans('MigrationReopenThisContract', $obj->cref)."<br>\n";
                $sql ="UPDATE ".MAIN_DB_PREFIX."contrat";
                $sql.=" SET statut=1";
                $sql.=" WHERE rowid=".$obj->cref;
                $resql2=$db->query($sql);
                if (! $resql2) dolibarr_print_error($db);
                
                $nbcontratsmodifie++;
    
                $i++;
            }
    
            $db->commit();
    
            if ($nbcontratsmodifie)
					print $langs->trans('MigrationReopenedContractsNumber', $nbcontratsmodifie)."<br>\n";
            else
					print $langs->trans('MigrationReopeningContractsNothingToUpdate')."<br>\n";
        }
    }
    else print $langs->trans('MigrationReopeningContractsNothingToUpdate')."<br>\n";

	print '</td></tr>';
}


/**
 * Factures fournisseurs
 */
function migrate_paiementfourn_facturefourn($db,$langs,$conf)
{
	global $bc;
	
	$error = 0;
    $nb=0;
	$select_sql  = 'SELECT rowid, fk_facture_fourn, amount ';
	$select_sql .= ' FROM '.MAIN_DB_PREFIX.'paiementfourn ';
	$select_sql .= ' WHERE fk_facture_fourn IS NOT NULL';
	$select_resql = $db->query($select_sql);
	if ($select_resql)
	{
		$select_num = $db->num_rows($select_resql);
		$i=0;
		$var = true;
        
		// Pour chaque paiement fournisseur, on ins�re une ligne dans paiementfourn_facturefourn
		while (($i < $select_num) && (! $error))
		{
			$var = !$var;
			$select_obj = $db->fetch_object($select_resql);

			// V�rifier si la ligne est d�j� dans la nouvelle table. On ne veut pas ins�rer de doublons.
			$check_sql  = 'SELECT fk_paiementfourn, fk_facturefourn';
			$check_sql .= ' FROM '.MAIN_DB_PREFIX.'paiementfourn_facturefourn';
			$check_sql .= ' WHERE fk_paiementfourn = '.$select_obj->rowid.' AND fk_facturefourn = '.$select_obj->fk_facture_fourn.';';
			$check_resql = $db->query($check_sql);
			if ($check_resql)
			{
				$check_num = $db->num_rows($check_resql);
				if ($check_num == 0)
				{
            		if ($nb == 0) 
            		{
                		print '<tr><td colspan="4" nowrap="nowrap"><b>'.$langs->trans('SuppliersInvoices').'</b></td></tr>';
            		    print '<tr><td>fk_paiementfourn</td><td>fk_facturefourn</td><td>'.$langs->trans('Amount').'</td><td>&nbsp;</td></tr>';
                    }
                    
    				print '<tr '.$bc[$var].'>';
    				print '<td>'.$select_obj->rowid.'</td><td>'.$select_obj->fk_facture_fourn.'</td><td>'.$select_obj->amount.'</td>';

					$insert_sql  = 'INSERT INTO '.MAIN_DB_PREFIX.'paiementfourn_facturefourn SET ';
					$insert_sql .= ' fk_paiementfourn = \''.$select_obj->rowid.'\',';
					$insert_sql .= ' fk_facturefourn  = \''.$select_obj->fk_facture_fourn.'\',';
					$insert_sql .= ' amount           = \''.$select_obj->amount.'\';';
					$insert_resql = $db->query($insert_sql);
					if ($insert_resql)
					{
						$nb++;
						print '<td><span style="color:green">'.$langs->trans("OK").'</span></td>';
					}
					else
					{
						print '<td><span style="color:red">Error on insert</span></td>';
						$error++;
					}
    				print '</tr>';
				}
			}
			else
			{
				$error++;
			}
			$i++;
		}
	}
	else
	{
        $error++;
	}
	if (! $nb && ! $error) 
	{
   		print '<tr><td colspan="3" nowrap="nowrap"><b>'.$langs->trans('SuppliersInvoices').'</b></td><td align="right">'.$langs->trans("AlreadyDone").'</td></tr>';
    }
	if ($error) 
	{
   		print '<tr><td colspan="3" nowrap="nowrap"><b>'.$langs->trans('SuppliersInvoices').'</b></td><td align="right">'.$langs->trans("Error").'</td></tr>';
    }
}



/*
 * Mise a jour des totaux lignes de facture
 */
function migrate_price_facture($db,$langs,$conf)
{
    if ($conf->facture->enabled)
    {
		$db->begin();

		dolibarr_install_syslog("upgrade2: Upgrade data for invoice");

	 	print '<tr><td colspan="4">';

	    print '<br>';
	    print '<b>'.$langs->trans('MigrationInvoice')."</b><br>\n";
		
		// Liste des lignes facture non a jour
		$sql = "SELECT fd.rowid, fd.qty, fd.subprice, fd.remise_percent, fd.tva_taux, ";
		$sql.= " f.rowid as facid, f.remise_percent as remise_percent_global";
		$sql.= " FROM ".MAIN_DB_PREFIX."facturedet as fd, ".MAIN_DB_PREFIX."facture as f";
		$sql.= " WHERE fd.fk_facture = f.rowid";
		$sql.= " AND ((fd.total_ttc = 0 AND fd.remise_percent != 100) or fd.total_ttc IS NULL)";
		$resql=$db->query($sql);
		if ($resql)
		{
			$num = $db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				while ($i < $num)
				{
					$obj = $db->fetch_object($resql);
					
					$rowid = $obj->rowid;
					$qty = $obj->qty;
					$pu = $obj->subprice;
					$txtva = $obj->tva_taux;
					$remise_percent = $obj->remise_percent;
					$remise_percent_global = $obj->remise_percent_global;
					
					// On met a jour les 3 nouveaux champs
					$facligne= new FactureLigne($db);
					$facligne->fetch($rowid);

					$result=calcul_price_total($qty,$pu,$remise_percent,$txtva,$remise_percent_global);
					$total_ht  = $result[0];
					$total_tva = $result[1];
					$total_ttc = $result[2];
					
					$facligne->total_ht  = $total_ht; 
					$facligne->total_tva = $total_tva;
					$facligne->total_ttc = $total_ttc;
					
					dolibarr_install_syslog("upgrade2: Line $rowid: facid=$obj->facid pu=$pu qty=$qty tva_taux=$txtva remise_percent=$remise_percent remise_global=$remise_percent_global -> $total_ht, $total_tva, $total_ttc");
					print ". ";
					$facligne->update_total();
															
					
					/* On touche pas a facture mere
					$facture = new Facture($db);
					$facture->id=$obj->facid;
	
					if ( $facture->fetch($facture->id) >= 0)
					{
						if ( $facture->update_price($facture->id) > 0 )
						{
							print ". ";
						}
						else
						{
							print "Error id=".$facture->id;
							$err++;
						}
					}
					else
					{
						print "Error #3";
						$err++;
					}
					*/
					$i++;
				}
			}
			else
			{
				print $langs->trans("AlreadyDone");	
			}
			$db->free();

			$db->rollback();
		}
		else
		{
			print "Error #1 ".$db->error();
			$err++;

			$db->rollback();
		}

		print '<br><br>';
		
		print '</td></tr>';
	}
}


/*
 * Mise a jour des totaux lignes de propal
 */
function migrate_price_propal($db,$langs,$conf)
{
    if ($conf->propal->enabled)
    {
		$db->begin();

		dolibarr_install_syslog("upgrade2: Upgrade data for propal");

	 	print '<tr><td colspan="4">';

	    print '<br>';
	    print '<b>'.$langs->trans('MigrationProposal')."</b><br>\n";

		// Liste des lignes propal non a jour
		$sql = "SELECT pd.rowid, pd.qty, pd.subprice, pd.remise_percent, pd.tva_tx as tva_taux, ";
		$sql.= " p.rowid as propalid, p.remise_percent as remise_percent_global";
		$sql.= " FROM ".MAIN_DB_PREFIX."propaldet as pd, ".MAIN_DB_PREFIX."propal as p";
		$sql.= " WHERE pd.fk_propal = p.rowid";
		$sql.= " AND ((pd.total_ttc = 0 AND pd.remise_percent != 100) or pd.total_ttc IS NULL)";
		$resql=$db->query($sql);
		if ($resql)
		{
			$num = $db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				while ($i < $num)
				{
					$obj = $db->fetch_object($resql);
	
					$rowid = $obj->rowid;
					$qty = $obj->qty;
					$pu = $obj->subprice;
					$txtva = $obj->tva_taux;
					$remise_percent = $obj->remise_percent;
					$remise_percent_global = $obj->remise_percent_global;
					
					// On met a jour les 3 nouveaux champs
					$propalligne= new PropaleLigne($db);
					$propalligne->fetch($rowid);

					$result=calcul_price_total($qty,$pu,$remise_percent,$txtva,$remise_percent_global);
					$total_ht  = $result[0];
					$total_tva = $result[1];
					$total_ttc = $result[2];
					
					$propalligne->total_ht  = $total_ht; 
					$propalligne->total_tva = $total_tva;
					$propalligne->total_ttc = $total_ttc;
					
					dolibarr_install_syslog("upgrade2: Line $rowid: propalid=$obj->rowid pu=$pu qty=$qty tva_taux=$txtva remise_percent=$remise_percent remise_global=$remise_percent_global -> $total_ht, $total_tva, $total_ttc");
					print ". ";
					$propalligne->update_total($rowid);


					/* On touche pas a propal mere
					$propal = new Propal($db);
					$propal->id=$obj->rowid;
					if ( $propal->fetch($propal->id) >= 0 )
					{
						if ( $propal->update_price($propal->id) > 0 )
						{
							print ". ";
						}
						else
						{
							print "Error id=".$propal->id;
							$err++;
						}
					}
					else
					{
						print "Error #3";
						$err++;
					}
					*/
					$i++;
				}
			}
			else
			{
				print $langs->trans("AlreadyDone");	
			}
				
			$db->free();

			$db->rollback();
		}
		else
		{
			print "Error #1 ".$db->error();
			$err++;

			$db->rollback();
		}

		print '<br>';

		print '</td></tr>';
	}
}


/*
 * Mise a jour des totaux lignes de commande
 */
function migrate_price_commande($db,$langs,$conf)
{
    if ($conf->facture->enabled)
    {
		$db->begin();

		dolibarr_install_syslog("upgrade2: Upgrade data for order");

	 	print '<tr><td colspan="4">';

	    print '<br>';
	    print '<b>'.$langs->trans('MigrationOrder')."</b><br>\n";

		// Liste des lignes commande non a jour
		$sql = "SELECT cd.rowid, cd.qty, cd.subprice, cd.remise_percent, cd.tva_tx as tva_taux, ";
		$sql.= " c.rowid as commandeid, c.remise_percent as remise_percent_global";
		$sql.= " FROM ".MAIN_DB_PREFIX."commandedet as cd, ".MAIN_DB_PREFIX."commande as c";
		$sql.= " WHERE cd.fk_commande = c.rowid";
		$sql.= " AND ((cd.total_ttc = 0 AND cd.remise_percent != 100) or cd.total_ttc IS NULL)";
		$resql=$db->query($sql);
		if ($resql)
		{
			$num = $db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				while ($i < $num)
				{
					$obj = $db->fetch_object($resql);
	
					$rowid = $obj->rowid;
					$qty = $obj->qty;
					$pu = $obj->subprice;
					$txtva = $obj->tva_taux;
					$remise_percent = $obj->remise_percent;
					$remise_percent_global = $obj->remise_percent_global;
					
					// On met a jour les 3 nouveaux champs
					$commandeligne= new CommandeLigne($db);
					$commandeligne->fetch($rowid);

					$result=calcul_price_total($qty,$pu,$remise_percent,$txtva,$remise_percent_global);
					$total_ht  = $result[0];
					$total_tva = $result[1];
					$total_ttc = $result[2];
					
					$commandeligne->total_ht  = $total_ht; 
					$commandeligne->total_tva = $total_tva;
					$commandeligne->total_ttc = $total_ttc;
					
					dolibarr_install_syslog("upgrade2: Line $rowid: commandeid=$obj->rowid pu=$pu qty=$qty tva_taux=$txtva remise_percent=$remise_percent remise_global=$remise_percent_global -> $total_ht, $total_tva, $total_ttc");
					print ". ";
					$commandeligne->update_total($rowid);

					/* On touche pas a facture mere
					$commande = new Commande($db);
					$commande->id = $obj->rowid;
					if ( $commande->fetch($commande->id) >= 0 )
					{
						if ( $commande->update_price($commande->id) > 0 )
						{
							print ". ";
						}
						else
						{
							print "Error id=".$commande->id;
							$err++;
						}
					}
					else
					{
						print "Error #3";
						$err++;
					}
					*/
					$i++;
				}
			}
			else
			{
				print $langs->trans("AlreadyDone");	
			}

			$db->free();

			$sql = "DELETE FROM ".MAIN_DB_PREFIX."commandedet";
			$sql.= " WHERE price = 0 and total_ttc = 0 and total_tva = 0 and total_ht = 0";
			$resql=$db->query($sql);
			if (! $resql)
			{
				dolibarr_print_error($db);	
			}
			
			$db->rollback();
		}
		else
		{
			print "Error #1 ".$db->error();
			$err++;

			$db->rollback();
		}

		print '<br>';

		print '</td></tr>';
	}
}


/*
 * Mise a jour des modeles selectionnes
 */
function migrate_modeles($db,$langs,$conf)
{
    //print '<br>';
    //print '<b>'.$langs->trans('UpdateModelsTable')."</b><br>\n";
    
    if ($conf->facture->enabled)
    {
	    include_once(DOL_DOCUMENT_ROOT.'/includes/modules/facture/modules_facture.php');
	    $model=new ModelePDFFactures();
	    $modellist=$model->liste_modeles($db);
		if (sizeof($modellist)==0)
		{
	    	// Aucun model par defaut.
		    $sql=" insert into llx_document_model(nom,type) values('crabe','invoice')";
		    $resql = $db->query($sql);
		    if (! $resql) dolibarr_print_error($db);
		}
	}

    if ($conf->commande->enabled)
    {
	    include_once(DOL_DOCUMENT_ROOT.'/includes/modules/commande/modules_commande.php');
	    $model=new ModelePDFCommandes();
	    $modellist=$model->liste_modeles($db);
		if (sizeof($modellist)==0)
		{
	    	// Aucun model par defaut.
		    $sql=" insert into llx_document_model(nom,type) values('einstein','order')";
		    $resql = $db->query($sql);
		    if (! $resql) dolibarr_print_error($db);
		}
	}
	
    if ($conf->expedition->enabled)
    {
	    include_once(DOL_DOCUMENT_ROOT.'/expedition/mods/pdf/ModelePdfExpedition.class.php');
	    $model=new ModelePDFExpedition();
	    $modellist=$model->liste_modeles($db);
		if (sizeof($modellist)==0)
		{
	    	// Aucun model par defaut.
		    $sql=" insert into llx_document_model(nom,type) values('rouget','shipping')";
		    $resql = $db->query($sql);
		    if (! $resql) dolibarr_print_error($db);
		}
	}
	
	//print $langs->trans("AlreadyDone");
}

/*
 * Supprime fichiers obsoletes
 */
function migrate_delete_old_files($db,$langs,$conf)
{
    //print '<br>';
    //print '<b>'.$langs->trans('UpdateModelsTable')."</b><br>\n";
	if (file_exists(DOL_DOCUMENT_ROOT.'/includes/triggers/interface_demo.class.php-NORUN'))
	{
		@dol_delete_file(DOL_DOCUMENT_ROOT.'/includes/triggers/interface_demo.class.php');
	}
}

?>
