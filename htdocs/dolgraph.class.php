<?php
/* Copyright (c) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (c) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
	    \file       htdocs/dolgraph.class.php
		\brief      Fichier de la classe m�re de gestion des graph phplot
		\version    $Revision$
	    \remarks    Usage:
                    $graph_data = array(array('labelA',yA),array('labelB',yB));
                                  array(array('labelA',yA1,...,yAn),array('labelB',yB1,...yBn));
	                $px = new DolGraph();
                    $px->SetData($graph_data);
				    $px->SetMaxValue($px->GetCeilMaxValue());
				    $px->SetMinValue($px->GetFloorMinValue());
                    $px->SetTitle("title");
                    $px->SetLegend(array("Val1","Val2"));
                    $px->SetWidth(width);
                    $px->SetHeight(height);
                    $px->draw("file.png");
*/


/**
        \class      Graph
	    \brief      Classe m�re permettant la gestion des graph
*/

class DolGraph
{
	var $type='bars';  	// Type de graph
	var $data;			// Tableau de donnees
	var $width=380;
	var $height=200;
	var $MaxValue=0;
	var $MinValue=0;
	var $SetShading=0;
	var $PrecisionY=-1;
	var $SetHorizTickIncrement=-1;
	var $SetNumXTicks=-1;

	var $graph;     	// Objet PHPlot

	var $error;


	function DolGraph()
	{
		global $conf;
		global $theme_bordercolor, $theme_datacolor, $theme_bgcolor, $theme_bgcoloronglet;

		// Test si module GD pr�sent
		$modules_list = get_loaded_extensions();
		$isgdinstalled=0;
		foreach ($modules_list as $module)
		{
			if ($module == 'gd') { $isgdinstalled=1; }
		}
		if (! $isgdinstalled) {
			$this->error="Erreur: Le module GD pour php ne semble pas disponible. Il est requis pour g�n�rer les graphiques.";
			return -1;
		}

		// V�rifie que chemin vers PHPLOT_PATH est connu et definie $graphpath
		$graphpathdir=DOL_DOCUMENT_ROOT."/includes/phplot";
		if (defined('PHPLOT_PATH')) $graphpathdir=PHPLOT_PATH;
		if ($conf->global->PHPLOT_PATH) $graphpathdir=$conf->global->PHPLOT_PATH;
		if (! eregi('[\\\/]$',$graphpathdir)) $graphpathdir.='/';

		include_once($graphpathdir.'phplot.php');


		// D�fini propri�t�s de l'objet graphe
		$this->bordercolor = array(235,235,224);
		$this->datacolor = array(array(120,130,150), array(160,160,180), array(190,190,220));
		$this->bgcolor = array(235,235,224);

		$color_file = DOL_DOCUMENT_ROOT."/theme/".$conf->theme."/graph-color.php";
		if (is_readable($color_file))
		{
			include_once($color_file);
			if (isset($theme_bordercolor)) $this->bordercolor = $theme_bordercolor;
			if (isset($theme_datacolor))   $this->datacolor   = $theme_datacolor;
			if (isset($theme_bgcolor))     $this->bgcolor     = $theme_bgcolor;
		}
		//print 'bgcolor: '.join(',',$this->bgcolor).'<br>';
		return 1;
	}


	function isGraphKo()
	{
		return $this->error;
	}


	/**
	*    \brief      G�n�re le fichier graphique sur le disque
	*    \param      file    Nom du fichier image
	*/
	function draw($file)
	{
		// Prepare parametres
		$this->prepare($file);

		// G�n�re le fichier $file
		$this->graph->DrawGraph();
	}

