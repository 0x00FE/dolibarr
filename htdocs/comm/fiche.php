<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
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

/**	    \file       htdocs/comm/fiche.php
        \ingroup    commercial
		\brief      Onglet client de la fiche societe
		\version    $Revision$
*/
 
require("./pre.inc.php");
require("../contact.class.php");
require("../actioncomm.class.php");

$langs->load("companies");
$langs->load("orders");
$langs->load("contracts");

$user->getrights();

llxHeader();


$sortorder=$_GET["sortorder"];
$sortfield=$_GET["sortfield"];

if ($sortorder == "") {
  $sortorder="ASC";
}
if ($sortfield == "") {
  $sortfield="nom";
}


if ($_GET["action"] == 'attribute_prefix') {
  $societe = new Societe($db, $_GET["socid"]);
  $societe->attribute_prefix($db, $_GET["socid"]);
}

if ($action == 'recontact') {
  $dr = mktime(0, 0, 0, $remonth, $reday, $reyear);
  $sql = "INSERT INTO ".MAIN_DB_PREFIX."soc_recontact (fk_soc, datere, author) VALUES ($socid, $dr,'".  $user->login ."')";
  $result = $db->query($sql);
}

if ($action == 'stcomm') {
  if ($stcommid <> 'null' && $stcommid <> $oldstcomm) {
    $sql = "INSERT INTO socstatutlog (datel, fk_soc, fk_statut, author) ";
    $sql .= " VALUES ('$dateaction',$socid,$stcommid,'" . $user->login . "')";
    $result = @$db->query($sql);

    if ($result) {
      $sql = "UPDATE ".MAIN_DB_PREFIX."societe SET fk_stcomm=$stcommid WHERE idp=$socid";
      $result = $db->query($sql);
    } else {
      $errmesg = "ERREUR DE DATE !";
    }
  }

  if ($actioncommid) {
    $sql = "INSERT INTO ".MAIN_DB_PREFIX."actioncomm (datea, fk_action, fk_soc, fk_user_author) VALUES ('$dateaction',$actioncommid,$socid,'" . $user->id . "')";
    $result = @$db->query($sql);

    if (!$result) {
      $errmesg = "ERREUR DE DATE !";
    }
  }
}

/*
 * Recherche
 *
 *
 */
if ($mode == 'search') {
  if ($mode-search == 'soc') {
    $sql = "SELECT s.idp FROM ".MAIN_DB_PREFIX."societe as s ";
    $sql .= " WHERE lower(s.nom) like '%".strtolower($socname)."%'";
  }
      
  if ( $db->query($sql) ) {
    if ( $db->num_rows() == 1) {
      $obj = $db->fetch_object();
      $socid = $obj->idp;
    }
    $db->free();
  }
}
/*
 *
 */
$_socid = $_GET["socid"];
/*
 * S�curit� si un client essaye d'acc�der � une autre fiche que la sienne
 */
if ($user->societe_id > 0) 
{
  $_socid = $user->societe_id;
}
/*********************************************************************************
 *
 * Mode fiche
 *
 *
 *********************************************************************************/  
