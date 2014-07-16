<?php
/**
* Libellé spécifique à la gestion des Préférences.
*
* @warning Ce fichier doit impérativement être au format UTF-8 pour une gestion cohérente des caractères accentués.
*
* @copyright LGPL License 3.0 http://www.gnu.org/copyleft/lesser.html
* @author Pierre-Luc MARY
* @date 2013-03-25
* @version 1.2
*/

	$L_Title = 'Gestión de Preferencias';
	
	$L_Welcome = 'Bienvenido';
	$L_Alerts = 'Alertas';
	$L_Connection = 'Login';
	$L_Welcome_Text = 'Advertencia: los parámetros en las distintas pestañas determinan la seguridad general de SecretManager y pueden causar problemas graves.' ;
	
	$L_Alert_Management = 'Administración de alertas';
	$L_Verbosity_Alert = 'Alertas de verbosidad';
	$L_Alert_Syslog = 'Alerta ascenso a través de Syslog';
	$L_Alert_Mail = 'Ascenso Alerta por correo electrónico';
	$L_Detailed_Verbosity = 'Detallado'; // Verbosité détaillée remontée
	$L_Technical_Verbosity = 'Técnico'; // Verbosité technique remontée
	$L_Normal_Verbosity = 'Par'; // Verbosité intelligible remontée
	$L_Language_Alerts = 'Alertas de Idiomas';	

	$L_Parameter_Updated = 'Parámetro de actualización';
	$L_Parameters_Updated = 'Parámetros actualizado';

	$L_SecretServer_Keys = 'Asegurar las claves utilizadas por secretServer';
	$L_Min_Key_Size = 'Tamaño de clave mínimo';
	$L_Key_Complexity = 'La complejidad de la clave';
	$L_Mother_Key = 'Clave de la Madre';
	$L_Operator_Key = 'Clave Operator';

	$L_Mail_From = "Nombre del Emisor";
	$L_Mail_To = "Nombres de los destinatarios deben estar separadas por comas";
	$L_Title_1 = 'Título';
	$L_Mail_Title = 'Título del mensaje';
	$L_Mail_Body = "Cuerpo del mensaje";
	$L_Body = 'Cuerpo';
	$L_Mail_Body_Type = 'Escriba el cuerpo del mensaje';
	$L_Body_Type = 'Tipo de cuerpo';

	$L_Connection_Management = 'Conexión al proceso de gestión';
	$L_Use_Password = 'Uso de contraseñas de autenticación';
	$L_Use_Radius = 'El uso de la autenticación RADIUS';
	$L_Use_LDAP = 'Uso de la autenticación LDAP';

	$L_Min_Size_Password = 'El tamaño mínimo de las contraseñas';
	$L_Password_Complexity = 'La complejidad de las contraseñas';
	$L_Default_User_Lifetime = 'Tiempo de vida de un usuario (en meses)';
	$L_Max_Attempt = 'Número máximo de intentos';
	$L_Default_Password = 'Contraseña por defecto';
	$L_Expiration_Time = 'Antes del tiempo de expiración de sesión (en minutos)';
	$L_Radius_Server = 'Dirección IP del servidor RADIUS';
	$L_Radius_Authentication_Port = 'Puerto de autenticación del servidor Radius';
	$L_Radius_Accounting_Port = 'Puerto de la contabilidad servidor RADIUS';
	$L_Radius_Secret_Common = 'Secreto compartido RADIUS';
	$L_LDAP_Server = 'Dirección IP del servidor LDAP';
	$L_LDAP_Port = 'Puerto del servidor LDAP';
	$L_LDAP_Protocol_Version = 'Versión del protocolo LDAP';
	$L_LDAP_Organization = 'Organización LDAP';
	$L_LDAP_RDN_Prefix = 'RDN prefijo LDAP';
	$L_Testing_Connection = 'Prueba de conexión';
	
	$L_ERR_MAJ_Alert = 'Error al actualizar los parámetros de alertas';
	$L_ERR_MAJ_Connection = 'Error durante la configuración de actualización Login';
?>