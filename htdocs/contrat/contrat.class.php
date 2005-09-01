<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Destailleur Laurent  <eldy@users.sourceforge.net>
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
	    \file       htdocs/contrat/contrat.class.php
        \ingroup    contrat
		\brief      Fichier de la classe des contrats
		\version    $Revision$
*/


/**
        \class      Contrat
		\brief      Classe permettant la gestion des contrats
*/

class Contrat
{
    var $db;
    
    var $id;
    var $ref;
    var $product;
    var $societe;

    var $user_author;
    var $user_service;
    var $user_cloture;
    var $date_creation;
    var $date_cloture;

    var $commercial_signature_id;
    var $commercial_suivi_id;

    var $fk_projet;
        
    var $statuts=array();
    
        
    /**
     *    \brief      Constructeur de la classe
     *    \param      DB          handler acc�s base de donn�es
     */
    function Contrat($DB)
    {
        global $langs;
        
        $this->db = $DB ;
        $this->product = new Product($DB);
        $this->societe = new Societe($DB);
        $this->user_service = new User($DB);
        $this->user_cloture = new User($DB);
        
        // Statut 0=ouvert, 1=actif, 2=clotur�
        $this->statuts[0]=$langs->trans("Draft");
        $this->statuts[1]=$langs->trans("Validated");
        $this->statuts[2]=$langs->trans("Closed");
    }

    /**
     *      \brief      Active une ligne detail d'un contrat
     *      \param      user        Objet User qui avtice le contrat
     *      \param      line_id     Id de la ligne de detail � activer
     *      \param      date        Date d'ouverture
     *      \param      dateend     Date fin pr�vue
     *      \return     int         < 0 si erreur, > 0 si ok
     */
    function active_line($user, $line_id, $date, $dateend='')
    {
        // statut actif : 4
    
        $sql = "UPDATE ".MAIN_DB_PREFIX."contratdet SET statut = 4,";
        $sql.= " date_ouverture = '".$this->db->idate($date)."',";
        if ($dateend) $sql.= " date_fin_validite = '".$this->db->idate($dateend)."',";
        $sql.= " fk_user_ouverture = ".$user->id;
        $sql.= " WHERE rowid = ".$line_id . " AND (statut = 0 OR statut = 3) ";
    
        $result = $this->db->query($sql) ;
    
        if ($result)
        {
            // Appel des triggers
            include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
            $interface=new Interfaces($this->db);
            $interface->run_triggers('CONTRACT_SERVICE_ACTIVATE',$this,$user,$lang,$conf);
            // Fin appel triggers
    
            return 1;
        }
        else
        {
            $this->error=$this->db->error();
            return -1;
        }
    }
    
    
    /**
     *      \brief      Active une ligne detail d'un contrat
     *      \param      user        Objet User qui avtice le contrat
     *      \param      line_id     Id de la ligne de detail � activer
     *      \param      dateend     Date fin
     *      \return     int         <0 si erreur, >0 si ok
     */
    function close_line($user, $line_id, $dateend)
    {
        // statut actif : 4
    
        $sql = "UPDATE ".MAIN_DB_PREFIX."contratdet SET statut = 5,";
        $sql.= " date_cloture = '".$this->db->idate($dateend)."',";
        $sql.= " fk_user_cloture = ".$user->id;
        $sql.= " WHERE rowid = ".$line_id . " AND statut = 4";
    
        $result = $this->db->query($sql) ;
    
        if ($result)
        {
            // Appel des triggers
            include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
            $interface=new Interfaces($this->db);
            $interface->run_triggers('CONTRACT_SERVICE_CLOSE',$this,$user,$lang,$conf);
            // Fin appel triggers
    
            return 1;
        }
        else
        {
            $this->error=$this->db->error();
            return -1;
        }
    }
    

    /**
     *    \brief      Cloture un contrat
     *    \param      user      Objet User qui cloture
     *    \param      lang      Environnement langue de l'utilisateur
     *    \param      conf      Environnement de configuration lors de l'op�ration
     *
     */
    function cloture($user,$lang='',$conf='')
    {
        $sql = "UPDATE ".MAIN_DB_PREFIX."contrat SET statut = 2";
        $sql .= " , date_cloture = now(), fk_user_cloture = ".$user->id;
        $sql .= " WHERE rowid = ".$this->id . " AND statut = 1";
    
        $result = $this->db->query($sql) ;
    
        // Appel des triggers
        include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
        $interface=new Interfaces($this->db);
        $interface->run_triggers('CONTRACT_CLOSE',$this,$user,$lang,$conf);
        // Fin appel triggers

        return 1;
    }
    
