<?php
/* Copyright (C) 2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
		\file 		htdocs/admin/tools/dolibarr_export.php
		\brief      Page export de la base
		\version    $Revision$
*/

require("./pre.inc.php");
include_once $dolibarr_main_document_root."/lib/databases/".$conf->db->type.".lib.php";

$langs->load("admin");

if (! $user->admin)
  accessforbidden();


llxHeader();

print_fiche_titre($langs->trans("Backup"),'','setup');
print '<br>';

print $langs->trans("DatabaseName").' : <b>'.$dolibarr_main_db_name.'</b><br>';
print '<br>';

?>



<!-- Dump of a server -->
<form method="post" action="export.php" name="dump">

<input type="hidden" name="export_type" value="server" />

<script type="text/javascript" language="javascript">
//<![CDATA[
function hide_them_all() {
    document.getElementById("mysql_options").style.display = 'none';
//    document.getElementById("csv_options").style.display = 'none';
//    document.getElementById("latex_options").style.display = 'none';
//    document.getElementById("pdf_options").style.display = 'none';
//    document.getElementById("none_options").style.display = 'none';
}

function show_checked_option() {
    hide_them_all();

    if (document.getElementById('radio_dump_mysql')) {
        document.getElementById('mysql_options').style.display = 'block';
    }
//    if (document.getElementById('radio_dump_latex').checked) {
//        document.getElementById('latex_options').style.display = 'block';
//    }
//    if (document.getElementById('radio_dump_pdf').checked) {
//        document.getElementById('pdf_options').style.display = 'block';
//    }
//    if (document.getElementById('radio_dump_xml').checked) {
//        document.getElementById('none_options').style.display = 'block';
//    }
//    if (document.getElementById('radio_dump_csv')) {
//        document.getElementById('csv_options').style.display = 'block';
//    }
    
}

//]]>
</script>

<fieldset id="fieldsetexport">
<legend>Sch�ma et/ou contenu des bases de donn�es
</legend>


<!-- LDR -->
<table><tr><td valign="top">

<div id="div_container_exportoptions">
<fieldset id="exportoptions">
<legend>M�thode d'exportation</legend>

    <div class="formelementrow">
        <input type="radio" name="what" value="mysql" id="radio_dump_mysql"
            onclick="
                if (this.checked) {
                    hide_them_all();
                    document.getElementById('mysql_options').style.display = 'block';
                }; return true"
             />
            <label for="radio_dump_mysql">MySQLDump</label>
    </div>

<!--    
    <div class="formelementrow">
        <input type="radio" name="what" value="latex" id="radio_dump_latex"
            onclick="
                if (this.checked) {
                    hide_them_all();
                    document.getElementById('latex_options').style.display = 'block';
                }; return true"
             />
        <label for="radio_dump_latex">LaTeX</label>

    </div>
    
    <div class="formelementrow">
        <input type="radio" name="what" value="pdf" id="radio_dump_pdf"
            onclick="
                if (this.checked) {
                    hide_them_all();
                    document.getElementById('pdf_options').style.display = 'block';
                }; return true"
             />
        <label for="radio_dump_pdf">PDF</label>
    </div>

    <div class="formelementrow">
        <input type="radio" name="what" value="csv" id="radio_dump_csv"
            onclick="if
                (this.checked) {
                    hide_them_all();
                    document.getElementById('csv_options').style.display = 'block';
                 }; return true"
              />
        <label for="radio_dump_csv">CSV</label>
    </div>
    
    <div class="formelementrow">
        <input type="radio" name="what" value="xml" id="radio_dump_xml"
            onclick="
                if (this.checked) {
                    hide_them_all();
                    document.getElementById('none_options').style.display = 'block';
                }; return true"
             />
        <label for="radio_dump_xml">XML</label>

    </div>
-->

</fieldset>
</div>

</td><td valign="top">


<div id="div_container_sub_exportoptions">