	/**
	*    \brief      Pr�pare l'objet PHPlot
	*    \param      file    Nom du fichier image � g�n�rer
	*/
	function prepare($file)
	{
		// Define the object
		$this->graph = new PHPlot($this->width, $this->height);

		$phplotversion=4;
		if (defined('TOTY')) $phplotversion=5;

		$this->graph->SetIsInline(1);

		$this->graph->SetPlotType($this->type);
		$this->graph->SetDataValues($this->data);

		// Precision axe y (pas de decimal si 3 chiffres ou plus)
		if ($this->PrecisionY > -1)
		{
			$this->graph->SetPrecisionY($this->PrecisionY);
			if ($this->PrecisionY == 0)		// Si precision de 0
			{
				// Determine un nombre de ticks qui permet decoupage qui tombe juste
				$maxval=$this->getMaxValue();
				$minval=$this->getMinValue();
				if ($maxval * $minval >= 0)	// Si du meme signe
				{
					$plage=$maxval;
				}
				else
				{
					$plage=$maxval-$minval;
				}
				if (abs($plage) <= 2)
				{
					$this->SetMaxValue(2);
					$maxticks=2;
				}
				else
				{
					$maxticks=10;
			        if (substr($plage,0,1) == 3 || substr($plage,0,1) == 6)
			        {
						$maxticks=min(6,$plage);
			        }
			        elseif (substr($plage,0,1) == 4 || substr($plage,0,1) == 8)
			        {
						$maxticks=min(8,$plage);
			        }
			        elseif (substr($plage,0,1) == 7)
			        {
						$maxticks=min(7,$plage);
			        }
			        elseif (substr($plage,0,1) == 9)
			        {
						$maxticks=min(9,$plage);
			        }
				}
				$this->graph->SetNumVertTicks($maxticks);
//				print 'minval='.$minval.' - maxval='.$maxval.' - plage='.$plage.' - maxticks='.$maxticks.'<br>';
			}
		}
		else
		{
			$this->graph->SetPrecisionY(3-strlen(round($this->GetMaxValueInData())));
		}
		$this->graph->SetPrecisionX(0);

		// Set areas
		$top_space=40;
		if ($phplotversion >= 5) $top_space=25;
		$left_space=80;								// For y labels
		$right_space=10;							// If no legend
		if (isset($this->Legend)) $right_space=70;	// For legend

		$this->graph->SetNewPlotAreaPixels($left_space, $top_space, $this->width-$right_space, $this->height-40);
		if (isset($this->MaxValue))
		{
			$this->graph->SetPlotAreaWorld(0,$this->MinValue,sizeof($this->data),$this->MaxValue);
		}

		// Define title
		if (isset($this->title)) $this->graph->SetTitle($this->title);

		// D�fini position du graphe (et legende) au sein de l'image
		if (isset($this->Legend))
		{
			$this->graph->SetLegendPixels($this->width-$right_space+8,40,'');
			$this->graph->SetLegend($this->Legend);
		}

		if (isset($this->SetShading))
		{
			$this->graph->SetShading($this->SetShading);
		}
		$this->graph->SetTickLength(6);

		$this->graph->SetBackgroundColor($this->bgcolor);
		$this->graph->SetDataColors($this->datacolor, $this->bordercolor);

		if ($this->SetNumXTicks > -1)
		{
			if ($phplotversion >= 5)	// If PHPlot 5, for compatibility
			{
				$this->graph->SetXLabelType('');
				$this->graph->SetNumXTicks($this->SetNumXTicks);
			}
			else
			{
				$this->graph->SetNumHorizTicks($this->SetNumXTicks);
			}
		}
		if ($this->SetHorizTickIncrement > -1)
		{
			// Les ticks sont en mode forc�
			$this->graph->SetHorizTickIncrement($this->SetHorizTickIncrement);
			if ($phplotversion >= 5)	// If PHPlot 5, for compatibility
			{
				$this->graph->SetXLabelType('');
				$this->graph->SetXTickLabelPos('none');
			}
		}
		else
		{
			// Les ticks sont en mode automatique
			if ($phplotversion >= 5)	// If PHPlot 5, for compatibility
			{
				$this->graph->SetXDataLabelPos('none');
			}
		}

		if ($phplotversion >= 5)
		{
			// Ne gere pas la transparence
			// $this->graph->SetBgImage(DOL_DOCUMENT_ROOT.'/theme/dolibarr_logo_2.png','tile');
			$this->graph->SetDrawPlotAreaBackground(array(255,255,255));
		}

		$this->graph->SetPlotBorderType("left");		// Affiche axe y a gauche uniquement
		$this->graph->SetVertTickPosition('plotleft');	// Affiche tick axe y a gauche uniquement

		$this->graph->SetOutputFile($file);
	}

