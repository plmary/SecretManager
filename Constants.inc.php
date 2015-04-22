<?php

/**
* Définit les constantes indispensables à la connaissance de l'architecture de SecretManager (position relative à l'installation de SecretManager).
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @date 2014-12-11
*
*/

define( 'SERVER', 'ihm.secretmanager.fr' );
define( 'APPLICATION_PATH', realpath( dirname( __FILE__ ) ) );

define( 'URL_BASE', 'https://' . SERVER );
define( 'URL_PICTURES', 'https://' . SERVER . '/Pictures');
define( 'URL_LIBRARIES', 'https://' . SERVER . '/Libraries' );

define( 'DIR_SESSION',   APPLICATION_PATH . DIRECTORY_SEPARATOR . 'Temp' );
define( 'DIR_LIBRARIES', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'Libraries' );
define( 'DIR_LABELS',    APPLICATION_PATH . DIRECTORY_SEPARATOR . 'Libraries' . DIRECTORY_SEPARATOR . 'Labels' );
define( 'DIR_RADIUS',    APPLICATION_PATH . DIRECTORY_SEPARATOR . 'Libraries' . DIRECTORY_SEPARATOR . 'Radius' );
define( 'DIR_PICTURES',  APPLICATION_PATH . DIRECTORY_SEPARATOR . 'Pictures' );
define( 'DIR_BACKUP',    APPLICATION_PATH . DIRECTORY_SEPARATOR . 'Backup' );
define( 'DIR_PROTECTED', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'Libraries' . DIRECTORY_SEPARATOR . 'Protected' );

define( 'FLAG_ERROR', 0 );
define( 'FLAG_SUCCESS',  1 );

define( 'IICA_DB_CONFIG', DIR_PROTECTED . DIRECTORY_SEPARATOR . 'Config_Access_DB.inc.php' );
define( 'IICA_LIBRARIES', DIR_LIBRARIES );
define( 'IICA_PICTURES', DIR_PICTURES );
define( 'IICA_LABELS', DIR_LABELS );

define( 'MAIL_BODY', DIR_LIBRARIES . DIRECTORY_SEPARATOR . 'Mail_Body.dat' );
define( 'SYSLOG_BODY', DIR_LIBRARIES . DIRECTORY_SEPARATOR . 'Syslog_Body.dat' );

define( 'INTEGRITY_FILENAME', DIR_PROTECTED . DIRECTORY_SEPARATOR . 'files_integrity.dat' );
define( 'MASTER_INTEGRITY_FILENAME', DIR_PROTECTED . DIRECTORY_SEPARATOR . 'file_integrity.dat' );

define( 'CONSTRAINTS_DB_FILENAME', 'Installation' . DIRECTORY_SEPARATOR . 'Secret_Manager-1.sql' );

define( 'FILE_AUTHORIZED_CLIENT_LIST', DIR_PROTECTED . 'Authorized_Client_List.dat' );
?>