if ($_socid > 0)
{
  // On recupere les donnees societes par l'objet
  $objsoc = new Societe($db);
  $objsoc->id=$_socid;
  $objsoc->fetch($_socid,$to);
  
  $dac = strftime("%Y-%m-%d %H:%M", time());
  if ($errmesg)
    {
      print "<b>$errmesg</b><br>";
    }
  
  /*
   * Affichage onglets
   */
  $h = 0;
  
  $head[$h][0] = DOL_URL_ROOT.'/soc.php?socid='.$objsoc->id;
  $head[$h][1] = $langs->trans("Company");
  $h++;
  
  if ($objsoc->client==1)
    {
      $hselected=$h;
      $head[$h][0] = DOL_URL_ROOT.'/comm/fiche.php?socid='.$objsoc->id;
      $head[$h][1] = $langs->trans("Customer");;
      $h++;
    }
  if ($objsoc->client==2)
    {
      $hselected=$h;
      $head[$h][0] = DOL_URL_ROOT.'/comm/prospect/fiche.php?id='.$obj->socid;
      $head[$h][1] = $langs->trans("Prospect");
      $h++;
    }
  if ($objsoc->fournisseur)
    {
      $head[$h][0] = DOL_URL_ROOT.'/fourn/fiche.php?socid='.$objsoc->id;
      $head[$h][1] = $langs->trans("Supplier");
      $h++;
    }
  
  if ($conf->compta->enabled) {
    $langs->load("compta");
    $head[$h][0] = DOL_URL_ROOT.'/compta/fiche.php?socid='.$objsoc->id;
    $head[$h][1] = $langs->trans("Accountancy");
    $h++;
  }

  $head[$h][0] = DOL_URL_ROOT.'/socnote.php?socid='.$objsoc->id;
  $head[$h][1] = $langs->trans("Note");
  $h++;
  
  if ($user->societe_id == 0)
    {
      $head[$h][0] = DOL_URL_ROOT.'/docsoc.php?socid='.$objsoc->id;
      $head[$h][1] = $langs->trans("Documents");
      $h++;
    }
  
  $head[$h][0] = DOL_URL_ROOT.'/societe/notify/fiche.php?socid='.$objsoc->id;
  $head[$h][1] = $langs->trans("Notifications");
  $h++;
  
  if (file_exists(DOL_DOCUMENT_ROOT.'/sl/'))
    {
      $head[$h][0] = DOL_URL_ROOT.'/sl/fiche.php?id='.$objsoc->id;
      $head[$h][1] = 'Fiche catalogue';
      $h++;
    }
  
  if ($user->societe_id == 0)
    {
      $head[$h][0] = DOL_URL_ROOT."/comm/index.php?socidp=$objsoc->id&action=add_bookmark";
      $head[$h][1] = '<img border="0" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/bookmark.png" alt="Bookmark" title="Bookmark">';
      $head[$h][2] = 'image';
      $h++;
    }
  
  dolibarr_fiche_head($head, $hselected, $objsoc->nom);
  
  /*
   *
   *
   */
  print '<table width="100%" border="0">';
  print '<tr><td valign="top">';
  print '<table class="border" width="100%">';
  
  print '<tr><td width="20%">'.$langs->trans("Name").'</td><td width="80%" colspan="3">';
  print $objsoc->nom;
  print '</td></tr>';
  print "<tr><td valign=\"top\">".$langs->trans("Address")."</td><td colspan=\"3\">".nl2br($objsoc->adresse).'</td></tr>';
  
  print '<tr><td>'.$langs->trans('Zip').' / '.$langs->trans('Town').'</td><td colspan="3">'.$objsoc->cp." ".$objsoc->ville.'</td></tr>';
  print '<tr><td>'.$langs->trans('Country').'</td><td colspan="3">'.$objsoc->pays.'</td></tr>';
  
  print '<tr><td>'.$langs->trans("Phone").'</td><td>'.dolibarr_print_phone($objsoc->tel).'&nbsp;</td><td>Fax</td><td>'.dolibarr_print_phone($objsoc->fax).'&nbsp;</td></tr>';
  print '<tr><td>'.$langs->trans("Web")."</td><td colspan=\"3\"><a href=\"http://$objsoc->url\">$objsoc->url</a>&nbsp;</td></tr>";
  
  print "<tr><td nowrap>".$langs->trans("ProfIdSiren")."</td><td><a href=\"http://www.societe.com/cgi-bin/recherche?rncs=$objsoc->siren\">$objsoc->siren</a>&nbsp;</td>";
  print '<td>'.$langs->trans("Prefix").'</td><td>';
  if ($objsoc->prefix_comm)
    {
      print $objsoc->prefix_comm;
    }
  else
    {
      print "[<a href=\"fiche.php?socid=$objsoc->id&action=attribute_prefix\">Attribuer</a>]";
    }
  
  print "</td></tr>";
  
  print "<tr><td>".$langs->trans("Type")."</td><td> $objsoc->typent</td><td>Effectif</td><td>$objsoc->effectif</td></tr>";
  print '<tr><td nowrap>';
  print $langs->trans("CustomerDiscount").'</td><td>'.$objsoc->remise_client."&nbsp;%</td>";
  print '<td colspan="2"><a href="remise.php?id='.$objsoc->id.'">';
  print img_edit($langs->trans("Modify"));
  print "</a>";
  print '</td>';
  
  print '<tr><td colspan="2">Remise exceptionnelles';
  print '</td>';
  print '<td colspan="2"><a href="remx.php?id='.$objsoc->id.'">';
  print img_edit($langs->trans("Modify"));
  print "</a>";
  print '</td></tr>';
  
  print "</table>";
  
  print "<br>";
  
  /*
   *
   */
  print "</td>\n";
  
    print '<td valign="top" width="50%">';
    
    // Nbre max d'�l�ments des petites listes
    $MAXLIST=4;
    
    /*
     * Dernieres propales
     */
    if ($conf->propal->enabled)
    {
        print '<table class="border" width="100%">';
    
        $sql = "SELECT s.nom, s.idp, p.rowid as propalid, p.price, p.ref, p.remise, ".$db->pdate("p.datep")." as dp, c.label as statut, c.id as statutid";
        $sql .= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."propal as p, ".MAIN_DB_PREFIX."c_propalst as c";
        $sql .= " WHERE p.fk_soc = s.idp AND p.fk_statut = c.id";
        $sql .= " AND s.idp = ".$objsoc->id;
        $sql .= " ORDER BY p.datep DESC";
    
        if ( $db->query($sql) )
        {
            $var=true;
            $num = $db->num_rows();
            if ($num >0 )
            {
                print "<tr $bc[$var]>";
                print '<td colspan="4"><table width="100%" class="noborder"><tr><td>'.$langs->trans("LastPropals",($num<=$MAXLIST?"":$MAXLIST)).'</td><td align="right"><a href="'.DOL_URL_ROOT.'/comm/propal.php?socidp='.$objsoc->id.'">'.$langs->trans("AllPropals").' ('.$num.')</td></tr></table></td>';
                print '</tr>';
                $var=!$var;
            }
            $i = 0;	$now = time(); $lim = 3600 * 24 * 15 ;
            while ($i < $num && $i < $MAXLIST)
            {
                $objp = $db->fetch_object();
                print "<tr $bc[$var]>";
                print "<td><a href=\"propal.php?propalid=$objp->propalid\">$objp->ref</a>\n";
                if ( ($now - $objp->dp) > $lim && $objp->statutid == 1 )
                {
                    print " <b>&gt; 15 jours</b>";
                }
                print "</td><td align=\"right\">".strftime("%d %B %Y",$objp->dp)."</td>\n";
                print '<td align="right" width="120">'.price($objp->price).'</td>';
                print '<td width="100" align="center">'.$objp->statut.'</td></tr>';
                $var=!$var;
                $i++;
            }
            $db->free();
        }
        else {
            dolibarr_print_error($db);
        }
        print "</table>";
    }
    
    /*
     * Dernieres commandes
     */
    if($conf->commande->enabled)
    {
        print '<table class="border" width="100%">';
    
        $sql = "SELECT s.nom, s.idp, p.rowid as propalid, p.total_ht, p.ref, ".$db->pdate("p.date_commande")." as dp";
        $sql .= " FROM ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."commande as p";
        $sql .= " WHERE p.fk_soc = s.idp ";
        $sql .= " AND s.idp = $objsoc->id";
        $sql .= " ORDER BY p.date_commande DESC";
    
        if ( $db->query($sql) )
        {
            $var=true;
            $num = $db->num_rows();
            if ($num >0 )
            {
                print "<tr $bc[$var]>";
                print '<td colspan="4"><table width="100%" class="noborder"><tr><td>'.$langs->trans("LastOrders",($num<=$MAXLIST?"":$MAXLIST)).'</td><td align="right"><a href="'.DOL_URL_ROOT.'/commande/liste.php?socidp='.$objsoc->id.'">'.$langs->trans("AllOrders").' ('.$num.')</td></tr></table></td>';
                print '</tr>';
            }
            $i = 0;	$now = time(); $lim = 3600 * 24 * 15 ;
            while ($i < $num && $i < $MAXLIST)
            {
                $objp = $db->fetch_object();
                $var=!$var;
                print "<tr $bc[$var]>";
                print '<td><a href="'.DOL_URL_ROOT.'/commande/fiche.php?id='.$objp->propalid.'">'.$objp->ref."</a>\n";
                if ( ($now - $objp->dp) > $lim && $objp->statutid == 1 )
                {
                    print " <b>&gt; 15 jours</b>";
                }
                print "</td><td align=\"right\">".strftime("%d %B %Y",$objp->dp)."</td>\n";
                print '<td align="right" width="120">'.price($objp->total_ht).'</td>';
                print '<td align="center" width="100">'.$objp->statut.'</td></tr>';
                $i++;
            }
            $db->free();
        }
        print "</table>";
    }
    
    /*
     * Derniers projets associ�s
     */
    if ($conf->projet->enabled)
    {
        print '<table class="border" width=100%>';
    
        $sql  = "SELECT p.rowid,p.title,p.ref,".$db->pdate("p.dateo")." as do";
        $sql .= " FROM ".MAIN_DB_PREFIX."projet as p";
        $sql .= " WHERE p.fk_soc = $objsoc->id";
        $sql .= " ORDER BY p.dateo DESC";
    
        if ( $db->query($sql) ) {
            $var=true;
            $i = 0 ;
            $num = $db->num_rows();
            if ($num > 0) {
                print "<tr $bc[$var]>";
                print '<td colspan="2"><table width="100%" class="noborder"><tr><td>'.$langs->trans("LastProjects",($num<=$MAXLIST?"":$MAXLIST)).'</td><td align="right"><a href="'.DOL_URL_ROOT.'/projet/index.php?socidp='.$objsoc->id.'">'.$langs->trans("AllProjects").' ('.$num.')</td></tr></table></td>';
                print '</tr>';
            }
            while ($i < $num && $i < $MAXLIST) {
                $obj = $db->fetch_object();
                $var = !$var;
                print "<tr $bc[$var]>";
                print '<td><a href="../projet/fiche.php?id='.$obj->rowid.'">'.$obj->title.'</a></td>';
    
                print "<td align=\"right\">".$obj->ref ."</td></tr>";
                $i++;
            }
            $db->free();
        }
        else
        {
            dolibarr_print_error($db);
        }
        print "</table>";
    }
    
    /*
     *
     *
     */
    print "</td></tr>";
    print "</table></div>\n";
    
    
    /*
     * Barre d'action
     *
     */
    print '<div class="tabsAction">';

    if ($conf->propal->enabled && $user->rights->propale->creer)
      {
    $langs->load("propal");
	print '<a class="tabAction" href="addpropal.php?socidp='.$objsoc->id.'&amp;action=create">'.$langs->trans("AddProp").'</a>';
      }

    if ($user->rights->contrat->creer)
      {
    $langs->load("contracts");
	print '<a class="tabAction" href="'.DOL_URL_ROOT.'/contrat/fiche.php?socid='.$objsoc->id.'&amp;action=create">'.$langs->trans("AddContract").'</a>';
      }

    if ($conf->commande->enabled && $user->rights->commande->creer)
      {
    $langs->load("orders");
	print '<a class="tabAction" href="'.DOL_URL_ROOT.'/commande/fiche.php?socidp='.$objsoc->id.'&amp;action=create">'.$langs->trans("AddOrder").'</a>';
      }

    if ($conf->projet->enabled && $user->rights->projet->creer)
      {
	print '<a class="tabAction" href="../projet/fiche.php?socidp='.$objsoc->id.'&action=create">'.$langs->trans("AddProject").'</a>';
      }

    if ($conf->fichinter->enabled)
      {
	print '<a class="tabAction" href="../fichinter/fiche.php?socidp='.$objsoc->id.'&amp;action=create">Intervention</a>';
      }
  
    print '</div>';
    print '<br>';
    
    /*
     *
     *
     *
     */
    if ($action == 'changevalue')
      {
	print "<hr noshade>";
	print "<form action=\"index.php?socid=$objsoc->id\" method=\"post\">";
	print "<input type=\"hidden\" name=\"action\" value=\"cabrecrut\">";
	print "Cette soci�t� est un cabinet de recrutement : ";
	print "<select name=\"selectvalue\">";
	print "<option value=\"\">";
	print "<option value=\"t\">Oui";
	print "<option value=\"f\">Non";
	print "</select>";
	print "<input type=\"submit\" value=\"".$langs->trans("Valid")."\">";
	print "</form>\n";
      }
    else
      {
	/*
	 *
	 * Liste des contacts
	 *
	 */
	if ($conf->clicktodial->enabled)
	  {
	    $user->fetch_clicktodial(); // lecture des infos de clicktodial
	  }

	print '<table class="noborder" width="100%">';
	
	print '<tr class="liste_titre"><td>'.$langs->trans("Firstname").' '.$langs->trans("Lastname").'</td>';
	print '<td>'.$langs->trans("Poste").'</td><td colspan="2">'.$langs->trans("Tel").'</td>';
	print '<td>'.$langs->trans("Fax").'</td><td>'.$langs->trans("EMail").'</td>';
	print "<td align=\"center\"><a href=\"".DOL_URL_ROOT.'/contact/fiche.php?socid='.$objsoc->id."&amp;action=create\">".$langs->trans("AddContact")."</a></td>";
	print '<td>&nbsp;</td>';
	print "</tr>";
	
	$sql = "SELECT p.idp, p.name, p.firstname, p.poste, p.phone, p.fax, p.email, p.note ";
	$sql .= " FROM ".MAIN_DB_PREFIX."socpeople as p";
	$sql .= " WHERE p.fk_soc = $objsoc->id";
	$sql .= " ORDER by p.datec";

	$result = $db->query($sql);
	$i = 0 ; $num = $db->num_rows(); $tag = True;

	while ($i < $num)
	  {
	    $obj = $db->fetch_object();
	    $var = !$var;
	    print "<tr $bc[$var]>";
	    
	    print '<td>';
	    print '<a href="'.DOL_URL_ROOT.'/contact/fiche.php?id='.$obj->idp.'">';
	    print img_object($langs->trans("Show"),"contact");
	    print '&nbsp;'.$obj->firstname.' '. $obj->name.'</a>&nbsp;';
	    
	    if ($obj->note)
	      {
		print "<br>".nl2br($obj->note);
	      }
	    print "</td>";
	    print "<td>$obj->poste&nbsp;</td>";
	    print '<td>';

	    /*
	     * Lien click to dial
	     */
	    
	    if (strlen($obj->phone) && $user->clicktodial_enabled == 1)
	      {
		print '<a href="'.DOL_URL_ROOT.'/comm/action/fiche.php?action=create&actionid=1&contactid='.$obj->idp.'&amp;socid='.$objsoc->id.'&amp;call='.$obj->phone.'">';
		print img_phone_out("Appel �mis") ;
	      }
	    print '</td><td>';
	    print '<a href="action/fiche.php?action=create&actionid=1&contactid='.$obj->idp.'&socid='.$objsoc->id.'">'.dolibarr_print_phone($obj->phone).'</a>&nbsp;</td>';
	    print '<td><a href="action/fiche.php?action=create&actionid=2&contactid='.$obj->idp.'&socid='.$objsoc->id.'">'.dolibarr_print_phone($obj->fax).'</a>&nbsp;</td>';
	    print '<td><a href="action/fiche.php?action=create&actionid=4&contactid='.$obj->idp.'&socid='.$objsoc->id.'">'.$obj->email.'</a>&nbsp;</td>';
	    
	    print '<td align="center">';
	    print "<a href=\"../contact/fiche.php?action=edit&amp;id=$obj->idp\">";
	    print img_edit();
	    print '</a></td>';
	    
	    print '<td align="center"><a href="action/fiche.php?action=create&actionid=5&contactid='.$obj->idp.'&socid='.$objsoc->id.'">';
	    print img_object($langs->trans("Rendez-Vous"),"action");
	    print '</a></td>';
	    
	    print "</tr>\n";
	    $i++;
	    $tag = !$tag;
	  }
	print "</table>";
	
	print "<br>";
	
	/*
	 *      Listes des actions a faire
	 *
	 */
	print '<table width="100%" class="noborder">';
	print '<tr class="liste_titre"><td><a href="action/index.php?socid='.$objsoc->id.'">'.$langs->trans("ActionsToDo").'</a></td><td align="right"> <a href="action/fiche.php?action=create&socid='.$objsoc->id.'&afaire=1">'.$langs->trans("AddActionToDo").'</a></td></tr>';
	print '<tr>';
	print '<td colspan="2" valign="top">';
	
	$sql = "SELECT a.id, ".$db->pdate("a.datea")." as da, c.libelle, u.code, a.propalrowid, a.fk_user_author, fk_contact, u.rowid ";
	$sql .= " FROM ".MAIN_DB_PREFIX."actioncomm as a, ".MAIN_DB_PREFIX."c_actioncomm as c, ".MAIN_DB_PREFIX."user as u ";
	$sql .= " WHERE a.fk_soc = $objsoc->id ";
	$sql .= " AND u.rowid = a.fk_user_author";
	$sql .= " AND c.id=a.fk_action AND a.percent < 100";
	$sql .= " ORDER BY a.datea DESC, a.id DESC";

      if ( $db->query($sql) ) {
	print "<table width=\"100%\" class=\"noborder\">\n";

	$i = 0 ; $num = $db->num_rows();
	while ($i < $num) {
	  $var = !$var;

	  $obj = $db->fetch_object();
	  print "<tr $bc[$var]>";

	  if ($oldyear == strftime("%Y",$obj->da) ) 
		{
	    //print '<td align="center">|</td>';
			print "<td align=\"center\">" .strftime("%Y",$obj->da)."</td>\n"; 
	  } 
		else 
		{
	    print "<td align=\"center\">" .strftime("%Y",$obj->da)."</td>\n"; 
	    $oldyear = strftime("%Y",$obj->da);
	  }

	  if ($oldmonth == strftime("%Y%b",$obj->da) ) 
		{
	    //print '<td align="center">|</td>';
		print "<td align=\"center\">" .strftime("%b",$obj->da)."</td>\n"; 
	  } 
		else 
		{
	    print "<td align=\"center\">" .strftime("%b",$obj->da)."</td>\n"; 
	    $oldmonth = strftime("%Y%b",$obj->da);
	  }
	  
	  print "<td>" .strftime("%d",$obj->da)."</td>\n"; 
	  print "<td>" .strftime("%H:%M",$obj->da)."</td>\n";

	  print '<td width="10%">&nbsp;</td>';

	  if ($obj->propalrowid)
	    {
	      print '<td width="40%"><a href="propal.php?propalid='.$obj->propalrowid.'">'.$obj->libelle.'</a></td>';
	    }
	  else
	    {
	      print '<td width="40%"><a href="action/fiche.php?id='.$obj->id.'">'.$obj->libelle.'</a></td>';
	    }
	  /*
	   * Contact pour cette action
	   *
	   */
	  if ($obj->fk_contact) {
	    $contact = new Contact($db);
	    $contact->fetch($obj->fk_contact);
	    print '<td width="40%"><a href="'.DOL_URL_ROOT.'/contact/fiche.php?id='.$contact->id.'">'.$contact->fullname.'</a></td>';
	  } else {
	    print '<td width="40%">&nbsp;</td>';
	  }

	  /*
	   */
	  print '<td width="20%"><a href="../user/fiche.php?id='.$obj->fk_user_author.'">'.$obj->code.'</a></td>';
	  print "</tr>\n";
	  $i++;
	}
	print "</table>";

	$db->free();
      } else {
        dolibarr_print_error($db);
      }
      print "</td></tr></table>";

      /*
       *
       *      Listes des actions effectuees
       *
       */
      print '<table class="noborder" width="100%">';
      print '<tr class="liste_titre"><td><a href="action/index.php?socid='.$objsoc->id.'">'.$langs->trans("ActionsDone").'</a></td></tr>';
      print '<tr>';
      print '<td valign="top">';

      $sql = "SELECT a.id, ".$db->pdate("a.datea")." as da, c.libelle, u.code, a.propalrowid, a.fk_user_author, fk_contact, u.rowid ";
      $sql .= " FROM ".MAIN_DB_PREFIX."actioncomm as a, ".MAIN_DB_PREFIX."c_actioncomm as c, ".MAIN_DB_PREFIX."user as u ";
      $sql .= " WHERE a.fk_soc = $objsoc->id ";
      $sql .= " AND u.rowid = a.fk_user_author";
      $sql .= " AND c.id=a.fk_action AND a.percent = 100";
      $sql .= " ORDER BY a.datea DESC, a.id DESC";

      if ( $db->query($sql) )
	{
	  print '<table width="100%" class="noborder">';
	  
	  $i = 0 ; 
	  $num = $db->num_rows();
	  $oldyear='';
	  $oldmonth='';
	  while ($i < $num)
	    {
	      $var = !$var;
	      
	      $obj = $db->fetch_object();
	      print "<tr $bc[$var]>";
	      
	      if ($oldyear == strftime("%Y",$obj->da) )
		{
		  print '<td align="center">|</td>';
		}
	      else
		{
		  print "<TD align=\"center\">" .strftime("%Y",$obj->da)."</td>\n"; 
		  $oldyear = strftime("%Y",$obj->da);
		}
	      
	      if ($oldmonth == strftime("%Y%b",$obj->da) )
		{
		  print '<td align="center">|</td>';
		}
	      else
		{
		  print "<TD align=\"center\">" .strftime("%b",$obj->da)."</td>\n"; 
		  $oldmonth = strftime("%Y%b",$obj->da);
		}
	  
	      print "<TD>" .strftime("%d",$obj->da)."</td>\n"; 
	      print "<TD>" .strftime("%H:%M",$obj->da)."</td>\n";
	      
	      print '<td width="10%">&nbsp;</td>';
	      
	      if ($obj->propalrowid)
		{
		  print '<td width="40%"><a href="'.DOL_URL_ROOT.'/comm/propal.php?propalid='.$obj->propalrowid.'">'.$obj->libelle.'</a></td>';
		}
	      else
		{
		  print '<td width="40%"><a href="'.DOL_URL_ROOT.'/comm/action/fiche.php?id='.$obj->id.'">'.$obj->libelle.'</a></td>';
		}
	      /*
	       * Contact pour cette action
	       *
	       */
	      if ($obj->fk_contact)
		{
		  $contact = new Contact($db);
		  $contact->fetch($obj->fk_contact);
		  print '<td width="40%"><a href="'.DOL_URL_ROOT.'/contact/fiche.php?id='.$contact->id.'">'.$contact->fullname.'</a></td>';
		}
	      else
		{
		  print '<td width="40%">&nbsp;</td>';
		}
	      /*
	       */
	      print '<td width="20%"><a href="../user/fiche.php?id='.$obj->fk_user_author.'">'.$obj->code.'</a></td>';
	      print "</tr>\n";
	      $i++;
	    }
	  print "</table>";
	  
	  $db->free();
	}
      else
	{
        dolibarr_print_error($db);
	}
      print "</td></tr></table>";
      /*
       *
       * Notes sur la societe
       *
       */
      if ($objsoc->note)
	{
	  print '<table class="border" width="100%" bgcolor="#e0e0e0">';
	  print "<tr><td>".nl2br($objsoc->note)."</td></tr>";
	  print "</table>";
	}
      /*
       *
       *
       *
       */

    }
  } else {
    dolibarr_print_error($db);
  }

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