<fieldset id="mysql_options">
    <legend>Parametres export MySQL</legend>

    <div class="formelementrow">
        Path commande mysqldump:<br />
        <input type="text" name="mysqldump" size="80"
            value="<?php echo $conf->global->SYSTEMTOOLS_MYSQLDUMP ?>" />
    </div>

    <div class="formelementrow">
        <input type="checkbox" name="use_transaction" value="yes"
            id="checkbox_use_transaction"
             />
        <label for="checkbox_use_transaction">
            Utiliser le mode transactionnel</label>

    </div>

    <div class="formelementrow">
        <input type="checkbox" name="disable_fk" value="yes"
            id="checkbox_disable_fk" checked="true"
             />
        <label for="checkbox_disable_fk">
            Ordre de d�sactivation des cl�s �trang�res � l'import</label>
    </div>
    <label for="select_sql_compat">
        Compatibilit� de l'exportation:</label>

    <select name="sql_compat" id="select_sql_compat">
        <option value="NONE" selected="selected">NONE</option>
<option value="ANSI">ANSI</option>
<option value="DB2">DB2</option>
<option value="MAXDB">MAXDB</option>
<option value="MYSQL323">MYSQL323</option>
<option value="MYSQL40">MYSQL40</option>
<option value="MSSQL">MSSQL</option>
<option value="ORACLE">ORACLE</option>
<option value="POSTGRESQL">POSTGRESQL</option>
    </select>
    <fieldset>
        <legend>Options d'exportation</legend>
        <input type="checkbox" name="drop_database" value="yes"
            id="checkbox_drop_database"
             />
        <label for="checkbox_drop_database">

            Ajouter DROP DATABASE</label>
    </fieldset>
    <fieldset>
        <legend>
            <input type="checkbox" name="sql_structure" value="structure"
                id="checkbox_sql_structure"
                 checked="checked"                onclick="
                    if (!this.checked &amp;&amp; !document.getElementById('checkbox_sql_data').checked)
                        return false;
                    else return true;" />
            <label for="checkbox_sql_structure">
                Structure</label>
        </legend>

        <input type="checkbox" name="drop" value="1" id="checkbox_dump_drop"
             />
        <label for="checkbox_dump_drop">
            Inclure des �nonc�s "DROP TABLE"</label><br />

    </fieldset>
    <fieldset>
        <legend>

            <input type="checkbox" name="sql_data" value="data"
                id="checkbox_sql_data"  checked="checked"                onclick="
                    if (!this.checked &amp;&amp; (!document.getElementById('checkbox_sql_structure') || !document.getElementById('checkbox_sql_structure').checked))
                        return false;
                    else return true;" />
            <label for="checkbox_sql_data">
                Donn�es</label>
        </legend>
        <input type="checkbox" name="showcolumns" value="yes"
            id="checkbox_dump_showcolumns"
             />
        <label for="checkbox_dump_showcolumns">
            Nomme les colonnes</label><br />

        <input type="checkbox" name="extended_ins" value="yes"
            id="checkbox_dump_extended_ins"
             />
        <label for="checkbox_dump_extended_ins">
            Insertions �tendues</label><br />

        <input type="checkbox" name="delayed" value="yes"
            id="checkbox_dump_delayed"
             />

        <label for="checkbox_dump_delayed">
            Insertions avec d�lais (DELAYED)</label><br />

        <input type="checkbox" name="sql_ignore" value="yes"
            id="checkbox_dump_ignore"
             />
        <label for="checkbox_dump_ignore">
            Ignorer les erreurs de doublons (INSERT IGNORE)</label><br />

        <input type="checkbox" name="hexforbinary" value="yes"
            id="checkbox_hexforbinary"
             checked="checked" />
        <label for="checkbox_hexforbinary">
            Encoder les champs binaires en hexad�cimal</label><br />

    </fieldset>
</fieldset>

