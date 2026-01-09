# UnifiProtectGateway
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

*  Die Schnittstelle zwischen Symcon und der Local Unifi Protect API

### 2. Voraussetzungen

- IP-Symcon ab Version 8.2
- UniFi Protect mit aktiviertem API-Zugang und gültigem API-Key

### 3. Software-Installation

* Über den Module Store das 'UnifiProtect'-Modul installieren.

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'UnifiProtectGateway'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name     | Beschreibung
-------- | ------------------
Unifi Geräte-IP    | IP Adresse der Unifi Protect installation
API-Schlüssel | API Key "UniFi Protect > Settings > Control Plane > Integrations" erzeugen.
Anwendungsversion anzeigen |  Erzeugt eine Variable mit der aktuellen Unifi Protect Version.


### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

| Name         | Typ      | Beschreibung                |
| ------------ | -------- | --------------------------  |
| Application Version         | String   | Unifi Protect version                  |


#### Profile

Keine vorhanden.


### 6. Visualisierung

keine

### 7. PHP-Befehlsreferenz

bool UNIFIPGW_getProtectVersion(int $InstanzID);
Ruft die aktuelle Protect-Version ab.

Beispiel:
UNIFIPGW_getProtectVersion(123456);