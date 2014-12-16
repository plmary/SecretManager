# Auteur : Pierre-Luc MARY
# Date   : 2014-10-01
# Objet  : Ce script met en place la sécurité sur les différents fichiers et répertoires de SecretManager.
# Pré-requis 1 : l'utilisateur SecretManager doit être créé
# Pré-requis 2 : le serveur Apache doit s'exécuter en tant qu'utilisateur SecretManager

# Teste que le script s'exécute à partir du bon répertoire.
if [ ! -d '../../SecretManager' ]
then
echo "%ERROR, you're not in SecretManager directory";
return;
fi

# Passe tous les fichiers et répertoires sous la propriété de SecretManager
sudo chown -R secretmanager ../

# Passe tous les répertoires en lecture seule.
sudo find ../ -type d -exec chmod 750 {} \;

# Gère les 2 répertoires particuliers (pour lesquels il faut plus de droits).
sudo chmod 770 ../Backup;
sudo chmod 770 ../Temp;

# Passe tous les répertoire en lecture seule.
sudo find ../ -type f -exec chmod 740 {} \;

# Gère les fichiers de données qui peuvent être modifiés par le SecretManager.
sudo chmod 760 ../Libraries/secret.dat;
sudo chmod 760 ../Libraries/Mail_Body.dat;
sudo chmod 760 ../Libraries/secret.dat;
sudo chmod 760 ../Libraries/Conf*.php;
