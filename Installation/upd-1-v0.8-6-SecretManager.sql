-- Modifie la table des Secrets pour intégrer une notion de propriétaire du Secret.
ALTER TABLE scr_secrets ADD idn_id BIGINT NULL AFTER app_id;
