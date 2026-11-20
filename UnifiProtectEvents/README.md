# UnifiProtectEvents
Empfängt Ereignisse von UniFi Protect in Echtzeit per WebSocket (z.B. Bewegung, smarte Erkennung, Klingel, Sensor-Events) und bildet sie als Boolean-Variablen in IP-Symcon ab — global je Ereignistyp und optional je Gerät.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Empfang und Verarbeitung von Ereignissen aus UniFi Protect (z.B. Bewegung, smarte Erkennung, Sensor-Events)
* Automatische Statusvariablen für globale und gerätespezifische Events
* Konfigurierbares Timeout als Sicherheitsnetz: Events ohne Endemeldung (z.B. Controller-Update während eines laufenden Events) werden automatisch beendet
* HTML-Kachel für die Kachel-Visualisierung: Anzahl und Liste der aktiven Ereignisse sowie Chips der aktivierten globalen Ereignistypen

### 2. Voraussetzungen

- IP-Symcon ab Version 8.2
- UniFi Protect mit aktiviertem API-Zugang und gültigem API-Key

### 3. Software-Installation

* Über den Module Store das 'UnifiProtectEvents'-Modul installieren.

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'UnifiProtectEvents'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name     | Beschreibung
-------- | ------------------
Unifi Protect Host IP | IP-Adresse der UniFi Protect Installation
API-Schlüssel         | API Key unter „UniFi Network > Settings > Control Plane > Integrations“ erzeugen
Anzeigename der Kachel (optional) | Überschreibt nur den Titel der HTML-Kachel; die Instanz wird nicht umbenannt
Events ohne Endemeldung automatisch beenden nach (s) | Sicherheitsnetz gegen hängende Event-Variablen, Standard 300 s (0 = aus)
Variable für globale Ereignisse | Je Ereignistyp (Bewegung, smarte Erkennung, Klingel, Sensor-Events, …) eine globale Boolean-Variable anlegen
Geräteereignis-Variablen | Liste aller von der API gemeldeten Geräte/Ereignistypen; per Checkbox wird je Eintrag eine eigene Boolean-Variable angelegt

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

Name             | Typ     | Beschreibung
---------------- | ------- | ------------
motionGlobal     | Boolean | Globale Bewegungserkennung
smartGlobal      | Boolean | Globale smarte Erkennung
sensorGlobal     | Boolean | Globale Sensor-Bewegungserkennung
EventActive_*    | Boolean | Gerätespezifische Ereignisse

#### Profile

keine Vorhanden

### 6. Visualisierung

- Anzeige von aktuellen Ereignissen und deren Status
- Visualisierung von Bewegungs- und Smart-Events
- Übersicht aller aktiven Events

### 7. PHP-Befehlsreferenz

`boolean UNIFIPEV_BeispielFunktion(integer $InstanzID);`
Erklärung der Funktion.

Beispiel:
`UNIFIPEV_BeispielFunktion(12345);`