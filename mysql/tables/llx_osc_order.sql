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
-- Structure de la table `llx_osc_order`
-- 

CREATE TABLE IF NOT EXISTS `llx_osc_order` (
  `osc_orderid` int(11) NOT NULL default '0',
  `osc_lastmodif` datetime default NULL,
  `doli_orderidp` int(11) NOT NULL default '0',
  PRIMARY KEY  (`osc_orderid`),
  UNIQUE KEY `doli_orderidp` (`doli_orderidp`)
) TYPE=InnoDB COMMENT='Table transition commande OSC - commande Dolibarr';