<!--
<fieldset id="latex_options">
    <legend>Parametres export LaTeX</legend>

    <div class="formelementrow">
        <input type="checkbox" name="latex_caption" value="yes"
            id="checkbox_latex_show_caption"
             checked="checked" />

        <label for="checkbox_latex_show_caption">
            Inclure les sous-titres</label>
    </div>

    <fieldset>
        <legend>
            <input type="checkbox" name="latex_structure" value="structure"
                id="checkbox_latex_structure"
                 checked="checked"                onclick="
                    if (!this.checked &amp;&amp; !document.getElementById('checkbox_latex_data').checked)
                        return false;
                    else return true;" />
            <label for="checkbox_latex_structure">
                Structure</label>

        </legend>

        <table>
        <tr><td><label for="latex_structure_caption">
                    Sous-titre de la table</label></td>
            <td><input type="text" name="latex_structure_caption" size="30"
                    value="Structure de la table __TABLE__"
                    id="latex_structure_caption" />
            </td>
        </tr>
        <tr><td><label for="latex_structure_continued_caption">

                    Sous-titre de la table (suite)</label></td>
            <td><input type="text" name="latex_structure_continued_caption"
                    value="Structure de la table __TABLE__ (suite)"
                    size="30" id="latex_structure_continued_caption" />
            </td>
        </tr>
        <tr><td><label for="latex_structure_label">
                    Cl� de l'�tiquette</label></td>
            <td><input type="text" name="latex_structure_label" size="30"
                    value="tab:__TABLE__-structure"
                    id="latex_structure_label" />
            </td>

        </tr>
        </table>

        </fieldset>
        <fieldset>
        <legend>
            <input type="checkbox" name="latex_data" value="data"
                id="checkbox_latex_data"
                 checked="checked"                onclick="
                    if (!this.checked &amp;&amp; (!document.getElementById('checkbox_latex_structure') || !document.getElementById('checkbox_latex_structure').checked))
                        return false;
                    else return true;" />
            <label for="checkbox_latex_data">
                Donn�es</label>

        </legend>
        <input type="checkbox" name="latex_showcolumns" value="yes"
            id="ch_latex_showcolumns"
             checked="checked" />
        <label for="ch_latex_showcolumns">
            Nom des colonnes</label><br />
        <table>
        <tr><td><label for="latex_data_caption">
                    Sous-titre de la table</label></td>
            <td><input type="text" name="latex_data_caption" size="30"
                    value="Contenu de la table __TABLE__"
                    id="latex_data_caption" />

            </td>
        </tr>
        <tr><td><label for="latex_data_continued_caption">
                    Sous-titre de la table (suite)</label></td>
            <td><input type="text" name="latex_data_continued_caption" size="30"
                    value="Contenu de la table __TABLE__ (suite)"
                    id="latex_data_continued_caption" />
            </td>
        </tr>
        <tr><td><label for="latex_data_label">

                    Cl� de l'�tiquette</label></td>
            <td><input type="text" name="latex_data_label" size="30"
                    value="tab:__TABLE__-data"
                    id="latex_data_label" />
            </td>
        </tr>
        <tr><td><label for="latex_replace_null">
                    Remplacer NULL par</label></td>
            <td><input type="text" name="latex_replace_null" size="20"
                    value="\textit{NULL}"
                    id="latex_replace_null" />
            </td>

        </tr>
        </table>
    </fieldset>
</fieldset>
-->

<!--
<fieldset id="csv_options">
    <input type="hidden" name="csv_data" value="csv_data" />
    <legend>Parametres export CSV</legend>

    <table>

    <tr><td><label for="export_separator">
                Champs termin�s par</label></td>
        <td><input type="text" name="export_separator" size="2"
                id="export_separator"
                value=";" />
        </td>
    </tr>
    <tr><td><label for="enclosed">
                Champs entour�s par</label></td>
        <td><input type="text" name="enclosed" size="2"
                id="enclosed"
                value="&quot;" />

        </td>
    </tr>
    <tr><td><label for="escaped">
                Caract�re sp�cial</label></td>
        <td><input type="text" name="escaped" size="2"
                id="escaped"
                value="\" />
        </td>
    </tr>
    <tr><td><label for="add_character">

                Lignes termin�es par</label></td>
        <td><input type="text" name="add_character" size="2"
                id="add_character"
                value="\r\n" />
        </td>
    </tr>
    <tr><td><label for="csv_replace_null">
                Remplacer NULL par</label></td>
        <td><input type="text" name="csv_replace_null" size="20"
                id="csv_replace_null"
                value="NULL" />
        </td>

    </tr>
    </table>
    <input type="checkbox" name="showcsvnames" value="yes"
        id="checkbox_dump_showcsvnames"
          />
    <label for="checkbox_dump_showcsvnames">
        Afficher les noms de champ en premi�re ligne</label>
