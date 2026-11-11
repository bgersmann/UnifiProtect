# Changelog

## Version 2.2 (2026-11-05)

### HTML-Kacheln für die Kachel-Visualisierung

Alle Geräteinstanzen liefern jetzt eine eigene HTML-Kachel (HTML-SDK, `GetVisualizationTile`), im Design an die Symcon-Kachelvisualisierung angelehnt (gleicher Ansatz wie im UnifiNet-Modul). Gemeinsame Basis: `libs/tile.html` + Trait `libs/UnifiProtectTile.php`; Updates werden per `UpdateVisualizationValue` live an offene Visualisierungen gepusht.

**Neu:**

- **UnifiProtectDevice:** Kachel je nach Gerätetyp — Kamera (Verbindungsstatus, Mikrofon, LCD-Nachricht, Schnappschuss-Button sowie Buttons zum Öffnen von Kamera-Stream und Schnappschuss-Bild; die Öffnen-Buttons erscheinen nur in Visualisierungen ab Symcon 8.2, da sie `openObject` aus dem HTML-SDK nutzen), Speaker (Status, Lautstärken, Modus), UP-Sense (Temperatur, Luftfeuchte, Beleuchtung, Batterie-Balken), UP-AirQuality (AQI-Ring, CO2, PM2.5, weitere Messwerte), Chime/Light (Verbindungsstatus). Datenquelle sind die ohnehin eintreffenden WebSocket-/REST-Updates.
- **UnifiProtectEvents:** Kachel mit Anzahl und Liste der aktiven Ereignisse (Typ, Gerät, Dauer) sowie Chips für die aktivierten globalen Ereignistypen.
- **UnifiProtectAlarmProfiles:** Kachel mit Alarm-Status (Unscharf/Scharf/Auslösung …), Schalter zum Scharf-/Unscharfschalten (mit Bestätigung durch zweites Tippen), Profil-Auswahl per Chips und "scharf seit".
- **UnifiWebhook:** Kachel mit einem Button je konfiguriertem Webhook zum direkten Auslösen.
- Alle vier Instanztypen: optionales Feld "Anzeigename der Kachel" — überschreibt nur den Titel in der HTML-Kachel, ohne die Instanz oder das Gerät umzubenennen.

## Version 2.1 (2026-10-25)

**Behoben:**

- Warnung "Entry in parameter OPTIONS includes unknown sub-parameters" beim Anlegen von Instanzen über den Konfigurator: Die Variablen-Darstellungen (Value Presentation) in UnifiProtectDevice, UnifiProtectEvents und UnifiProtectAlarmProfiles enthielten die ungültigen Unterparameter "Color" und "ColorDisplay". Gültig sind dort nur Value, Caption, IconActive, IconValue, ColorActive und ColorValue.
- Warnungen "Parameter USAGE_TYPE is not defined for this presentation" / "has invalid value" sowie "Parameter DIGITS ist für diese Darstellung nicht definiert": USAGE_TYPE und DIGITS sind bei der Value Presentation nur für Integer-/Float-Variablen definiert (USAGE_TYPE dort nur 0=Keine, 1=Temperatur) — bei Bool-/String-Variablen und in Enumerationen entfernt; fälschlich als "Temperatur" (1) deklarierte Messwerte (Luftfeuchte, Beleuchtung, Luftqualität, Batterie) auf 0 korrigiert. Die Lautstärke-Slider nutzen jetzt USAGE_TYPE 3 (Lautstärke).

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
- Geräte-Instanzen filtern eingehende Gateway-Broadcasts per ReceiveDataFilter bereits im Kernel auf die eigene Geräte-ID (weniger Overhead bei vielen Geräten).

**Update-Hinweis:**

Nach dem Update auf 2.0 ist einmalig ein Neustart von IP-Symcon erforderlich (Wechsel des Instanz-Typs). Danach legt das Gateway den WebSocket Client automatisch an. Bestehende Geräte-Instanzen und deren Variablen bleiben erhalten; das bisherige Timer-Intervall entfällt ersatzlos.
