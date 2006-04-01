<?php
/* Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       htdocs/exports/export.php
        \ingroup    export
        \brief      Page d'edition d'un export
        \version    $Revision$
*/
 
require_once("./pre.inc.php");
require_once(DOL_DOCUMENT_ROOT."/exports/export.class.php");
require_once(DOL_DOCUMENT_ROOT.'/includes/modules/export/modules_export.php');


$langs->load("exports");

$user->getrights();

if (! $user->societe_id == 0)
  accessforbidden();

$entitytoicon=array(
	'invoice'=>'bill','invoice_line'=>'bill',
	'member'=>'user', 'member_type'=>'group');
$entitytolang=array(
	'user'=>'User',
	'company'=>'Company','contact'=>'Contact',
	'invoice'=>'Bill','invoice_line'=>'InvoiceLine',
	'member'=>'Member','member_type'=>'MemberType');

$array_selected=isset($_SESSION["export_selected_fields"])?$_SESSION["export_selected_fields"]:array();
$datatoexport=isset($_GET["datatoexport"])?$_GET["datatoexport"]:'';
$action=isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"])?$_POST["action"]:'');
$step=isset($_GET["step"])?$_GET["step"]:'1';

$objexport=new Export($db);
$objexport->load_arrays($user,$datatoexport);

$objmodelexport=new ModeleExports();


/*
 * Actions
 */
 
if ($action=='selectfield')
{ 
    $array_selected[$_GET["field"]]=sizeof($array_selected)+1;
    //print_r($array_selected);
    $_SESSION["export_selected_fields"]=$array_selected;
}
if ($action=='unselectfield')
{ 
    unset($array_selected[$_GET["field"]]);
    // Renumerote champs de array_selected (de 1 � nb_elements)
    asort($array_selected);
    $i=0;
    $array_selected_save=$array_selected;
    foreach($array_selected as $code=>$value)
    {
        $i++;
        $array_selected[$code]=$i;
        //print "x $code x $i y<br>";
    }
    $_SESSION["export_selected_fields"]=$array_selected;
}

if ($action=='downfield' || $action=='upfield')
{ 
    $pos=$array_selected[$_GET["field"]];
    if ($action=='downfield') $newpos=$pos+1;
    if ($action=='upfield') $newpos=$pos-1;
    // Recherche code avec qui switch�
    $newcode="";
    foreach($array_selected as $code=>$value)
    {
        if ($value == $newpos)
        {
            $newcode=$code;
            break;
        }
    }
    //print("Switch pos=$pos (code=".$_GET["field"].") and newpos=$newpos (code=$newcode)");
    if ($newcode)   // Si newcode trouv� (prtoection contre resoumission de page
    {
        $array_selected[$_GET["field"]]=$newpos;
        $array_selected[$newcode]=$pos;
        $_SESSION["export_selected_fields"]=$array_selected;
    }
}

if ($step == 1 || $action == 'cleanselect')
{
    $_SESSION["export_selected_fields"]=array();
    $array_selected=array();
}

if ($action == 'builddoc')
{
    // Gen�re le fichier
	$result=$objexport->build_file($user, $_POST["model"], $datatoexport, $array_selected);
	if ($result < 0)
	{
	    $mesg='<div class="error">'.$objexport->error.'</div>';
	}
	else
	{
//	    $mesg='<div class="ok">'.$langs->trans("FileSuccessfulyBuilt").'</div>';
    }
}


/*
 * Affichage Pages des Etapes
 */

