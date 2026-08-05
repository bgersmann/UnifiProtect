# UnifiProtect

Anbindung von UniFi Protect an IP-Symcon über die lokale Protect Integration API.
Geräte-Updates werden seit Version 2.0 in Echtzeit per WebSocket empfangen — zyklisches Polling ist nur noch als Fallback aktiv.

Folgende Module beinhaltet das UnifiProtect Repository:

- __UnifiProtectGateway__ ([Dokumentation](UnifiProtectGateway))  
Die Schnittstelle zwischen Symcon und der lokalen Unifi Protect API. Arbeitet als Splitter und nutzt den nativen WebSocket Client von IP-Symcon für Echtzeit-Updates.

- __UnifiProtectDevice__ ([Dokumentation](UnifiProtectDevice))  
Dieses Modul repräsentiert die Unifi Protect Geräte in IP-Symcon.

- __UnifiProtectEvents__ ([Dokumentation](UnifiProtectEvents))  
Empfängt und verarbeitet Ereignisse (z.B. Bewegung, smarte Erkennung, Sensor-Events) von UniFi Protect.

- __UnifiProtectConfigurator__ ([Dokumentation](UnifiProtectConfigurator))  
- UnifiWebsocket ([Dokumentation](UnifiWebsocket))
Modul zur komfortablen Auswahl und Instanz-Erstellung der verfügbaren UniFi Protect Geräte.