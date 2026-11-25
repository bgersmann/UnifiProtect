# UnifiProtect

Anbindung von UniFi Protect an IP-Symcon über die lokale Protect Integration API.
Geräte-Updates werden seit Version 2.0 in Echtzeit per WebSocket empfangen — zyklisches Polling ist nur noch als Fallback aktiv.

Folgende Module beinhaltet das UnifiProtect Repository:

- __UnifiProtectGateway__ ([Dokumentation](UnifiProtectGateway/README.md))  
Die Schnittstelle zwischen Symcon und der lokalen Unifi Protect API. Arbeitet als Splitter und nutzt den nativen WebSocket Client von IP-Symcon für Echtzeit-Updates.

- __UnifiProtectDevice__ ([Dokumentation](UnifiProtectDevice/README.md))  
Dieses Modul repräsentiert die Unifi Protect Geräte in IP-Symcon.

- __UnifiProtectEvents__ ([Dokumentation](UnifiProtectEvents/README.md))  
Empfängt und verarbeitet Ereignisse (z.B. Bewegung, smarte Erkennung, Sensor-Events) von UniFi Protect.

- __UnifiProtectConfigurator__ ([Dokumentation](UnifiProtectConfigurator/README.md))  
Modul zur komfortablen Auswahl und Instanz-Erstellung der verfügbaren UniFi Protect Geräte.

- __UnifiProtectAlarmProfiles__ ([Dokumentation](UnifiProtectAlarmProfiles/README.md))  
Liest die Alarmprofile (Arm Profiles) aus, zeigt den Arm-Status an und schaltet den Alarm scharf/unscharf.

- __UnifiWebhook__ ([Dokumentation](UnifiWebhook/README.md))  
Löst in UniFi Protect hinterlegte Webhooks (Alarm-Manager) direkt aus IP-Symcon aus.

Optional kann das Gateway zusätzlich die inoffizielle Protect-API nutzen (Anmeldung mit lokalem UniFi-Benutzer): Kameras erhalten damit einen Privatsphäre-Schalter und die Auswahl des Aufzeichnungs-Modus.

Alle Geräteinstanzen bringen eine eigene HTML-Kachel für die Kachel-Visualisierung mit (ab IP-Symcon 8.2, Design an die Visualisierung angelehnt). Der Kachel-Titel kann je Instanz über das Feld "Anzeigename der Kachel" überschrieben werden, ohne die Instanz umzubenennen.