    /**
     *    \brief      Valide un contrat
     *    \param      user      Objet User qui valide
     *    \param      lang      Environnement langue de l'utilisateur
     *    \param      conf      Environnement de configuration lors de l'op�ration
     */
    function validate($user,$lang='',$conf='')
    {
        $sql = "UPDATE ".MAIN_DB_PREFIX."contrat SET statut = 1";
        $sql .= " WHERE rowid = ".$this->id . " AND statut = 0";
    
        $result = $this->db->query($sql) ;
    
        // Appel des triggers
        include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
        $interface=new Interfaces($this->db);
        $interface->run_triggers('CONTRACT_VALIDATE',$this,$user,$lang,$conf);
        // Fin appel triggers
    }

    /**
     *    \brief      Annule un contrat
     *    \param      user      Objet User qui annule
     *    \param      lang      Environnement langue de l'utilisateur
     *    \param      conf      Environnement de configuration lors de l'op�ration
     */
    function annule($user,$lang='',$conf='')
    {
        $sql = "UPDATE ".MAIN_DB_PREFIX."contrat SET statut = 0";
        $sql .= " , date_cloture = now(), fk_user_cloture = ".$user->id;
        $sql .= " WHERE rowid = ".$this->id . " AND statut = 1";
    
        $result = $this->db->query($sql) ;
    
        // Appel des triggers
        include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
        $interface=new Interfaces($this->db);
        $interface->run_triggers('CONTRACT_CANCEL',$this,$user,$lang,$conf);
        // Fin appel triggers
    }
    
    /**
     *    \brief      Charge de la base les donn�es du contrat
     *    \param      id      id du contrat � charger
     *    \return     int     <0 si KO, >0 si OK
     */
    function fetch($id)
    {
        $sql = "SELECT rowid, statut, fk_soc, ".$this->db->pdate("mise_en_service")." as datemise,";
        $sql.= " fk_user_mise_en_service, ".$this->db->pdate("date_contrat")." as datecontrat,";
        $sql.= " fk_user_author,";
        $sql.= " fk_projet,";
        $sql.= " fk_commercial_signature, fk_commercial_suivi ";
        $sql.= " FROM ".MAIN_DB_PREFIX."contrat WHERE rowid = $id";
    
        $resql = $this->db->query($sql) ;
    
        if ($resql)
        {
            $result = $this->db->fetch_array($resql);
    
            $this->id                = $result["rowid"];
            $this->ref               = $result["rowid"];
            $this->statut            = $result["statut"];
            $this->factureid         = $result["fk_facture"];
            $this->facturedetid      = $result["fk_facturedet"];
            $this->mise_en_service   = $result["datemise"];
            $this->date_fin_validite = $result["datefin"];
            $this->date_contrat      = $result["datecontrat"];
    
            $this->user_author_id    = $result["fk_user_author"];
    
            $this->commercial_signature_id = $result["fk_commercial_signature"];
            $this->commercial_suivi_id = $result["fk_commercial_suivi"];
    
            $this->user_service->id = $result["fk_user_mise_en_service"];
            $this->user_cloture->id = $result["fk_user_cloture"];

            $this->fk_projet        = $result["fk_projet"];
    
            $this->societe->fetch($result["fk_soc"]);
    
            $this->db->free($resql);
    
            return 1;
        }
        else
        {
            $this->error=$this->db->error();
            return -1;
        }
    
    }
    
    /**
     *      \brief      Cr�e un contrat vierge en base
     *      \param      user        Utilisateur qui cr�e
     *      \param      lang        Environnement langue de l'utilisateur
     *      \param      conf        Environnement de configuration lors de l'op�ration
     *      \return     int         < 0 si erreur, id contrat cr�� sinon
     */
    function create($user,$lang='',$conf='')
    {
        global $langs;
        
        // Controle validit� des param�tres
        $paramsok=1;
        if ($this->commercial_signature_id <= 0)
        {
            $langs->load("commercial");
            $this->error.=$langs->trans("ErrorFieldRequired",$langs->trans("SalesRepresentativeSignature"));
            $paramsok=0;
        }
        if ($this->commercial_suivi_id <= 0)
        {
            $langs->load("commercial");
            $this->error.=($this->error?"<br>":'');
            $this->error.=$langs->trans("ErrorFieldRequired",$langs->trans("SalesRepresentativeFollowUp"));
            $paramsok=0;
        }
        if (! $paramsok) return -1;
        
        // Ins�re contrat
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."contrat (datec, fk_soc, fk_user_author, fk_commercial_signature, fk_commercial_suivi, date_contrat)";
        $sql.= " VALUES (now(),".$this->soc_id.",".$user->id;
        $sql.= ",".($this->commercial_signature_id>=0?$this->commercial_signature_id:"null");
        $sql.= ",".($this->commercial_suivi_id>=0?$this->commercial_suivi_id:"null");
        $sql.= ",".$this->db->idate($this->date_contrat) .")";
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."contrat");
    