if ($step == 1 || ! $datatoexport)
{
    llxHeader('',$langs->trans("NewExport"));

    /*
     * Affichage onglets
     */
    $h = 0;

    $head[$h][0] = DOL_URL_ROOT.'/exports/export.php?step=1';
    $head[$h][1] = $langs->trans("Step")." 1";
    $hselected=$h;
    $h++;

    /*
    $head[$h][0] = '';
    $head[$h][1] = $langs->trans("Step")." 2";
    $h++;
    */
    
    dolibarr_fiche_head($head, $hselected, $langs->trans("NewExport"));


    print '<table class="notopnoleftnoright" width="100%">';

    print $langs->trans("SelectExportDataSet").'<br>';
    print '<br>';
    
    // Affiche les modules d'exports
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre">';
    print '<td width="25%">'.$langs->trans("Module").'</td>';
    print '<td>'.$langs->trans("ExportableDatas").'</td>';
    print '<td>&nbsp;</td>';
    print '</tr>';
    $val=true;
    if (sizeof($objexport->array_export_code))
    {
        foreach ($objexport->array_export_code as $key => $value)
        {
            $val=!$val;
            print '<tr '.$bc[$val].'><td>';
            print img_object($objexport->array_export_module[$key]->getName(),$objexport->array_export_module[$key]->picto).' ';
            print $objexport->array_export_module[$key]->getName();
            print '</td><td>';
            print $objexport->array_export_label[$key];
            print '</td><td align="right">';
            print '<a href="'.DOL_URL_ROOT.'/exports/export.php?step=2&amp;datatoexport='.$objexport->array_export_code[$key].'">'.img_picto($langs->trans("NewExport"),'filenew').'</a>';
            print '</td></tr>';
        }
    }
    else
    {
        print '<tr><td '.$bc[false].' colspan="2">'.$langs->trans("NoExportableData").'</td></tr>';
    }
    print '</table>';    

    print '</table>';
}

if ($step == 2 && $datatoexport)
{
    llxHeader('',$langs->trans("NewExport"));
    
    /*
     * Affichage onglets
     */
    $h = 0;

    $head[$h][0] = DOL_URL_ROOT.'/exports/export.php?step=1';
    $head[$h][1] = $langs->trans("Step")." 1";
    $h++;

    $head[$h][0] = DOL_URL_ROOT.'/exports/export.php?step=2&amp;datatoexport='.$datatoexport;
    $head[$h][1] = $langs->trans("Step")." 2";
    $hselected=$h;
    $h++;
    
    dolibarr_fiche_head($head, $hselected, $langs->trans("NewExport"));

    print '<table width="100%" class="border">';

    // Module
    print '<tr><td width="25%">'.$langs->trans("Module").'</td>';
    print '<td>';
    print img_object($objexport->array_export_module[0]->getName(),$objexport->array_export_module[0]->picto).' ';
    print $objexport->array_export_module[0]->getName();
    print '</td></tr>';

    // Lot de donn�es � exporter
    print '<tr><td width="25%">'.$langs->trans("DatasetToExport").'</td>';
    print '<td>'.$objexport->array_export_label[0].'</td></tr>';

    print '</table>';
    print '<br>';
    
    print $langs->trans("SelectExportFields").'<br>';
    
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Entities").'</td>';
    print '<td>'.$langs->trans("ExportableFields").'</td>';
    print '<td width="4%">&nbsp;</td>';
    print '<td width="48%">'.$langs->trans("ExportedFields").'</td>';
    print '</tr>';

    // Champs exportables
    $fieldsarray=$objexport->array_export_fields[0];

#    $this->array_export_module[0]=$module;
#    $this->array_export_code[0]=$module->export_code[$r];
#    $this->array_export_label[0]=$module->export_label[$r];
#    $this->array_export_sql[0]=$module->export_sql[$r];
#    $this->array_export_fields[0]=$module->export_fields_array[$r];
#    $this->array_export_entities[0]=$module->export_fields_entities[$r];
#    $this->array_export_alias[0]=$module->export_fields_alias[$r];
                                
    $var=true;
    foreach($fieldsarray as $code=>$label)
    {
        $var=!$var;
        print "<tr $bc[$var]>";
                    
        $entity=$objexport->array_export_entities[0][$code];
        $entityicon=$entitytoicon[$entity]?$entitytoicon[$entity]:$entity;
        $entitylang=$entitytolang[$entity]?$entitytolang[$entity]:$entity;

        print '<td>'.img_object('',$entityicon).' '.$langs->trans($entitylang).'</td>';
        if (isset($array_selected[$code]) && $array_selected[$code])
        {
            // Champ s�lectionn�
            print '<td>&nbsp;</td>';
            print '<td><a href="'.$_SERVER["PHP_SELF"].'?step=2&amp;datatoexport='.$datatoexport.'&amp;action=unselectfield&amp;field='.$code.'">'.img_left().'</a></td>';
            print '<td>'.$langs->trans($label).' ('.$code.')</td>';
        }
        else
        {
        	// Champ non s�lectionn�
            print '<td>'.$langs->trans($label).' ('.$code.')</td>';
            print '<td><a href="'.$_SERVER["PHP_SELF"].'?step=2&amp;datatoexport='.$datatoexport.'&amp;action=selectfield&amp;field='.$code.'">'.img_right().'</a></td>';
            print '<td>&nbsp;</td>';
        }

        print '</tr>';
    }
    
    print '</table>';

    print '</div>';
    
    /*
     * Barre d'action
     *
     */
    print '<div class="tabsAction">';

    if (sizeof($array_selected))
    {
        print '<a class="butAction" href="export.php?step=3&amp;datatoexport='.$datatoexport.'">'.$langs->trans("NextStep").'</a>';
    }

    print '</div>';    
}

