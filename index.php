<?php

/**
* Ce script est exécuté par défaut et oblige l'utilisateur à passer en HTTPS.
*
* PHP version 5.4
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
* @date 2013-07-09
*
*/

include( 'Constants.inc.php' );

session_save_path( DIR_SESSION );

$Server = $_SERVER[ 'SERVER_NAME' ];
$Script = $_SERVER[ 'SCRIPT_NAME' ];

header( 'Location: https://' . $Server . dirname( $Script ) . '/SM-login.php' );

?>