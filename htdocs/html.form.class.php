<?PHP
/* Copyright (c) 2002-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Benoit Mortier       <benoit.mortier@opensides.be>
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

class Form
{
  var $db;
  var $errorstr;


  function Form($DB)
  {
    
    $this->db = $DB;
    
    return 1;
  }
  
  /*
   * \brief     Retourne la liste d�roulante des d�partements/province/cantons 
   *            avec un affichage avec rupture sur le pays
   * \remarks   La cle de la liste est le code (il peut y avoir plusieurs entr�e pour
   *            un code donn�e mais dans ce cas, le champ pays et lang diff�re).
   *            Ainsi les liens avec les d�partements se font sur un d�partement
   *            independemment de nom som.
   */
  function select_departement($selected='',$htmlname='departement_id')
  {
    // On recherche les d�partements/cantons/province active d'une region et pays actif
    $sql = "SELECT d.rowid, d.code_departement as code , d.nom, d.active, p.libelle as libelle_pays, p.code as code_pays FROM ";
    $sql .= MAIN_DB_PREFIX ."c_departements as d, ".MAIN_DB_PREFIX."c_regions as r,".MAIN_DB_PREFIX."c_pays as p";
    $sql .= " WHERE d.fk_region=r.code_region and r.fk_pays=p.rowid";
    $sql .= " AND d.active = 1 AND r.active = 1 AND p.active = 1 ";
    $sql .= "ORDER BY code_pays, code ASC";
    
    if ($this->db->query($sql))
      {
    print '<select name="'.$htmlname.'">';
	$num = $this->db->num_rows();
	$i = 0;
	if ($num)
	  {
	    $pays='';
	    while ($i < $num)
	      {
		$obj = $this->db->fetch_object( $i);
		if ($obj->code == 0) {
		  print '<option value="0">&nbsp;</option>';
		}
		else {
		  if ($pays == '' || $pays != $obj->libelle_pays) {
		    // Affiche la rupture
		    print '<option value="-1">----- '.$obj->libelle_pays." -----</option>\n";
		    $pays=$obj->libelle_pays;   
		  }
		  
		  if ($selected > 0 && $selected == $obj->rowid)
		    {
		      print '<option value="'.$obj->code.'" selected>['.$obj->code.'] '.$obj->nom.'</option>';
		    }
		  else
		    {
		      print '<option value="'.$obj->code.'">['.$obj->code.'] '.$obj->nom.'</option>';
		    }
		}
		$i++;
	      }
	  }
    print '</select>';
      }
    else {
      dolibarr_print_error($this->db);
    }
  }
  
  /*
   * \brief     Retourne la liste d�roulante des regions actives dont le pays est actif
   * \remarks   La cle de la liste est le code (il peut y avoir plusieurs entr�e pour
   *            un code donn�e mais dans ce cas, le champ pays et lang diff�re).
   *            Ainsi les liens avec les regions se font sur une region independemment
   *            de nom som.
   */
  function select_region($selected='',$htmlname='region_id')
  {
    $sql = "SELECT r.rowid, r.code_region as code, r.nom as libelle, r.active, p.libelle as libelle_pays FROM ".MAIN_DB_PREFIX."c_regions as r, ".MAIN_DB_PREFIX."c_pays as p";
    $sql .= " WHERE r.fk_pays=p.rowid AND r.active = 1 and p.active = 1 ORDER BY libelle_pays, libelle ASC";

    if ($this->db->query($sql))
      {
    print '<select name="'.$htmlname.'">';
	$num = $this->db->num_rows();
	$i = 0;
	if ($num)
	  {
	    $pays='';
	    while ($i < $num)
	      {
		$obj = $this->db->fetch_object( $i);
		if ($obj->code == 0) {
		  print '<option value="0">&nbsp;</option>';
		}
		else {
		  if ($pays == '' || $pays != $obj->libelle_pays) {
		    // Affiche la rupture
		    print '<option value="-1">----- '.$obj->libelle_pays." -----</option>\n";
		    $pays=$obj->libelle_pays;   
		  }
		  
		  if ($selected > 0 && $selected == $obj->code)
		    {
		      print '<option value="'.$obj->code.'" selected>'.$obj->libelle.'</option>';
		    }
		  else
		    {
		      print '<option value="'.$obj->code.'">'.$obj->libelle.'</option>';
		    }
		}
		$i++;
	      }
	  }
    print '</select>';
      }
    else {
      dolibarr_print_error($this->db);
    }
  }

  /*
   * \brief     Retourne la liste d�roulante des pays actifs
   *
   */
  function select_pays($selected='',$htmlname='pays_id')
  {
    $sql = "SELECT rowid, libelle, active FROM ".MAIN_DB_PREFIX."c_pays";
    $sql .= " WHERE active = 1 ORDER BY libelle ASC";

    if ($this->db->query($sql))
      {
    print '<select name="'.$htmlname.'">';
	$num = $this->db->num_rows();
	$i = 0;
	if ($num)
	  {
	    while ($i < $num)
	      {
		$obj = $this->db->fetch_object( $i);
		if ($selected == $obj->rowid)
		  {
		    print '<option value="'.$obj->rowid.'" selected>'.$obj->libelle.'</option>';
		  }
		else
		  {
		    print '<option value="'.$obj->rowid.'">'.$obj->libelle.'</option>';
		  }
		$i++;
	      }
	  }
    print '</select>';
      }
  }


  /*
   * \brief     Retourne la liste d�roulante des langues disponibles
   * \param     
   */
  function select_lang($selected='',$htmlname='lang_id')
  {
    global $langs;
    
    $langs_available=$langs->get_available_languages();
    
    print '<select name="'.$htmlname.'">';
	$num = count($langs_available);
	$i = 0;
	if ($num)
	  {
	    while ($i < $num)
	      {
		if ($selected == $langs_available[$i])
		  {
		    print '<option value="'.$langs_available[$i].'" selected>'.$langs_available[$i].'</option>';
		  }
		else
		  {
		    print '<option value="'.$langs_available[$i].'">'.$langs_available[$i].'</option>';
		  }
		$i++;
	      }
	  }
    print '</select>';
  }


  /*
   * Retourne la liste d�roulante des soci�t�s
   *
   */
  function select_societes($selected='',$htmlname='soc_id')
  {
    // On recherche les societes
    $sql = "SELECT s.idp, s.nom FROM ";
    $sql .= MAIN_DB_PREFIX ."societe as s ";
    $sql .= "ORDER BY nom ASC";

    if ($this->db->query($sql))
      {
    print '<select name="'.$htmlname.'">';
	$num = $this->db->num_rows();
	$i = 0;
	if ($num)
	  {
	    while ($i < $num)
	      {
		$obj = $this->db->fetch_object( $i);
		  if ($selected > 0 && $selected == $obj->idp)
		    {
		      print '<option value="'.$obj->idp.'" selected>'.$obj->nom.'</option>';
		    }
		  else
		    {
		      print '<option value="'.$obj->idp.'">'.$obj->nom.'</option>';
		    }
		$i++;
	      }
	  }
    print '</select>';
      }
    else {
      dolibarr_print_error($this->db);
    }
  }
  
  /*
   * Retourne la liste d�roulante des contacts d'une soci�t� donn�e
   *
   */
  function select_contacts($socid,$selected='',$htmlname='contactid')
  {
    // On recherche les societes
    $sql = "SELECT s.idp, s.name, s.firstname FROM ";
    $sql .= MAIN_DB_PREFIX ."socpeople as s";
    $sql .= " WHERE fk_soc=".$socid;
    $sql .= " ORDER BY s.name ASC";

    if ($this->db->query($sql))
      {
    print '<select name="'.$htmlname.'">';
	$num = $this->db->num_rows();
	$i = 0;
	if ($num)
	  {
	    while ($i < $num)
	      {
  		  $obj = $this->db->fetch_object($i);

		  if ($selected && $selected == $obj->idp)
		    {
		      print '<option value="'.$obj->idp.'" selected>'.$obj->name.' '.$obj->firstname.'</option>';
		    }
		  else
		    {
		      print '<option value="'.$obj->idp.'">'.$obj->name.' '.$obj->firstname.'</option>';
		    }
		$i++;
	      }
	  }
    print '</select>';
      }
    else {
      dolibarr_print_error($this->db);
    }
  }

  
  /*
   * Retourne le nom d'un pays
   *
   */
  function pays_name($id)
  {
    $sql = "SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."c_pays";
    $sql .= " WHERE rowid=$id";

    if ($this->db->query($sql))
      {
	$num = $this->db->num_rows();

	if ($num)
	  {
	    $obj = $this->db->fetch_object(0);
	    return $obj->libelle;
	  }
	else
	  {
	    return "Non d�finit";
	  }

      }
  }



  /*
   * Retourne la liste d�roulante des civilite actives
   *
   */

  function select_civilite($selected='')
  {
    global $conf;
    
    $sql = "SELECT rowid, code, civilite, active FROM ".MAIN_DB_PREFIX."c_civilite";
    $sql .= " WHERE active = 1";
    $sql .= " AND lang='".$conf->langage."'";

    if ($this->db->query($sql))
      {
    print '<select name="civilite_id">';
	$num = $this->db->num_rows();
	$i = 0;
	if ($num)
	  {
	    while ($i < $num)
	      {
		$obj = $this->db->fetch_object( $i);
		if ($selected == $obj->rowid)
		  {
		    print '<option value="'.$obj->code.'" selected>'.$obj->civilite.'</option>';
		  }
		else
		  {
		    print '<option value="'.$obj->code.'">'.$obj->civilite.'</option>';
		  }
		$i++;
	      }
	  }
    print '</select>';
      }
    else {
          dolibarr_print_error($this->db);
      }

  }

  /*
   * Retourne la liste d�roulante des formes juridiques
   * avec un affichage avec rupture sur le pays
   *
   */
    function select_forme_juridique($selected='')
    {
      // On recherche les formes juridiques actives des pays actifs
      $sql = "SELECT f.rowid, f.code as code , f.libelle as nom, f.active, p.libelle as libelle_pays, p.code as code_pays FROM llx_c_forme_juridique as f, llx_c_pays as p";
      $sql .= " WHERE f.fk_pays=p.rowid";
      $sql .= " AND f.active = 1 AND p.active = 1 ORDER BY code_pays, code ASC";
      
      if ($this->db->query($sql))
        {
      print '<select name="forme_juridique_code">';
	  $num = $this->db->num_rows();
	  $i = 0;
	  if ($num)
            {
	      $pays='';
	      while ($i < $num)
                {
		  $obj = $this->db->fetch_object( $i);
		  if ($obj->code == 0) {
		    print '<option value="0">&nbsp;</option>';
		  }
		  else {
		    if ($pays == '' || $pays != $obj->libelle_pays) {
		      // Affiche la rupture
		      print '<option value="0">----- '.$obj->libelle_pays." -----</option>\n";
		      $pays=$obj->libelle_pays;   
		    }
		    
		    if ($selected > 0 && $selected == $obj->code)
		      {
			print '<option value="'.$obj->code.'" selected>['.$obj->code.'] '.$obj->nom.'</option>';
		      }
		    else
		      {
			print '<option value="'.$obj->code.'">['.$obj->code.'] '.$obj->nom.'</option>';
		      }
		  }
		  $i++;
                }
            }
      print '</select>';
      }
      else {
          dolibarr_print_error($this->db);
      }
    }
  
  /*
   *
   *
   *
   */
  function form_confirm($page, $title, $question, $action)
  {
    global $langs;
    
    print '<form method="post" action="'.$page.'">';
    print '<input type="hidden" name="action" value="'.$action.'">';
    print '<table cellspacing="0" class="border" width="100%" cellpadding="3">';
    print '<tr><td colspan="3">'.$title.'</td></tr>';
    
    print '<tr><td class="valid">'.$question.'</td><td class="valid">';
    
    $this->selectyesno("confirm","no");
    
    print "</td>\n";
    print '<td class="valid" align="center"><input type="submit" value="'.$langs->trans("Confirm").'"</td></tr>';
    print '</table>';
    print "</form>\n";  
  }
  /*
   *
   *
   */
  function select_tva($name='', $defaulttx = '')
  {
    if (! strlen(trim($name)))
    {
      $name = "tauxtva";
    }

    $file = DOL_DOCUMENT_ROOT . "/conf/tva.local.php";
    if (is_readable($file))
      {
	include $file;
      }
    else
      {
	$txtva[0] = '19.6';
	$txtva[1] = '5.5';
	$txtva[2] = '0';
      }

    if ($defaulttx == '')
      {
	$defaulttx = $txtva[0];
      }

    $taille = sizeof($txtva);

    print '<select name="'.$name.'">';

    for ($i = 0 ; $i < $taille ; $i++)
      {
	print '<option value="'.$txtva[$i].'"';
	if ($txtva[$i] == $defaulttx)
	  {
	    print ' SELECTED>'.$txtva[$i].' %</option>';
	  }
	else
	  {
	    print '>'.$txtva[$i].' %</option>';
	  }
      }
    print '</select>';
  }

  /*
   * Affiche zone de selection de date
   * Liste deroulante pour les jours, mois, annee et eventuellement heurs et minutes
   * Les champs sont pr�s�lectionn�es avec:
   * - La date set_time (timestamps ou date au format YYYY-MM-DD ou YYYY-MM-DD HH:MM)
   * - La date du jour si set_time vaut ''
   * - Aucune date (champs vides) si set_time vaut -1
   */
  function select_date($set_time='', $prefix='re', $h = 0, $m = 0, $empty=0)
  {
    if (! $set_time && ! $empty)
      {
	    $set_time = time();
      }

    $strmonth[1] = "Janvier";
    $strmonth[2] = "F&eacute;vrier";
    $strmonth[3] = "Mars";
    $strmonth[4] = "Avril";
    $strmonth[5] = "Mai";
    $strmonth[6] = "Juin";
    $strmonth[7] = "Juillet";
    $strmonth[8] = "Ao&ucirc;t";
    $strmonth[9] = "Septembre";
    $strmonth[10] = "Octobre";
    $strmonth[11] = "Novembre";
    $strmonth[12] = "D&eacute;cembre";
    
    # Analyse de la date de pr�selection
    if (eregi('^([0-9]+)\-([0-9]+)\-([0-9]+)\s?([0-9]+)?:?([0-9]+)?',$set_time,$reg)) {
        // Date au format 'YYYY-MM-DD' ou 'YYYY-MM-DD HH:MM:SS'
        $syear = $reg[1];
        $smonth = $reg[2];
        $sday = $reg[3];
        $shour = $reg[4];
        $smin = $reg[5];
    }
    else {
        // Date est un timestamps
        $syear = date("Y", $set_time);
        $smonth = date("n", $set_time);
        $sday = date("d", $set_time);
        $shour = date("H", $set_time);
        $smin = date("i", $set_time);
    }
    
    print '<select name="'.$prefix.'day">';    

    if ($empty || $set_time == -1)
      {
    	$sday = 0;
    	$smonth = 0;
    	$syear = 0;
    	$shour = 0;
    	$smin = 0;

    	print '<option value="0" selected>';
      }
    
    for ($day = 1 ; $day <= 31; $day++) 
      {
	if ($day == $sday)
	  {
	    print "<option value=\"$day\" selected>$day";
	  }
	else 
	  {
	    print "<option value=\"$day\">$day";
	  }
      }
    
    print "</select>";
    
    
    print '<select name="'.$prefix.'month">';    
    if ($empty || $set_time == -1)
      {
	print '<option value="0" selected>';
      }


    for ($month = 1 ; $month <= 12 ; $month++)
      {
	if ($month == $smonth)
	  {
	    print "<option value=\"$month\" selected>" . $strmonth[$month];
	  }
	else
	  {
	    print "<option value=\"$month\">" . $strmonth[$month];
	  }
      }
    print "</select>";

    if ($empty || $set_time == -1)
      {
	print '<input type="text" size="5" maxlength="4" name="'.$prefix.'year">';
      }
    else
      {
    
	print '<select name="'.$prefix.'year">';
	
	for ($year = $syear - 3; $year < $syear + 5 ; $year++)
	  {
	    if ($year == $syear)
	      {
		print "<option value=\"$year\" selected>$year";
	      }
	    else
	      {
		print "<option value=\"$year\">$year";
	      }
	  }
	print "</select>\n";
      }

    if ($h)
      {
	print '<select name="'.$prefix.'hour">';
    
	for ($hour = 0; $hour < 24 ; $hour++)
	  {
	    if (strlen($hour) < 2)
	      {
		$hour = "0" . $hour;
	      }
	    if ($hour == $shour)
	      {
		print "<option value=\"$hour\" selected>$hour";
	      }
	    else
	      {
		print "<option value=\"$hour\">$hour";
	      }
	  }
	print "</select>H\n";

	if ($m)
	  {
	    print '<select name="'.$prefix.'min">';
	    
	    for ($min = 0; $min < 60 ; $min++)
	      {
		if (strlen($min) < 2)
		  {
		    $min = "0" . $min;
		  }
		if ($min == $smin)
		  {
		    print "<option value=\"$min\" selected>$min";
		  }
		else
		  {
		    print "<option value=\"$min\">$min";
		  }
	      }
	    print "</select>M\n";
	  }
	
      }
  }
  /*
   *
   *
   */
  function select($name, $sql, $id='')
    {

      $result = $this->db->query($sql);
      if ($result)
	{

	  print '<select name="'.$name.'">';

	  $num = $this->db->num_rows();
	  $i = 0;
	  
	  if (strlen("$id"))
	    {	    	      
	      while ($i < $num)
		{
		  $row = $this->db->fetch_row($i);
		  print "<option value=\"$row[0]\" ";
		  if ($id == $row[0])
		    {
		      print "selected";
		    }
		  print ">$row[1]</option>\n";
		  $i++;
		}
	    }
	  else
	    {
	      while ($i < $num)
		{
		  $row = $this->db->fetch_row($i);
		  print "<option value=\"$row[0]\">$row[1]</option>\n";
		  $i++;
		}
	    }

	  print "</select>";
	}
      else 
	{
	  print $this->db->error();
	}

    }
    
    /*!
    		\brief Affiche un select � partir d'un tableau
    		\param	nom de la zone select
    		\param	tableau de key+valeur
    		\param	key pr�s�lectionn�e
    		\param	1 si il faut un valeur "-" dans la liste, 0 sinon
    		\param	1 pour afficher la key dans la valeur "[key] value"
    */
  function select_array($name, $array, $id='', $empty=0, $key_libelle=0)
    {
      print '<select name="'.$name.'">';
      
      $i = 0;

      if (strlen($id))
	{
	  if ($empty == 1)
	    {
	      $array[0] = "-";
	    }
	  reset($array);

	  while (list($key, $value) = each ($array))
	    {
	      print "<option value=\"$key\" ";
	      if ($id == $key)
		{
		  print "selected";
		}
	      if ($key_libelle)
		{
		  print ">[$key] $value</option>\n";  
		}
	      else
		{
		  print ">$value</option>\n";
		}
	    }
	}
      else
	{
	  while (list($key, $value) = each ($array) )
	    {
	      print "<option value=\"$key\" ";
	      if ($key_libelle)
		{
		  print ">[$key] $value</option>\n";  
		}
	      else
		{
		  print ">$value</option>\n";
		}
	    }
	
	}

      print "</select>";
    
    }
  /*
   * Renvoie la cha�ne de caract�re d�crivant l'erreur
   *
   *
   */
  function error()
    {
      return $this->errorstr;
    }
  /*
   *
   * Yes/No
   *
   */
  function selectyesno($name,$value='')
  {
    global $langs;
    
    print '<select name="'.$name.'">';

    if ($value == 'yes') 
      {
	print '<option value="yes" selected>'.$langs->trans("yes").'</option>';
	print '<option value="no">'.$langs->trans("no").'</option>';
      }
    else
      {
	print '<option value="yes">'.$langs->trans("yes").'</option>';
	print '<option value="no" selected>'.$langs->trans("no").'</option>';
      }
    print '</select>';
  }
  /*
   *
   * Yes/No
   *
   */
  function selectyesnonum($name,$value='')
  {
    global $langs;
    
    print '<select name="'.$name.'">';

    if ($value == 1) 
      {
	print '<option value="1" selected>'.$langs->trans("yes").'</option>';
	print '<option value="0">'.$langs->trans("no").'</option>';
      }
    else
      {
	print '<option value="1">'.$langs->trans("yes").'</option>';
	print '<option value="0" selected>'.$langs->trans("no").'</option>';
      }
    print '</select>';
  }
  /*
   *
   * Checkbox
   *
   */
  function checkbox($name,$checked=0,$value=1)
    {
      if ($checked==1){
	print "<input type=\"checkbox\" name=\"$name\" value=\"$value\" checked />\n";
      }else{
	print "<input type=\"checkbox\" name=\"$name\" value=\"$value\" />\n";
      }
    }
}

?>