if ($step == 3 && $datatoexport)
{
    asort($array_selected);

    llxHeader('',$langs->trans("NewExport"));
    
    /*
     * Affichage onglets
     */
    $h = 0;

    $head[$h][0] = DOL_URL_ROOT.'/exports/export.php?step=1';
    $head[$h][1] = $langs->trans("Step")." 1";
    $h++;

    $head[$h][0] = DOL_URL_ROOT.'/exports/export.php?step=2&amp;datatoexport='.$datatoexport;
    $head[$h][1] = $langs->trans("Step")." 2";
    $h++;

    $head[$h][0] = DOL_URL_ROOT.'/exports/export.php?step=3&amp;datatoexport='.$datatoexport;
    $head[$h][1] = $langs->trans("Step")." 3";
    $hselected=$h;
    $h++;
    
    dolibarr_fiche_head($head, $hselected, $langs->trans("NewExport"));

    print '<table width="100%" class="border">';

    // Module
    print '<tr><td width="25%">'.$langs->trans("Module").'</td>';
    print '<td>';
    print img_object($objexport->array_export_module[0]->getName(),$objexport->array_export_module[0]->picto).' ';
    print $objexport->array_export_module[0]->getName();
    print '</td></tr>';

    // Lot de donn�es � exporter
    print '<tr><td width="25%">'.$langs->trans("DatasetToExport").'</td>';
    print '<td>'.$objexport->array_export_label[0].'</td></tr>';

    // Nbre champs export�s
    print '<tr><td width="25%">'.$langs->trans("ExportedFields").'</td>';
    $list='';
    foreach($array_selected as $code=>$value)
    {
        $list.=($list?',':'');
        $list.=$langs->trans($objexport->array_export_fields[0][$code]);
    }
    print '<td>'.$list.'</td></tr>';

    print '</table>';
    print '<br>';
    
    print $langs->trans("ChooseFieldsOrdersAndTitle").'<br>';
    
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Entities").'</td>';
    print '<td>'.$langs->trans("ExportedFields").'</td>';
    print '<td align="right" colspan="2">'.$langs->trans("Position").'</td>';
    print '<td>&nbsp;</td>';
    print '<td>'.$langs->trans("FieldsTitle").'</td>';
    print '</tr>';

    $var=true;
    foreach($array_selected as $code=>$value)
    {
        $var=!$var;
        print "<tr $bc[$var]>";

        $entity=$objexport->array_export_entities[0][$code];
        $entityicon=$entitytoicon[$entity]?$entitytoicon[$entity]:$entity;
        $entitylang=$entitytolang[$entity]?$entitytolang[$entity]:$entity;

        print '<td>'.img_object('',$entityicon).' '.$langs->trans($entitylang).'</td>';
                    
        print '<td>'.$langs->trans($objexport->array_export_fields[0][$code]).' ('.$code.')</td>';

        print '<td align="right" width="100">';
        print $value.' ';
        print '</td><td align="center" width="20">';
        if ($value < sizeof($array_selected)) print '<a href="'.$_SERVER["PHP_SELF"].'?step=3&amp;datatoexport='.$datatoexport.'&amp;action=downfield&amp;field='.$code.'">'.img_down().'</a>';
        if ($value > 1) print '<a href="'.$_SERVER["PHP_SELF"].'?step=3&amp;datatoexport='.$datatoexport.'&amp;action=upfield&amp;field='.$code.'">'.img_up().'</a>';
        print '</td>';

        print '<td>&nbsp;</td>';

        print '<td>'.$langs->trans($objexport->array_export_fields[0][$code]).'</td>';

        print '</tr>';
    }
    
    print '</table>';

    print '</div>';
    
    /*
     * Barre d'action
     *
     */
    print '<div class="tabsAction">';

    if (sizeof($array_selected))
    {
        print '<a class="butAction" href="export.php?step=4&amp;datatoexport='.$datatoexport.'">'.$langs->trans("NextStep").'</a>';
    }

    print '</div>';    
}

