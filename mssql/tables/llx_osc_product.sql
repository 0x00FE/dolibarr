-- phpMyAdmin SQL Dump
-- version 2.6.2-Debian-3sarge1
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- G�n�r� le : Samedi 05 Ao�t 2006 � 17:25
-- Version du serveur: 4.0.24
-- Version de PHP: 4.3.10-16
-- 
-- Base de donn�es: 'dolidev'
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table 'llx_osc_product'
-- 

if not exists (select * from sysobjects where name='llx_osc_product' and xtype='U')
CREATE TABLE llx_osc_product (
  osc_prodid int PRIMARY KEY NOT NULL default 0,
  osc_lastmodif datetime default NULL,
  doli_prodidp int UNIQUE NOT NULL default 0,
);
