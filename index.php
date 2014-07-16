<?php

/**
* Ce script est exécuté par défaut et oblige l'utilisateur à passer en HTTPS.
*
* PHP version 5.4
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @date 2013-07-28
*
*/

include( 'Constants.inc.php' );

session_save_path( DIR_SESSION );

header( 'Location: ' . URL_BASE . '/SM-login.php' );

?>