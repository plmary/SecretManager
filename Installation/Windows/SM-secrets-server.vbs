' Auteur : Pierre-Luc MARY
' Date : 2014-12-15
' Objet : lance le SecretServer en tache de fond sous Windows.
' Pré-requis : la variable d'environnement 'SecretManager' doit être défini et doit pointer sur le répertoire du SecretManager.

Set objShell = CreateObject("Wscript.Shell")
'Wscript.Echo objShell.CurrentDirectory
objShell.CurrentDirectory = objShell.ExpandEnvironmentStrings( "%SecretManager%" )
'Wscript.Echo objShell.CurrentDirectory
objShell.Run "SM-secrets-server.bat", 0, False
Set objSHell = Nothing