	function SetPrecisionY($which_prec)
	{
		$this->PrecisionY = $which_prec;
		return true;
	}

	/*
	 	\remarks	Utiliser SetNumTicks ou SetHorizTickIncrement mais pas les 2
	*/
	function SetHorizTickIncrement($xi)
	{
		$this->SetHorizTickIncrement = $xi;
		return true;
	}

	/*
	 	\remarks	Utiliser SetNumTicks ou SetHorizTickIncrement mais pas les 2
	*/
	function SetNumXTicks($xt)
	{
		$this->SetNumXTicks = $xt;
		return true;
	}


	function SetYLabel($label)
	{
		$this->YLabel = $label;
	}

	function SetWidth($w)
	{
		$this->width = $w;
	}

	function SetTitle($title)
	{
		$this->title = $title;
	}

	function SetData($data)
	{
		$this->data = $data;
	}

	function SetType($type)
	{
		$this->type = $type;
	}

	function SetLegend($legend)
	{
		$this->Legend = $legend;
	}

	function SetMaxValue($max)
	{
		$this->MaxValue = $max;
	}
	function GetMaxValue()
	{
		return $this->MaxValue;
	}

	function SetMinValue($min)
	{
		$this->MinValue = $min;
	}
	function GetMinValue()
	{
		return $this->MinValue;
	}

	function SetHeight($h)
	{
		$this->height = $h;
	}

	function SetShading($s)
	{
		$this->SetShading = $s;
	}

	function ResetBgColor()
	{
		unset($this->bgcolor);
	}

	/**
	*	\brief		Definie la couleur de fond du graphique
	*	\param		bg_color		array(R,G,B) ou 'onglet' ou 'default'
	*/
	function SetBgColor($bg_color = array(255,255,255))
	{
		global $theme_bgcolor,$theme_bgcoloronglet;
		if (! is_array($bg_color))
		{
			if ($bg_color == 'onglet')
			{
				//print 'ee'.join(',',$theme_bgcoloronglet);
				$this->bgcolor = $theme_bgcoloronglet;
			}
			else
			{
				$this->bgcolor = $theme_bgcolor;
			}
		}
		else
		{
			$this->bgcolor = $bg_color;
		}
	}

	function ResetDataColor()
	{
		unset($this->datacolor);
	}

	function GetMaxValueInData()
	{
		$k = 0;
		$vals = array();

		$nblines = sizeof($this->data);
		$nbvalues = sizeof($this->data[0]) - 1;

		for ($j = 0 ; $j < $nblines ; $j++)
		{
			for ($i = 0 ; $i < $nbvalues ; $i++)
			{
				$vals[$k] = $this->data[$j][$i+1];
				$k++;
			}
		}
		rsort($vals);
		return $vals[0];
	}

	function GetMinValueInData()
	{
		$k = 0;
		$vals = array();

		$nblines = sizeof($this->data);
		$nbvalues = sizeof($this->data[0]) - 1;

		for ($j = 0 ; $j < $nblines ; $j++)
		{
			for ($i = 0 ; $i < $nbvalues ; $i++)
			{
				$vals[$k] = $this->data[$j][$i+1];
				$k++;
			}
		}
		sort($vals);
		return $vals[0];
	}

	function GetCeilMaxValue()
	{
		$max = $this->GetMaxValueInData();
		if ($max != 0) $max++;
		$size=strlen(abs(ceil($max)));
		$factor=1;
		for ($i=0; $i < ($size-1); $i++)
		{
			$factor*=10;
		}
		$res=ceil($max/$factor)*$factor;

		//print "max=".$max." res=".$res;
		return $res;
	}

	function GetFloorMinValue()
	{
		$min = $this->GetMinValueInData();
		if ($min != 0) $min--;
		$size=strlen(abs(floor($min)));
		$factor=1;
		for ($i=0; $i < ($size-1); $i++)
		{
			$factor*=10;
		}
		$res=floor($min/$factor)*$factor;

		//print "min=".$min." res=".$res;
		return $res;
	}
}

?>
