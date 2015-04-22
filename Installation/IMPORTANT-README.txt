Date : 2015-04-02
Author : Pierre-Luc MARY

Contact me : mailto:pl.mary(at)free.fr
Bug track ticket : https://sourceforge.net/p/secretmanager/tickets/?source=navbar

FR ]
Quelles nouvelles :
- La nouvelle "machine virtuelle" a été mis à votre disposition.
Voir le lien ci-dessous :
https://sourceforge.net/projects/secretmanager/files/VM-SecretManager-v0.12-0.zip/download
Important : Un guide d'installation et d'utilisation ont été rédigés pour utiliser la VM.
Pour ce connecter à la VM : user: "root", password: "plmary"

Une correction :
- Correction du problème d'utilisation de clé de chiffrement entre la clé active et la clé utilisé pour
  stocker les anciens secrets.

Nouveauté :
Mise en oeuvre d'une API pour interfacer en création et en modification le SecretManager avec vos
applications.
Modification des écrans ci-dessous :
- Gestion des Utilisateurs (implémentation des utilisateurs de type API) ;
- Gestion de l'écran de paramétrage (nouvel onglet) ;
- Gestion des sauvegardes (prise en compte du nouveau champ dans table idn_identities).

------------------------
Première installation :

Déziper "SecretManager-v0.12-0.zip" dans le "DocumentRoot" de 
votre Serveur Apache (voir la configuration de votre httpd-vhosts.conf). Ensuite, lire le 
"Guide d'installation" dans le répertoire "Documentations" dans l'arborescence de 
SecretManager.

Utilisateur par défaut : Nom utilisateur : "root", Mot de passe : "Welcome !"
Clé opérateur par défaut : "CleO"

----------------------------------
Mise à jour de v0.11-0 à v0.12-0 :

Déziper "upd_SecretManager-v0.12-0.zip" dans le précédent répertoire d'installation de 
votre "SecretManager".

*** Attention : Il y a une mise à jour de base de données à faire.
A partir de la racine de SecretManager, il faut exécuter le fichier :
Installation/upd-1-v0.12-0.sql

===================================

[ EN ] What news: 
- The new "virtual machine" has been made available. See the link above. An installation 
and use guide has written. 
https://sourceforge.net/projects/secretmanager/files/VM-SecretManager-v0.11-0.zip/download

VM user connection: user: "root", password: "orasys"

- This version fixe little bugs.


--------------------
First installation: 
Unzip "SecretManager-v0.11-0.zip" in the "DocumentRoot" your Apache Server (see your 
httpd-vhosts.conf).
Then read the "Installation Guide" in the "Documents" folder in the tree SecretManager.

Default user : Username: "root", Password: "Welcome !"
Default Operator Key : "CleO"

--------------------------
Update v0.10-0 to v0.11-0: 
Unzip "upd_SecretManager-v0.11-0.zip" in your previous installation "SecretManager"
directory. 


*** ========================================================== ***

Important:
"hosts" file must contain the following line : 127.0.0.1 secretmanager.local 
"httpd-vhosts.conf" file must contain the following lines (carefull, it's an exemple.
Adjust the locations 'D:/xampp/htdocs' and 'D:\xampp\apache\conf' as your context) : 

<VirtualHost secretmanager.local:443>
 ServerName secretmanager.local
 ServerAlias secretmanager 

 DocumentRoot D:/xampp/htdocs/SecretManager 

 SSLEngine on SSLCertificateFile D:\xampp\apache\conf\ssl.crt\server.crt 
 SSLCertificateKeyFile D:\xampp\apache\conf\ssl.key\server.key 

 ServerAdmin secretmanager@yourcompagny.com

 <Directory D:/xampp/htdocs/SecretManager> 
  DirectoryIndex index.php index.html
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