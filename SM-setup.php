<?php

if ( ! file_exists( DIR_SESSION ) ) {
    if ( @mkdir( DIR_SESSION, 0771 ) ) {
        print( 'Directory "' . DIR_SESSION . '" created' . "\n" );
    } else {
        print( 'Directory "' . DIR_SESSION . '" error during creation' . "\n" );
    }
}

if ( ! file_exists( DIR_LIBRARIES ) ) {
    if ( @mkdir( DIR_LIBRARIES, 0771 ) ) {
        print( 'Directory "' . DIR_LIBRARIES . '" created' . "\n" );
    } else {
        print( 'Directory "' . DIR_LIBRARIES . '" error during creation' . "\n" );
    }
}

if ( ! file_exists( DIR_LABELS ) ) {
    if ( @mkdir( DIR_LABELS, 0771 ) ) {
        print( 'Directory "' . DIR_LABELS . '" created' . "\n" );
    } else {
        print( 'Directory "' . DIR_LABELS . '" error during creation' . "\n" );
    }
}

if ( ! file_exists( DIR_RADIUS ) ) {
    if ( @mkdir( DIR_RADIUS, 0771 ) ) {
        print( 'Directory "' . DIR_RADIUS . '" created' . "\n" );
    } else {
        print( 'Directory "' . DIR_RADIUS . '" error during creation' . "\n" );
    }
}

if ( ! file_exists( DIR_PICTURES ) ) {
    if ( @mkdir( DIR_PICTURES, 0771 ) ) {
        print( 'Directory "' . DIR_PICTURES . '" created' . "\n" );
    } else {
        print( 'Directory "' . DIR_PICTURES . '" error during creation' . "\n" );
    }
}

if ( ! file_exists( DIR_BACKUP ) ) {
    if ( @mkdir( DIR_BACKUP, 0777 ) ) {
        print( 'Directory "' . DIR_BACKUP . '" created' . "\n" );
    } else {
        print( 'Directory "' . DIR_BACKUP . '" error during creation' . "\n" );
    }
}

?>