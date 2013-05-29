<?php

// Eléments d'authentification LDAP
$ldap_organization = 'dc=orasys,dc=fr';
$ldap_rdn_prefix = 'uid';
$ldap_user = 'root';
$ldap_pass = 'pipota';    // Mot de passe associé
$ldap_rdn  = $ldap_rdn_prefix . '=' . $ldap_user . ',' . $ldap_organization;     // DN ou RDN LDAP

$ldap_host = 'localhost'; // Nom du Host ou adresse IP
$ldap_port = 10389;       // Numéro de port IP
$ldap_protocol_version = 3;

// Connexion au serveur LDAP
$ldap_c = ldap_connect( $ldap_host, $ldap_port );
if ( $ldap_c === FALSE ) {
	print( "Impossible de se connecter au serveur LDAP.<br/>" .
	 ldap_error( $ldap_c ) . ' (' . ldap_errno( $ldap_c ) . ')'
	);

	exit();
}

	 
if ( ldap_set_option( $ldap_c, LDAP_OPT_PROTOCOL_VERSION, $ldap_protocol_version ) === FALSE ) {
	print( "Impossible de changer la version du protocole.<br/>" .
	 ldap_error( $ldap_c ) . ' (' . ldap_errno( $ldap_c ) . ')'
	);

	exit();
}
 

if ($ldap_c) {
    // Connexion au serveur LDAP
    $ldap_b = ldap_bind($ldap_c, $ldap_rdn, $ldap_pass);

    // Vérification de l'authentification
    if ($ldap_b) {
        echo "Connexion LDAP réussie...";
    } else {
		print( "Connexion LDAP échouée...<br/>" .
		 ldap_error( $ldap_c ) . ' (' . ldap_errno( $ldap_c ) . ')'
		);

		exit();
    }
}



?>