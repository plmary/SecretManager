--
-- Client: localhost
-- Généré le: Mar 19 Février 2013 à 16:54
-- Version du serveur: 5.5.25a
-- Version de PHP: 5.4.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `secret_manager`
--
USE `secret_manager`;


--
-- Ajout d'une clé unique sur la table `prf_profiles`
--
ALTER TABLE `prf_profiles` ADD UNIQUE (
`prf_label`
)


--
-- Ajout d'une clé unique sur la table `sgr_secrets_groups`
--
ALTER TABLE `sgr_secrets_groups` ADD UNIQUE (
`sgr_label`
);


--
-- Ajout d'une nouvelle colonne dans la table `ach_access_history` et création de la table `aht_access_history_type`
--
ALTER TABLE `ach_access_history` ADD `aht_id` BIGINT NOT NULL;

DROP TABLE IF EXISTS `aht_access_history_type`;
CREATE TABLE IF NOT EXISTS `aht_access_history_type` (
  `aht_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `aht_name` varchar(60) NOT NULL,
  PRIMARY KEY (`aht_id`),
  UNIQUE KEY `aht_name` (`aht_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;


--
-- Ajout des valeurs par défaut de la table `aht_access_history_type`
--
INSERT INTO `aht_access_history_type` (`aht_id`, `aht_name`) VALUES(1, 'L_Access_History_Type_1');
INSERT INTO `aht_access_history_type` (`aht_id`, `aht_name`) VALUES(2, 'L_Access_History_Type_2');


--
-- Supprime le précédent index de la table `cvl_civilities` et le recréé
--
ALTER TABLE `cvl_civilities` DROP INDEX `cvl_civilities_idx`;
ALTER TABLE `secret_manager`.`cvl_civilities` ADD UNIQUE `cvl_civilities_idx` ( `cvl_last_name` , `cvl_first_name` );