SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=1;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

GRANT USAGE ON *.* TO 'iica_user'@'localhost' IDENTIFIED BY PASSWORD '*5E59D2AFD76D20C92203C039B9F0D6F0013EA9A0';

GRANT SELECT, INSERT, UPDATE, DELETE ON `Secret\_Manager`.* TO 'iica_user'@'localhost';
