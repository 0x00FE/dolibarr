<?PHP
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      �ric Seigne          <erics@rycks.com>
 * Copyright (C) 2004      Laurent Destailleur  <eldy@users.sourceforge.net>
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
require("./pre.inc.php");

require("../../contact.class.php");
require("../../lib/webcal.class.php");
require("../../cactioncomm.class.php");
require("../../actioncomm.class.php");

/*
 * S�curit� acc�s client
 */
if ($user->societe_id > 0) 
{
  $action = '';
  $socid = $user->societe_id;
}

llxHeader();

/*
 *
 *
 *
 */
if ($action=='delete_action')
{
  $actioncomm = new ActionComm($db);
  $actioncomm->delete($actionid);
}
/*
 *
 */
if ($action=='add_action')
{
  $contact = new Contact($db);
  $contact->fetch($contactid);

  $actioncomm = new ActionComm($db);

  if ($actionid == 5)
    {
      $actioncomm->date = $db->idate(mktime($heurehour,$heuremin,0,$remonth,$reday,$reyear));
    }
  else
    {
    $actioncomm->date = $date;
    }
  $actioncomm->type = $actionid;
  $actioncomm->contact = $contactid;
  $actioncomm->societe = $socid;
  $actioncomm->note = $note;
  $actioncomm->add($user);

  $societe = new Societe($db);
  $societe->fetch($socid);

  $todo = new TodoComm($db);
  $todo->date = mktime(12,0,0,$remonth, $reday, $reyear);
  $todo->libelle = $todo_label;
  $todo->societe = $societe->id;
  $todo->contact = $contactid;

  $todo->note = $todo_note;

  $todo->add($user);

  $webcal = new Webcal();

  $webcal->heure = $heurehour . $heuremin . '00';
  $webcal->duree = ($dureehour * 60) + $dureemin;

  if ($actionid == 5) {
    $libelle = "Rendez-vous avec ".$contact->fullname;
    $libelle .= "\n" . $todo->libelle;
  } else {
    $libelle = $todo->libelle;
  }


  $webcal->add($user, $todo->date, $societe->nom, $libelle);
  
}

/*
 *
 *  Liste
 *
 */

if ($page == -1) { $page = 0 ; }
$limit = $conf->liste_limit;
$offset = $limit * $page ;
if ($sortorder == "")
{
  $sortorder="DESC";
}
if ($sortfield == "")
{
  $sortfield="a.datea";
}

$sql = "SELECT s.nom as societe, s.idp as socidp, s.client, a.id,".$db->pdate("a.datea")." as da, a.datea, c.libelle, u.code, a.fk_contact, a.note, a.percent as percent";
$sql .= " FROM ".MAIN_DB_PREFIX."actioncomm as a, ".MAIN_DB_PREFIX."c_actioncomm as c, ".MAIN_DB_PREFIX."societe as s, ".MAIN_DB_PREFIX."user as u";
$sql .= " WHERE a.fk_soc = s.idp AND c.id=a.fk_action AND a.fk_user_author = u.rowid";

if ($type)
{
  $sql .= " AND c.id = $type";
}

if ($time == "today")
{
  $sql .= " AND date_format(a.datea, '%d%m%Y') = ".strftime("%d%m%Y",time());
}

if ($socid) 
{
  $sql .= " AND s.idp = $socid";
}

$sql .= " ORDER BY a.datea DESC";
$sql .= $db->plimit( $limit + 1, $offset);
  
  
if ( $db->query($sql) )
{
  $num = $db->num_rows();
  if ($socid) 
    {
      $societe = new Societe($db);
      $societe->fetch($socid);
      
      print_barre_liste("Liste des actions commerciales r�alis�es ou � faire sur " . $societe->nom, $page, "index.php",'',$sortfield,$sortorder,'',$num);
    }
  else
    {      
      print_barre_liste("Liste des actions commerciales r�alis�es ou � faire", $page, "index.php",'',$sortfield,$sortorder,'',$num);
    }
  $i = 0;
  print "<table class=\"noborder\" width=\"100%\" cellspacing=\"0\" cellpadding=\"3\">";
  print '<tr class="liste_titre">';
  print '<td colspan="4">'.$langs->trans("Date").'</td>';
  print '<td>Avancement</td>';
  print '<td>Action</td>';
  print '<td>Soci�t�</td>';
  print '<td>Contact</Td>';
  print "<td>".$langs->trans("Comments")."</td><td>".$langs->trans("Author")."</td>";
  print "</tr>\n";
  $var=True;
  while ($i < min($num,$limit))
    {
      $obj = $db->fetch_object( $i);
      
      $var=!$var;
    
      print "<tr $bc[$var]>";
    
      if ($oldyear == strftime("%Y",$obj->da) )
	{
	  print '<td>&nbsp;</td>';
	}
      else
	{
	  print "<td width=\"30\">" .strftime("%Y",$obj->da)."</td>\n"; 
	  $oldyear = strftime("%Y",$obj->da);
	}
      
      if ($oldmonth == strftime("%Y%b",$obj->da) )
	{
	  print '<td width=\"20\">&nbsp;</td>';
	}
	else
	  {
	    print "<td width=\"20\">" .strftime("%b",$obj->da)."</td>\n"; 
	    $oldmonth = strftime("%Y%b",$obj->da);
	  }
	
	print "<td width=\"20\">" .strftime("%d",$obj->da)."</td>\n"; 
	print "<td width=\"30\">" .strftime("%H:%M",$obj->da)."</td>\n";
    if ($obj->percent < 100) {
    	print "<td align=\"center\">".$obj->percent."%</td>";
	}
	else {
		print "<td align=\"center\"><b>r�alis�</b></td>";
	}
	print '<td><a href="fiche.php?id='.$obj->id.'">'.$obj->libelle.'</a></td>';
	
	print '<td>';

	if ($obj->client == 1)
	  {
	    print '&nbsp;<a href="'.DOL_URL_ROOT.'/comm/fiche.php?socid='.$obj->socidp.'">'.$obj->societe.'</A></TD>';
	  }
	elseif ($obj->client == 2)
	  {
	    print '&nbsp;<a href="'.DOL_URL_ROOT.'/comm/prospect/fiche.php?id='.$obj->socidp.'">'.$obj->societe.'</A></TD>';
	  }
	else
	  {
	    print '&nbsp;<a href="'.DOL_URL_ROOT.'/soc.php?socid='.$obj->socidp.'">'.$obj->societe.'</A></TD>';
	  }
	/*
	 * Contact
	 */
	print '<td>';
	if ($obj->fk_contact)
	  {
	    $cont = new Contact($db);
	    $cont->fetch($obj->fk_contact);
	    print '<a href="'.DOL_URL_ROOT.'/comm/people.php?socid='.$obj->socidp.'&contactid='.$cont->id.'">'.$cont->fullname.'</a>';
	  }
	else
	  {
	    print "&nbsp;";
	  }
	print '</td>';
	/*
	 *
	 */
	print '<td>'.substr($obj->note, 0, 20).' ...</td>';
	print "<td>$obj->code</td>\n";
	
	print "</tr>\n";
	$i++;
      }
      print "</table>";
      $db->free();
    }
  else
    {
      print $db->error() . ' ' . $sql ;
    }


$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