            // Appel des triggers
            include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
            $interface=new Interfaces($this->db);
            $interface->run_triggers('CONTRACT_CREATE',$this,$user,$lang,$conf);
            // Fin appel triggers
    
            $result = $this->id;
        }
        else
        {
            $this->error=$lang->trans("UnknownError: ".$this->db->error()." - sql=".$sql);
            dolibarr_syslog("Contrat::create - 10");
            $result = -1;
        }
    
        return $result;
    }
    

    /**
     *      \brief      Supprime un contrat de la base
     *      \param      user        Utilisateur qui supprime
     *      \param      lang        Environnement langue de l'utilisateur
     *      \param      conf        Environnement de configuration lors de l'op�ration
     *      \return     int         < 0 si erreur, > 0 si ok
     */
    function delete($user,$lang='',$conf='')
    {
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."contrat";
        $sql.= " WHERE rowid=".$this->id;
        if ($this->db->query($sql))
        {
            // Appel des triggers
            include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
            $interface=new Interfaces($this->db);
            $interface->run_triggers('CONTRACT_DELETE',$this,$user,$lang,$conf);
            // Fin appel triggers
    
            $result = 1;
        }
        else
        {
            $result = -1;
        }
    
        return $result;
    }

    
    /**
     *      \brief      Ajoute une ligne de contrat en base
     *      \param      desc            Description de la ligne
     *      \param      pu              Prix unitaire
     *      \param      qty             Quantit�
     *      \param      txtva           Taux tva
     *      \param      fk_product      Id produit
     *      \param      remise_percent  Pourcentage de remise de la ligne
     *      \param      datestart       Date de debut pr�vue
     *      \param      dateend         Date de fin pr�vue
     *      \return     int             <0 si erreur, >0 si ok
     */
    function addline($desc, $pu, $qty, $txtva, $fk_product=0, $remise_percent=0, $datestart, $dateend)
    {
        global $langs;
        
        $qty = ereg_replace(",",".",$qty);
        $pu = ereg_replace(",",".",$pu);
        
        if (strlen(trim($qty))==0)
        {
            $qty=1;
        }
        
        if ($fk_product > 0)
        {
            $prod = new Product($this->db, $fk_product);
            if ($prod->fetch($fk_product) > 0)
            {
                $label = $prod->libelle;
                $pu    = $prod->price;
                $txtva = $prod->tva_tx;
            }
        }
        
        $remise = 0;
        $price = ereg_replace(",",".",round($pu, 2));
        $subprice = $price;
        if (trim(strlen($remise_percent)) > 0)
        {
            $remise = round(($pu * $remise_percent / 100), 2);
            $price = $pu - $remise;
        }
        
        // Insertion dans la base
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."contratdet";
        $sql.= " (fk_contrat, label, description, fk_product, price_ht, qty, tva_tx,";
        $sql.= " remise_percent, subprice, remise";
        if ($datestart > 0) { $sql.= ",date_ouverture_prevue"; }
        if ($dateend > 0)  { $sql.= ",date_fin_validite"; }
        $sql.= ") VALUES ($this->id, '" . addslashes($label) . "','" . addslashes($desc) . "',";
        $sql.= ($fk_product>0 ? $fk_product : "null");
        $sql.= ",".ereg_replace(",",".",$price).", '$qty', $txtva, $remise_percent,'".ereg_replace(",",".",$subprice)."','".ereg_replace(",",".", $remise)."'";
        if ($datestart > 0) { $sql.= ",".$this->db->idate($datestart); }
        if ($dateend > 0) { $sql.= ",".$this->db->idate($dateend); }
        $sql.= ");";
        
        // Retour
        if ( $this->db->query($sql) )
        {
            //$this->update_price();
            return 0;
        }
        else
        {
            dolibarr_print_error($this->db);
            return -1;
        }
    }

    /**
     *      \brief     Mets � jour une ligne de contrat
     *      \param     rowid            Id de la ligne de facture
     *      \param     desc             Description de la ligne
     *      \param     pu               Prix unitaire
     *      \param     qty              Quantit�
     *      \param     remise_percent   Pourcentage de remise de la ligne
     *      \param     datestart        Date de debut pr�vue
     *      \param     dateend          Date de fin pr�vue
     *      \param     tvatx            Taux TVA
     *      \return    int              < 0 si erreur, > 0 si ok
     */
    function updateline($rowid, $desc, $pu, $qty, $remise_percent=0, $datestart='', $dateend='', $tvatx)
    {
        // Nettoyage parametres
        $qty=trim($qty);
        $desc=trim($desc);
        $desc=trim($desc);
        $price = ereg_replace(",",".",$pu);
        $tvatx = ereg_replace(",",".",$tvatx);
        $subprice = $price;
        $remise = 0;
        if (strlen($remise_percent) > 0)
        {
            $remise = round(($pu * $remise_percent / 100), 2);
            $price = $pu - $remise;
        }
        else
        {
            $remise_percent=0;
        }

        dolibarr_syslog("Contrat::UpdateLine $rowid, $desc, $pu, $qty, $remise_percent, $datestart, $dateend, $tvatx");
    
        $this->db->begin();

        $sql = "UPDATE ".MAIN_DB_PREFIX."contratdet set description='".addslashes($desc)."'";
        $sql .= ",price_ht='" .     ereg_replace(",",".",$price)."'";
        $sql .= ",subprice='" .     ereg_replace(",",".",$subprice)."'";
        $sql .= ",remise='" .       ereg_replace(",",".",$remise)."'";
        $sql .= ",remise_percent='".ereg_replace(",",".",$remise_percent)."'";
        $sql .= ",qty='$qty'";
        $sql .= ",tva_tx='".        ereg_replace(",",".",$tvatx)."'";

        if ($datestart > 0) { $sql.= ",date_ouverture_prevue=".$this->db->idate($datestart); }
        else { $sql.=",date_ouverture_prevue=null"; }
        if ($dateend > 0) { $sql.= ",date_fin_validite=".$this->db->idate($dateend); }
        else { $sql.=",date_fin_validite=null"; }

        $sql .= " WHERE rowid = $rowid ;";

        $result = $this->db->query($sql);
        if ($result)
        {
            $this->db->commit();

            return $result;
        }
        else
        {
            $this->db->rollback();

            dolibarr_print_error($this->db);
            return -1;
        }
    }
    
  /** 
   *    \brief      Supprime une ligne de detail du contrat
   *    \param      idligne     id de la ligne detail de contrat � supprimer
   */
  function delete_line($idligne)
    {

      $sql = "DELETE FROM ".MAIN_DB_PREFIX."contratdet WHERE rowid =".$idligne;
      
      if ($this->db->query($sql) )
	{
	  //$this->update_price();
	  
	  return 0;
	}
      else
	{
	  return 1;
	}
    }


    /**
     *      \brief     Classe le contrat dans un projet
     *      \param     projid       Id du projet dans lequel classer le contrat
     */
    function classin($projid)
    {
        $sql = "UPDATE ".MAIN_DB_PREFIX."contrat";
        if ($projid) $sql.= " SET fk_projet = $projid";
        else $sql.= " SET fk_projet = NULL";
        $sql.= " WHERE rowid = ".$this->id;

        if ($this->db->query($sql))
        {
            return 1;
        }
        else
        {
            dolibarr_print_error($this->db);
            return -1;
        }
    }


  /**
   *    \brief      Retourne le libell� du statut du contrat
   *    \return     string      Libell�
   */
    function getLibStatut()
    {
		return $this->LibStatut($this->statut);
    }

  /**
   *    \brief      Renvoi le libell� d'un statut donn�
   *    \param      statut      id statut
   *    \return     string      Libell�
   */
    function LibStatut($statut)
    {
        global $langs;
        $langs->load("contracts");

        if ($statut == 0) { return $langs->trans("ContractStatusDraft"); }
        if ($statut == 1) { return $langs->trans("ContractStatusValidated"); }
        if ($statut == 2) { return $langs->trans("ContractStatusClosed"); }
    }


   /*
    *       \brief     Charge les informations d'ordre info dans l'objet contrat
    *       \param     id     id du contrat a charger
    */
    function info($id)
    {
        $sql = "SELECT c.rowid, ".$this->db->pdate("datec")." as datec, ".$this->db->pdate("date_cloture")." as date_cloture,";
        $sql.= $this->db->pdate("c.tms")." as date_modification,";
        $sql.= " fk_user_author, fk_user_cloture";
        $sql.= " FROM ".MAIN_DB_PREFIX."contrat as c";
        $sql.= " WHERE c.rowid = ".$id;

        $result=$this->db->query($sql);
        if ($result)
        {
            if ($this->db->num_rows($result))
            {
                $obj = $this->db->fetch_object($result);

                $this->id = $obj->rowid;

                if ($obj->fk_user_author) {
                    $cuser = new User($this->db, $obj->fk_user_author);
                    $cuser->fetch();
                    $this->user_creation     = $cuser;
                }

                if ($obj->fk_user_cloture) {
                    $cuser = new User($this->db, $obj->fk_user_cloture);
                    $cuser->fetch();
                    $this->user_cloture = $cuser;
                }

                $this->date_creation     = $obj->datec;
                $this->date_modification = $obj->date_modification;
                $this->date_cloture      = $obj->date_cloture;
            }

            $this->db->free($result);

        }
        else
        {
            dolibarr_print_error($this->db);
        }
    }
 
 
    /** 
     *    \brief      R�cup�re les lignes de detail du contrat
     *    \param      statut      Statut des lignes detail � r�cup�rer
     *    \return     array       Tableau des lignes de details
     */
    function array_detail($statut=-1)
    {
        $tab=array();
        
        $sql = "SELECT cd.rowid";
        $sql.= " FROM ".MAIN_DB_PREFIX."contratdet as cd";
        $sql.= " WHERE fk_contrat =".$this->id;
        if ($statut >= 0) $sql.= " AND statut = '$statut'";
   
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $num=$this->db->num_rows($result);
            $i=0;
            while ($i < $num)
            {
                $obj = $this->db->fetch_object($result);
                $tab[$i]=$obj->rowid;
                $i++;
            }
            return $tab;
        }
        else
        {
            $this->error=$this->db->error();
            return -1;
        }
    }


    /**
     *      \brief      Charge indicateurs this->nbtodo et this->nbtodolate de tableau de bord
     *      \param      user        Objet user
     *      \param      mode        "inactive" pour services � activer, "expired" pour services expir�s
     *      \return     int         <0 si ko, >0 si ok
     */
    function load_board($user,$mode)
    {
        global $conf;
        
        $this->nbtodo=$this->nbtodolate=0;
        if ($mode == 'inactives')
        {
            $sql = "SELECT cd.rowid,".$this->db->pdate("cd.date_ouverture_prevue")." as datefin";
            $sql.= " FROM ".MAIN_DB_PREFIX."contrat as c, ".MAIN_DB_PREFIX."contratdet as cd";
            $sql.= " WHERE c.statut = 1 AND c.rowid = cd.fk_contrat";
            $sql.= " AND cd.statut = 0";
        }
        if ($mode == 'expired')
        {
            $sql = "SELECT cd.rowid,".$this->db->pdate("cd.date_fin_validite")." as datefin";
            $sql.= " FROM ".MAIN_DB_PREFIX."contrat as c, ".MAIN_DB_PREFIX."contratdet as cd";
            $sql.= " WHERE c.statut = 1 AND c.rowid = cd.fk_contrat";
            $sql.= " AND cd.statut = 4";
            $sql.= " AND cd.date_fin_validite < '".$this->db->idate(time())."'";
        }
        if ($user->societe_id) $sql.=" AND fk_soc = ".$user->societe_id;
        $resql=$this->db->query($sql);
        if ($resql)
        {
            while ($obj=$this->db->fetch_object($resql))
            {
                $this->nbtodo++;
                if ($mode == 'inactives')
                    if ($obj->datefin && $obj->datefin < (time() - $conf->contrat->services->inactifs->warning_delay)) $this->nbtodolate++;
                if ($mode == 'expired')
                    if ($obj->datefin && $obj->datefin < (time() - $conf->contrat->services->expires->warning_delay)) $this->nbtodolate++;
            }
            return 1;
        }
        else 
        {
            dolibarr_print_error($this->db);
            $this->error=$this->db->error();
            return -1;
        }
    }
    
}
?>
