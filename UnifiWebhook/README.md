# UnifiWebhook
Löst in UniFi Protect hinterlegte Webhooks (Alarm-Manager) direkt aus IP-Symcon aus — je konfiguriertem Webhook wird eine Auslöse-Variable angelegt.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [Visualisierung](#6-visualisierung)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Auslösen von UniFi-Protect-Webhooks (Alarm-Manager) aus IP-Symcon
* Eine Auslöse-Variable (Button) je konfiguriertem Webhook
* HTML-Kachel für die Kachel-Visualisierung mit einem Button je Webhook

### 2. Voraussetzungen

- IP-Symcon ab Version 8.2
- Eine konfigurierte **UnifiProtectGateway**-Instanz (Server-Adresse + API-Key)
- In UniFi Protect angelegte Webhooks (Alarm-Manager), deren IDs bekannt sind

### 3. Software-Installation

* Über den Module Store das 'UnifiProtect'-Modul installieren.

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'UnifiWebhook'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name     | Beschreibung
-------- | ------------------
Webhooks | Liste der Webhooks: je Eintrag ein Name (frei wählbar) und die Webhook-ID aus UniFi Protect. Achtung: Bei Änderung von Name oder ID werden die Variablen neu angelegt (neue Variablen-IDs)!
Anzeigename der Kachel (optional) | Überschreibt nur den Titel der HTML-Kachel; die Instanz wird nicht umbenannt

### 5. Statusvariablen und Profile

Die Statusvariablen werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

Name   | Typ     | Beschreibung
------ | ------- | ------------
\<Webhook-Name\> | Integer (Button) | Je konfiguriertem Webhook eine Variable; Auslösen per Klick oder `RequestAction($VariablenID, 1)`

#### Profile

keine vorhanden

### 6. Visualisierung

Die Instanz bringt eine HTML-Kachel mit (Kachel-Visualisierung, ab IP-Symcon 8.2): ein Button je Webhook zum direkten Auslösen.

### 7. PHP-Befehlsreferenz

Das Auslösen erfolgt über die Standard-Funktion `RequestAction` auf der jeweiligen Webhook-Variable:

`RequestAction(integer $VariablenID, 1);`
