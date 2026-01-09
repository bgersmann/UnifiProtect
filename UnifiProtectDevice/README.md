# UnifiProtectDevice
Beschreibung des Moduls.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

- Integration von UniFi Protect Geräten (Kameras, Sensoren, Chimes, Lights) in IP-Symcon
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
Timer    | Abfrage intervall
Gerätetyp | Gerätetyp (Kamera/Sensor/Licht/Gong)
Geräte-ID | Gerätename aus Unifi
ID-Anzeigen | ID als Variable anlegen
Stream Niedrig/Mittel/Hoch | Stream in entsprechender Auflösung anlegen (Schaltet auch Stream in Unifi Protect ein/aus!!)

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

| Name         | Typ      | Beschreibung                |
| ------------ | -------- | --------------------------  |
| Name         | String   | Gerätename                  |
| ID           | String   | Geräte-ID                   |
| Model        | String   | Gerätemodell                |
| State        | String   | Verbindungsstatus           |
| Is Microphone enabled  | Boolean      | Status Mikrofon |
| Microphone Volume | Integer | Mikrofon Lautstärke |

#### Profile

keine vorhanden

### 6. Visualisierung

- Anzeige von Live-Streams und Snapshots
- Steuerung von Geräten und Anzeige von Ereignissen
- Visualisierung von Status und Sensorwerten

### 7. PHP-Befehlsreferenz

`boolean UNIFIPDV_BeispielFunktion(integer $InstanzID);`
Erklärung der Funktion.

Beispiel:
`UNIFIPDV_BeispielFunktion(12345);`