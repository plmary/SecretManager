<?php

$Server = $_SERVER[ 'SERVER_NAME' ];
$Script = $_SERVER[ 'SCRIPT_NAME' ];

header( 'Location: https://' . $Server . dirname( $Script ) . '/SM-login.php' );

?>