--
-- $Id$
-- $Source$
-- $Revision$
--
-- Attention � l ordre des requetes.
-- Ce fichier doit �tre charg� sur une version 2.1.0 
-- sans AUCUNE erreur ni warning
--

ALTER TABLE llx_cotisation ADD UNIQUE INDEX uk_cotisation (fk_adherent,dateadh);
