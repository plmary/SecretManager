<?php

// Eléments d'authentification LDAP
$ldap_organization = 'cn=proxyuser,cn=users,dc=atix,dc=de';
$ldap_rdn_prefix = 'uid'; // "uid" or "sn"
$ldap_user = 'pascal'; // your username in LDAP
$ldap_pass = 'your_famous_password';    // your password in LDAP

$ldap_rdn  = $ldap_rdn_prefix . '=' . $ldap_user . ',' . $ldap_organization;     // DN ou RDN LDAP

$ldap_host = 'localhost'; // Hostname or IP adress
$ldap_port = 10389;       // IP port number (10389 is the default)
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