--
-- $Id$
--
-- Attention � l ordre des requetes.
-- Ce fichier doit �tre charg� sur une version 2.2.0 
--

delete from llx_const where name='MAIN_GRAPH_LIBRARY' and (value like 'phplot%' or value like 'artichow%');
