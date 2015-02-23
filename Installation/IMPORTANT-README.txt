Date : 2014-12-15
Author : Pierre-Luc MARY

Contact me : mailto:pl.mary(at)orasys.fr
Bug track ticket : https://sourceforge.net/p/secretmanager/tickets/?source=navbar

FR ]
Quelles nouvelles :
- La nouvelle "machine virtuelle" a été mis à votre disposition.
Voir le lien ci-dessous :
https://sourceforge.net/projects/secretmanager/files/VM-SecretManager_v0.10-1.zip/download
Important : Un guide d'installation et d'utilisation ont été rédigés.

- Cette version implémente la gestion de l'historique des Secrets.
L'historique permet à un Administrateur de pouvoir utiliser un ancien mot de passe suite à
la reconstruction d'une ancienne application ou ancien serveur.
- Elle implémente également un concept de connexion en cascade pour l'utilisateur "root".
Ce principe permet à un utilisateur "root" de pouvoir se connecter suite à un problème
avec son LDAP ou son RADIUS.
Pour ce faire, il tape le mot de passe qu'il utilisait avant la bascule vers le LDAP ou le
RADIUS.

Plusieurs petites corrections :
- Correction d'un problème lors de la création des "Groupes de Secrets" ;
- Réécriture du processus de restauration (qui ne fonctionnait pas bien avec la mise en 
place des contraintes d'intégrité dans la base de données) ;

------------------------
Première installation :

Déziper "SecretManager_v0.10-0.zip" dans le "DocumentRoot" de 
votre Serveur Apache (voir la configuration de votre httpd-vhosts.conf). Ensuite, lire le 
"Guide d'installation" dans le répertoire "Documentations" dans l'arborescence de 
SecretManager.

----------------------------------
Mise à jour de v0.9-1 à v0.10-0 :

Déziper "upd_SecretManager_v0.10-0.zip" dans le précédent répertoire d'installation de 
votre "SecretManager".
*** Attention : il faut mettre à jour la base de données "Secret_Manager" par l'exécution
du script suivant :
- upd-1-v0.10-0-SecretManager.sql Ce script est dans le répertoire "Installation" comme
tous les scripts SQL.

===================================

[ EN ] What news: 
- The new "virtual machine" has been made available. See the link above. An installation 
and use guide has written. 
- This version implements the management of history Secrets. The history allows an 
Administrator can use an old password following the reconstruction of an old application 
or old server. 
- It also implements a cascade connection concept for the "root" user. This principle 
allows a "root" user to be able to connect due to a problem with the LDAP or RADIUS. To do
this, type the password he used before switching to LDAP or RADIUS.

--------------------
First installation: 
Unzip "SecretManager_v0.10-0.zip" in the "DocumentRoot" your Apache Server (see your 
httpd-vhosts.conf).
Then read the "Installation Guide" in the "Documents" folder in the tree SecretManager.

--------------------------
Update v0.9-1 to v0.10-0: 
Unzip "upd_SecretManager_v0.10-0.zip" in your previous installation "SecretManager"
directory. 

*** Warning: you must update the "Secret_Manager" database by executing the following 
script:
- Upd-1-v0.10-0-SecretManager.sql

Show the "Installation" directory.

*** ========================================================== ***

Important "hosts" file must contain the following line : 127.0.0.1 secretmanager.local 
"httpd-vhosts.conf" file must contain the following lines (carefull, it's an exemple.

Adjust the locations 'D:/xampp/htdocs' and 'D:\xampp\apache\conf' as your context) : 

<VirtualHost secretmanager.local:443>
 ServerName secretmanager.local
 ServerAlias secretmanager 

 DocumentRoot D:/xampp/htdocs/SecretManager 

 SSLEngine on SSLCertificateFile D:\xampp\apache\conf\ssl.crt\server.crt 
 SSLCertificateKeyFile D:\xampp\apache\conf\ssl.key\server.key 

 ServerAdmin orasys@orasys.fr

 <Directory D:/xampp/htdocs/SecretManager> 
  DirectoryIndex index.php 
  AllowOverride All 
  Order allow,deny 
  Allow from all 
 </Directory>

 CustomLog logs/ssl_request_log "%t %h %{SSL_PROTOCOL}x %{SSL_CIPHER}x \"%r\" %b" 
</VirtualHost> 


Start SecretServer in background : 
Windows users, I've initiate "SM-secrets-server.vbs" and "SM-secrets-server.bat" (ajust 
them. Implement the Environment variable "SecretManager") 

Linux users, you can start SecretServer whith a simple 
"sudo -b /Applications/xampp/htdocs/SecretManager/SM-secrets-server.php" 

If it's your SecretManager first start, in "Preferences management" and in "SecretServer" 
tab, you can load the mother key and using the default operator key "CleO". 
After, you must change "operator key" and "mother key" on your product computer. 

Have fun !!!