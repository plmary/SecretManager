<?php

/**
* Définit les constantes indispensables à la connaissance de l'architecture de SecretManager (position relative à l'installation de SecretManager).
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.1
* @date 2013-08-13
*
*/

define( 'SERVER', 'secretmanager.localhost' );
define( 'APPLICATION_PATH', realpath( dirname( __FILE__ ) ) );

define( 'URL_BASE', 'https://' . SERVER );
define( 'URL_PICTURES', 'https://' . SERVER . '/Pictures');
define( 'URL_LIBRARIES', 'https://' . SERVER . '/Libraries' );

define( 'DIR_SESSION',   APPLICATION_PATH . '/Temp' );
define( 'DIR_LIBRARIES', APPLICATION_PATH . '/Libraries' );
define( 'DIR_LABELS',    APPLICATION_PATH . '/Libraries/Labels' );
define( 'DIR_RADIUS',    APPLICATION_PATH . '/Libraries/Radius' );
define( 'DIR_PICTURES',  APPLICATION_PATH . '/Pictures' );
define( 'DIR_BACKUP',    APPLICATION_PATH . '/Backup' );

define( 'FLAG_ERROR', 0 );
define( 'FLAG_SUCCESS',  1 );

define( 'IICA_DB_CONFIG', DIR_LIBRARIES . '/Config_Access_DB.inc.php' );
define( 'IICA_LIBRARIES', DIR_LIBRARIES );
define( 'IICA_PICTURES', DIR_PICTURES );
define( 'IICA_LABELS', DIR_LABELS );

define( 'MAIL_BODY', DIR_LIBRARIES . "/Mail_Body.dat" );

?>