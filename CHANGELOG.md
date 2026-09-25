# Changelog

## Version 2.0 (2026-08-04)

### Echtzeit-Updates per WebSocket (Breaking Change)

Das Gateway wurde von einer I/O- in eine Splitter-Instanz umgebaut. Geräte-Updates kommen jetzt in Echtzeit über den WebSocket-Endpunkt `/subscribe/devices` der Protect Integration API statt per zyklischem Polling.

**Neu:**

- Gateway nutzt den nativen "WS Client" von IP-Symcon als übergeordnete Instanz. Dieser wird beim Übernehmen der Gateway-Einstellungen automatisch angelegt und konfiguriert (URL, API-Key-Header, Zertifikatsprüfung) — keine manuelle Einrichtung nötig.
- Geräte-Instanzen (Kameras, Sensoren, Chimes, Lights, Speaker) erhalten Änderungen sofort per Push und schreiben nur die tatsächlich geänderten Felder.
- Watchdog im Gateway: überwacht die WebSocket-Verbindung, stößt bei Abriss automatisch einen Reconnect an und löst nach Wiederverbindung einen Vollabgleich aller Geräte aus.
- Interner Fallback-Abgleich pro Gerät alle 60 Minuten, um verpasste WebSocket-Updates (z.B. nach Downtime) abzufangen.

- Neuer Gerätetyp "Vape/Air Quality Sensor" (UP-AirQuality): Temperatur und Luftfeuchte sowie — sobald die Protect Integration API sie bereitstellt — AQI, Vape-Index, TVOC, VOC, CO2 und Feinstaub (PM1.0/PM2.5/PM4.0/PM10), jeweils per Checkbox aktivierbar. Der Konfigurator erkennt den Sensortyp automatisch.

**Geändert:**

- Geräte-Instanzen: Die Timer-Eingabe (Abfrageintervall) wurde entfernt und durch eine Checkbox "Aktiv (Updates per WebSocket)" ersetzt (Standard: aktiv). Der Snapshot-Timer für Kameras bleibt unverändert.
- Durch den Wegfall des Pollings sinkt die Last auf der Protect-API deutlich (Rate-Limit-Probleme bei vielen Geräten entfallen).

**Update-Hinweis:**

Nach dem Update auf 2.0 ist einmalig ein Neustart von IP-Symcon erforderlich (Wechsel des Instanz-Typs). Danach legt das Gateway den WebSocket Client automatisch an. Bestehende Geräte-Instanzen und deren Variablen bleiben erhalten; das bisherige Timer-Intervall entfällt ersatzlos.
