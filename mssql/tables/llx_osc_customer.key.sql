-- phpMyAdmin SQL Dump
-- version 2.6.2-Debian-3sarge1
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- G�n�r� le : Samedi 05 Ao�t 2006 � 17:25
-- Version du serveur: 4.0.24
-- Version de PHP: 4.3.10-16
-- 


ALTER TABLE llx_osc_customer
  ADD CONSTRAINT llx_osc_customer_societe_idp FOREIGN KEY (doli_socidp) REFERENCES llx_societe (idp);
