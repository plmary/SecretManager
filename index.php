<?php

include( 'Constants.inc.php' );

session_save_path( DIR_SESSION );

$Server = $_SERVER[ 'SERVER_NAME' ];
$Script = $_SERVER[ 'SCRIPT_NAME' ];

header( 'Location: https://' . $Server . dirname( $Script ) . '/SM-login.php' );

?>