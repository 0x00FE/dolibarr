-- phpMyAdmin SQL Dump
-- version 2.6.2-Debian-3sarge1
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- G�n�r� le : Samedi 05 Ao�t 2006 � 17:25
-- Version du serveur: 4.0.24
-- Version de PHP: 4.3.10-16
-- 
-- Base de donn�es: `dolidev`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `llx_osc_product`
-- 

CREATE TABLE IF NOT EXISTS `llx_osc_product` (
  `osc_prodid` int(11) NOT NULL default '0',
  `osc_lastmodif` datetime default NULL,
  `doli_prodidp` int(11) NOT NULL default '0',
  PRIMARY KEY  (`osc_prodid`),
  UNIQUE KEY `doli_prodidp` (`doli_prodidp`)
) TYPE=InnoDB COMMENT='Table transition produit OSC - produit Dolibarr';


