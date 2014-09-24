--
-- Ce script met à jour la base de données "secret_manager".
-- Passage de la v0.9-0 à la v0.9-1
--
--
-- @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
-- @author Pierre-Luc MARY
-- @date 2014-09-18
--

-- Sélectionne la base de données "secret_manager".
USE `secret_manager`;

-- Il n'y a plus de lien fort entre la table des Groupes de Secrets et la table des Secrets (cas de la gestion des Secrets Personnels).
ALTER TABLE `scr_secrets` CHANGE `sgr_id` `sgr_id` BIGINT(20) NULL;
ALTER TABLE `scr_secrets` DROP FOREIGN KEY `sgr_scr_fk`;


-- Il n'y a plus de lien fort entre la table des Applications et la table des Secrets (cas de la gestion des Secrets de type moet de passe OS).
ALTER TABLE `scr_secrets` DROP FOREIGN KEY `app_scr_fk`;