</fieldset>
-->

<!--
<fieldset id="pdf_options">
    <input type="hidden" name="pdf_data" value="pdf_data" />

    <legend>Parametres export PDF</legend>

    <div class="formelementrow">
        <label for="pdf_report_title">Titre du rapport</label>
        <input type="text" name="pdf_report_title" size="50"
            value=""
            id="pdf_report_title" />
    </div>
</fieldset>
-->

<!--
<fieldset id="none_options">
    <legend>Options XML</legend>
    Ce format ne comporte pas d'options    <input type="hidden" name="xml_data" value="xml_data" />
</fieldset>
-->

</div>


</td></tr></table>

<script type="text/javascript" language="javascript">
//<![CDATA[
    show_checked_option();
	hide_them_all();
//]]>
</script>

</fieldset>



<fieldset>
    
    <label for="filename_template">
        Nom du fichier � g�n�rer</label> :
    <input type="text" name="filename_template" size="60" id="filename_template"
     value="<?php
$file='mysqldump_'.$dolibarr_main_db_name.'_'.strftime("%Y%m%d%H%M").'.sql';
echo $file;
?>" />

<br><br>

    <div class="formelementrow">
        Compression :
        <input type="radio" name="compression" value="none"
            id="radio_compression_none"
            onclick="document.getElementById('checkbox_dump_asfile').checked = true;"
             checked="checked" />
        <label for="radio_compression_none">aucune</label>

<!-- No zip support (not open source)
            <input type="radio" name="compression" value="zip"
            id="radio_compression_zip"
            onclick="document.getElementById('checkbox_dump_asfile').checked = true;"
             />
        <label for="radio_compression_zip">"zipp�"</label>
-->

<?php
if (function_exists('gz_open'))
{
?>
            <input type="radio" name="compression" value="gz"
            id="radio_compression_gzip"
            onclick="document.getElementById('checkbox_dump_asfile').checked = true;"
             />
        <label for="radio_compression_gzip">"gzipp�"</label>
<?php
}
?>
<?php
if (function_exists('bz_open'))
{
?>
            <input type="radio" name="compression" value="bz"
            id="radio_compression_bzip"
            onclick="document.getElementById('checkbox_dump_asfile').checked = true;"
             />
        <label for="radio_compression_bzip">"bzipp�"</label>
        </div>
<?php
}
?>

</fieldset>


<center>
    <input type="submit" class="button" value="Ex�cuter" id="buttonGo" /><br><br>
</center>


</form>


<script type="text/javascript" language="javascript">
//<![CDATA[


// set current db, table and sql query in the querywindow
if (window.parent.refreshLeft) {
    window.parent.reload_querywindow("","","");
}


if (window.parent.frames[1]) {
    // reset content frame name, as querywindow needs to set a unique name
    // before submitting form data, and navigation frame needs the original name
    if (window.parent.frames[1].name != 'frame_content') {
        window.parent.frames[1].name = 'frame_content';
    }
    if (window.parent.frames[1].id != 'frame_content') {
        window.parent.frames[1].id = 'frame_content';
    }
    //window.parent.frames[1].setAttribute('name', 'frame_content');
    //window.parent.frames[1].setAttribute('id', 'frame_content');
}
//]]>
</script>


<?php

llxFooter();

?>