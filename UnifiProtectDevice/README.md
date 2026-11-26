# UnifiProtectDevice
Repräsentiert ein UniFi Protect Gerät (Kamera, Sensor, Chime, Light, Speaker) in IP-Symcon.
Seit Version 2.0 werden die Gerätedaten in Echtzeit per WebSocket aktualisiert; ein interner Timer gleicht zusätzlich stündlich den kompletten Gerätestand ab, um verpasste Updates (z.B. nach Downtime) abzufangen.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

- Integration von UniFi Protect Geräten (Kameras, Sensoren, Vape-/Luftqualitätssensoren, Chimes, Lights, Speaker) in IP-Symcon
- Geräte-Updates in Echtzeit per WebSocket (kein Polling-Intervall mehr nötig)
- Interner Fallback-Abgleich alle 60 Minuten für verpasste WebSocket-Updates
- Abruf und Steuerung von Streams und Snapshots
- Verwaltung von Geräte-Einstellungen und Status
- Ereignis- und Bewegungsmeldungen

### 2. Voraussetzungen

- IP-Symcon ab Version 8.2
- UniFi Protect mit aktiviertem API-Zugang und gültigem API-Key

### 3. Software-Installation

* Über den Module Store das 'UnifiProtectDevice'-Modul installieren.

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'UnifiProtectDevice'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name     | Beschreibung
-------- | ------------------
Aktiv (Updates per WebSocket) | Instanz aktivieren. Daten kommen in Echtzeit per WebSocket; zusätzlich läuft ein interner Fallback-Abgleich alle 60 Minuten.
Gerätetyp | Gerätetyp (Kamera/Sensor/Vape-/Luftqualitätssensor/Licht/Gong/Speaker)
Geräte-ID | Gerätename aus Unifi
ID-Anzeigen | ID als Variable anlegen
Stream Niedrig/Mittel/Hoch | Stream in entsprechender Auflösung anlegen (Schaltet auch Stream in Unifi Protect ein/aus!!)
Snapshot-Timer aktiv / Snapshot-Timer (s) | Snapshot der Kamera zyklisch aktualisieren
Snapshot deaktivieren | Für Fremdkameras (z.B. ONVIF), die keinen Snapshot liefern: entfernt Snapshot-Variable, Medienobjekt und Kachel-Buttons
Block "Inoffizielle API" | Einzeln aktivierbare Funktionen der inoffiziellen Protect-API — Kamera: Privatsphäre-Schalter und Aufzeichnungs-Modus; UP-AirQuality: die einzelnen Luftqualitätswerte. Nur bedienbar, wenn die inoffizielle API im Gateway aktiviert und konfiguriert ist
Anzeigename der Kachel (optional) | Überschreibt nur den Titel der HTML-Kachel; Instanz und Gerät werden nicht umbenannt
Temperatur/Luftfeuchtigkeit/… | Je nach Gerätetyp (UP-Sense, UP-AirQuality): Messwert-Variablen per Checkbox aktivieren

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

| Name         | Typ      | Beschreibung                |
| ------------ | -------- | --------------------------  |
| Name         | String   | Gerätename                  |
| ID           | String   | Geräte-ID                   |
| Model        | String   | Gerätemodell (modelKey, z.B. "camera") |
| Type         | String   | Hardware-Typ (z.B. "UVC G3"); wird nur angelegt, wenn die Protect-Version das Feld liefert |
| State        | String   | Verbindungsstatus           |
| Is Microphone enabled  | Boolean      | Status Mikrofon |
| Microphone Volume | Integer | Mikrofon Lautstärke |
| Air Quality Index / Vape Index / TVOC / VOC / CO2 / PM1.0 / PM2.5 / PM4.0 / PM10 / NOX | Integer/Float | Luftqualitätswerte des UP-AirQuality. Die Integration API liefert sie derzeit nicht — zum Auslesen die Option "Inoffizielle API nutzen" aktivieren |
| Privacy      | Boolean  | Privatsphäre-Modus der Kamera (inoffizielle API): legt eine bildfüllende Privacy-Zone an, schaltet das Mikrofon stumm und die Aufzeichnung ab; beim Ausschalten werden die vorherigen Werte wiederhergestellt |
| RecordingMode | String  | Aufzeichnungs-Modus der Kamera (inoffizielle API): Adaptiv/Immer/Nie/Zeitplan/nur Ereignisse |

#### Profile

keine vorhanden

### 6. Visualisierung

- Anzeige von Live-Streams und Snapshots
- Steuerung von Geräten und Anzeige von Ereignissen
- Visualisierung von Status und Sensorwerten
- Eigene HTML-Kachel für die Kachel-Visualisierung (ab IP-Symcon 8.2), je nach Gerätetyp:
  - Kamera: Verbindungsstatus, Mikrofon, LCD-Nachricht, Snapshot erstellen sowie Buttons zum Öffnen von Stream und Snapshot (Öffnen-Buttons ab Symcon 8.2-Visualisierung); mit aktivierter inoffizieller API zusätzlich Privatsphäre-Schalter und Aufzeichnungs-Modus-Auswahl
  - Speaker: Status, Lautstärken, Modus
  - UP-Sense: Temperatur, Luftfeuchte, Beleuchtung, Batterie
  - UP-AirQuality: Luftqualitätsindex, CO2, Feinstaub und weitere Messwerte
  - Chime/Light: Verbindungsstatus

### 7. PHP-Befehlsreferenz

`string UNIFIPDV_getData(integer $InstanzID);`
Fordert die aktuellen Gerätedaten vom Controller an und aktualisiert Variablen und Kachel.

`void UNIFIPDV_updateSnapshot(integer $InstanzID);`
Erstellt einen neuen Snapshot der Kamera (nur Gerätetyp Kamera).

`void UNIFIPDV_Send(integer $InstanzID, string $Api, string $Param1);`
Interne Sende-Funktion zum Gateway (z.B. `getStreams`, `getSnapshot`); wird von den Formular-Buttons verwendet.

Beispiel:
`UNIFIPDV_getData(12345);`