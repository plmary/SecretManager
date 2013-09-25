-- phpMyAdmin SQL Dump
-- version 3.5.2
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Mar 19 Février 2013 à 16:07
-- Version du serveur: 5.5.25a
-- Version de PHP: 5.4.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `secret_manager`
--
DROP DATABASE IF EXISTS `secret_manager`;
CREATE DATABASE `secret_manager` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `secret_manager`;

-- --------------------------------------------------------

--
-- Structure de la table `ach_access_history`
--

DROP TABLE IF EXISTS `ach_access_history`;
CREATE TABLE IF NOT EXISTS `ach_access_history` (
  `ach_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `scr_id` bigint(20) DEFAULT NULL,
  `idn_id` bigint(20) NOT NULL,
  `ach_date` datetime NOT NULL,
  `ach_access` varchar(300) CHARACTER SET latin1 NOT NULL,
  `ach_ip` varchar(40) DEFAULT NULL,
  `aht_id` bigint(20) NOT NULL,
  PRIMARY KEY (`ach_id`),
  KEY `idn_identities_ach_access_history_fk` (`idn_id`),
  KEY `scr_secrets_ach_access_history_fk` (`scr_id`),
  KEY `ach_type` (`aht_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=400 ;

-- --------------------------------------------------------

--
-- Structure de la table `aht_access_history_type`
--

DROP TABLE IF EXISTS `aht_access_history_type`;
CREATE TABLE IF NOT EXISTS `aht_access_history_type` (
  `aht_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `aht_name` varchar(60) NOT NULL,
  PRIMARY KEY (`aht_id`),
  UNIQUE KEY `aht_name` (`aht_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Structure de la table `cvl_civilities`
--

DROP TABLE IF EXISTS `cvl_civilities`;
CREATE TABLE IF NOT EXISTS `cvl_civilities` (
  `cvl_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cvl_last_name` varchar(35) CHARACTER SET latin1 NOT NULL,
  `cvl_first_name` varchar(25) CHARACTER SET latin1 NOT NULL,
  `cvl_sex` tinyint(1) NOT NULL DEFAULT '0',
  `cvl_birth_date` date DEFAULT NULL,
  `cvl_born_town` varchar(60) CHARACTER SET latin1 DEFAULT NULL,
  `cvl_logical_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cvl_id`),
  UNIQUE KEY `cvl_civilities_idx` (`cvl_last_name`,`cvl_first_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Structure de la table `ent_entities`
--

DROP TABLE IF EXISTS `ent_entities`;
CREATE TABLE IF NOT EXISTS `ent_entities` (
  `ent_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ent_code` varchar(10) CHARACTER SET latin1 NOT NULL,
  `ent_label` varchar(60) CHARACTER SET latin1 NOT NULL,
  `ent_logical_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ent_id`),
  UNIQUE KEY `ent_entites_idx` (`ent_code`),
  UNIQUE KEY `ent_entites_idx1` (`ent_label`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Structure de la table `env_environments`
--

DROP TABLE IF EXISTS `env_environments`;
CREATE TABLE IF NOT EXISTS `env_environments` (
  `env_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `env_name` varchar(30) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`env_id`),
  UNIQUE KEY `env_environments_idx` (`env_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Structure de la table `idn_identities`
--

DROP TABLE IF EXISTS `idn_identities`;
CREATE TABLE IF NOT EXISTS `idn_identities` (
  `idn_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ent_id` bigint(20) NOT NULL,
  `cvl_id` bigint(20) NOT NULL,
  `idn_login` varchar(20) CHARACTER SET latin1 NOT NULL,
  `idn_authenticator` char(64) CHARACTER SET latin1 NOT NULL,
  `idn_salt` varchar(32) CHARACTER SET latin1 NOT NULL,
  `idn_change_authenticator` tinyint(1) NOT NULL DEFAULT '1',
  `idn_super_admin` tinyint(1) NOT NULL DEFAULT '0',
  `idn_auditor` tinyint(1) NOT NULL DEFAULT '0',
  `idn_attempt` smallint(6) NOT NULL DEFAULT '0',
  `idn_disable` tinyint(1) NOT NULL DEFAULT '0',
  `idn_logical_delete` tinyint(1) NOT NULL DEFAULT '0',
  `idn_last_connection` datetime NOT NULL,
  `idn_expiration_date` datetime NOT NULL,
  `idn_updated_authentication` datetime NOT NULL,
  PRIMARY KEY (`idn_id`),
  UNIQUE KEY `idn_identities_idx` (`idn_login`),
  KEY `ent_entities_idn_identities_fk` (`ent_id`),
  KEY `civilités_idn_identities_fk` (`cvl_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Structure de la table `idpr_identities_profiles`
--

DROP TABLE IF EXISTS `idpr_identities_profiles`;
CREATE TABLE IF NOT EXISTS `idpr_identities_profiles` (
  `idn_id` bigint(20) NOT NULL,
  `prf_id` bigint(20) NOT NULL,
  `idpr_logical_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idn_id`,`prf_id`),
  KEY `profils_identité_rattaché_à_profils_fk` (`prf_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `prf_profiles`
--

DROP TABLE IF EXISTS `prf_profiles`;
CREATE TABLE IF NOT EXISTS `prf_profiles` (
  `prf_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `prf_label` varchar(60) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`prf_id`),
  UNIQUE KEY `prf_profiles_idx` (`prf_label`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Structure de la table `prsg_profiles_secrets_groups`
--

DROP TABLE IF EXISTS `prsg_profiles_secrets_groups`;
CREATE TABLE IF NOT EXISTS `prsg_profiles_secrets_groups` (
  `prf_id` bigint(20) NOT NULL,
  `sgr_id` bigint(20) NOT NULL,
  `rgh_id` bigint(20) NOT NULL,
  PRIMARY KEY (`prf_id`,`sgr_id`,`rgh_id`),
  KEY `rgh_rights_prpg_profiles_passwords_groups_fk` (`rgh_id`),
  KEY `sgr_secrets_groups_prpg_profiles_passwords_groups_fk` (`sgr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `rgh_rights`
--

DROP TABLE IF EXISTS `rgh_rights`;
CREATE TABLE IF NOT EXISTS `rgh_rights` (
  `rgh_id` bigint(20) NOT NULL,
  `rgh_name` varchar(30) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`rgh_id`),
  UNIQUE KEY `rgh_rights_idx` (`rgh_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `scr_secrets`
--

DROP TABLE IF EXISTS `scr_secrets`;
CREATE TABLE IF NOT EXISTS `scr_secrets` (
  `scr_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sgr_id` bigint(20) NOT NULL,
  `stp_id` bigint(20) NOT NULL,
  `env_id` bigint(20) NOT NULL,
  `scr_host` varchar(255) CHARACTER SET latin1 NOT NULL,
  `scr_user` varchar(100) CHARACTER SET latin1 NOT NULL,
  `scr_password` longblob NOT NULL,
  `scr_application` varchar(60) CHARACTER SET latin1 DEFAULT NULL,
  `scr_comment` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `scr_alert` int(11) NOT NULL DEFAULT '0',
  `scr_creation_date` datetime NOT NULL,
  `scr_modification_date` datetime NOT NULL,
  `scr_expiration_date` datetime NULL,
  PRIMARY KEY (`scr_id`),
  UNIQUE KEY `scr_secrets_idx` (`scr_host`,`scr_user`),
  KEY `env_environment_scr_secrets_fk` (`env_id`),
  KEY `stp_secret_type_scr_secrets_fk` (`stp_id`),
  KEY `sgr_secrets_groups_scr_secrets_fk` (`sgr_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Structure de la table `sgr_secrets_groups`
--

DROP TABLE IF EXISTS `sgr_secrets_groups`;
CREATE TABLE IF NOT EXISTS `sgr_secrets_groups` (
  `sgr_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sgr_label` varchar(60) DEFAULT NULL,
  `sgr_alert` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sgr_id`),
  UNIQUE KEY `sgr_label` (`sgr_label`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Structure de la table `spr_system_parameters`
--

DROP TABLE IF EXISTS `spr_system_parameters`;
CREATE TABLE IF NOT EXISTS `spr_system_parameters` (
  `spr_name` varchar(30) CHARACTER SET latin1 NOT NULL,
  `spr_value` varchar(60) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`spr_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `stp_secret_types`
--

DROP TABLE IF EXISTS `stp_secret_types`;
CREATE TABLE IF NOT EXISTS `stp_secret_types` (
  `stp_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `stp_name` varchar(30) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`stp_id`),
  UNIQUE KEY `stp_secret_types_idx` (`stp_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
