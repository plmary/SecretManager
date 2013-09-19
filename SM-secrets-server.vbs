Set oWShell = CreateObject("Wscript.Shell")
oWShell.Run """{SecretManager}SM-secrets-server.bat""", 0, False
Set oWSHell = Nothing