if ($step == 4 && $datatoexport)
{
    asort($array_selected);

    llxHeader('',$langs->trans("NewExport"));
    
    /*
     * Affichage onglets
     */
    $h = 0;

    $head[$h][0] = DOL_URL_ROOT.'/exports/export.php?step=1';
    $head[$h][1] = $langs->trans("Step")." 1";
    $h++;

    $head[$h][0] = DOL_URL_ROOT.'/exports/export.php?step=2&amp;datatoexport='.$datatoexport;
    $head[$h][1] = $langs->trans("Step")." 2";
    $h++;

    $head[$h][0] = DOL_URL_ROOT.'/exports/export.php?step=3&amp;datatoexport='.$datatoexport;
    $head[$h][1] = $langs->trans("Step")." 3";
    $h++;

    $head[$h][0] = DOL_URL_ROOT.'/exports/export.php?step=4&amp;datatoexport='.$datatoexport;
    $head[$h][1] = $langs->trans("Step")." 4";
    $hselected=$h;
    $h++;
    
    dolibarr_fiche_head($head, $hselected, $langs->trans("NewExport"));

    print '<table width="100%" class="border">';

    // Module
    print '<tr><td width="25%">'.$langs->trans("Module").'</td>';
    print '<td>';
    print img_object($objexport->array_export_module[0]->getName(),$objexport->array_export_module[0]->picto).' ';
    print $objexport->array_export_module[0]->getName();
    print '</td></tr>';

    // Lot de donn�es � exporter
    print '<tr><td width="25%">'.$langs->trans("DatasetToExport").'</td>';
    print '<td>'.$objexport->array_export_label[0].'</td></tr>';

    // Nbre champs export�s
    print '<tr><td width="25%">'.$langs->trans("ExportedFields").'</td>';
    $list='';
    foreach($array_selected as $code=>$label)
    {
        $list.=($list?',':'');
        $list.=$langs->trans($objexport->array_export_fields[0][$code]);
    }
    print '<td>'.$list.'</td></tr>';

    print '</table>';
    print '<br>';
    
    print $langs->trans("NowClickToGenerateToBuildExportFile").'<br>';
    
    // Liste des formats d'exports disponibles
    $var=true;
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre">';
    print '<td>'.$langs->trans("AvailableFormats").'</td>';
    print '<td>'.$langs->trans("LibraryUsed").'</td>';
    print '<td>'.$langs->trans("LibraryVersion").'</td>';
    print '</tr>';

    $liste=$objmodelexport->liste_modeles($db);
    foreach($liste as $key)
    {
        $var=!$var;
        print '<tr '.$bc[$var].'><td>'.$objmodelexport->getDriverLabel($key).'</td><td>'.$objmodelexport->getLibLabel($key).'</td><td>'.$objmodelexport->getLibVersion($key).'</td></tr>';
    }
    print '</table>';    

    print '</div>';

    if ($mesg) print $mesg;

    $htmlform=new Form($db);
    print '<table width="100%"><tr><td width="50%">';

    if (! is_dir($conf->export->dir_ouput)) create_exdir($conf->export->dir_ouput);

    // Affiche liste des documents
    // NB: La fonction show_documents rescanne les modules qd genallowed=1
    $htmlform->show_documents('export','',$conf->export->dir_ouput.'/'.$user->id,$_SERVER["PHP_SELF"].'?step=4&amp;datatoexport='.$datatoexport,$liste,1,'csv','',1);
    
    print '</td><td width="50%">&nbsp;</td></tr>';
    print '</table>';
}



$db->close();

llxFooter('$Date$ - $Revision$');

?>
