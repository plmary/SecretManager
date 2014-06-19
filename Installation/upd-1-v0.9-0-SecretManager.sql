--
-- Ce script met à jour la base de données "secret_manager".
--
--
-- @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
-- @author Pierre-Luc MARY
-- @date 2014-06-18
--

-- Sélectionne la base de données "secret_manager".
USE `secret_manager`;

-- Modifie la table des Utilisateurs pour introduire la notion d'Opérateur en lieu et place au profil Auditeur.
ALTER TABLE `idn_identities` CHANGE `idn_auditor` `idn_operator` TINYINT(1) NOT NULL DEFAULT '0';

-- L'environnement n'est plus obligatoire. Notamment dans le cas des Secrets personnels.
ALTER TABLE `scr_secrets` CHANGE `env_id` `env_id` BIGINT(20) NULL;