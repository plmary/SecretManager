<?php
if ( array_key_exists('HTTP_HOST', $_SERVER) ) {
	header( 'Location: index.html' );
	exit(1);
}

include_once( 'Constants.inc.php' );
include_once( DIR_LIBRARIES . '/Class_Security.inc.php' );

include( DIR_LABELS . '/en_SM-secrets-server.php' );
include( DIR_LABELS . '/en_labels_generic.php' );

$Security = new Security();

$Tmp = $Security->checkFilesIntegrity( TRUE );
if ( $Tmp[0] === FALSE ) {
	print( "%ERR, error in create '" . INTEGRITY_FILENAME . "\n" );
	foreach ($Tmp[1] as $key => $value) {
		print( "'" . $Key . "'' is corrupted\n" );
	}
	exit();
}

$Security->checkMasterFileIntegrity( TRUE );
if ( $Tmp[0] === FALSE ) {
	print( "%ERR, error in create '" . MASTER_INTEGRITY_FILENAME . "\n" );
	exit();
}


?>