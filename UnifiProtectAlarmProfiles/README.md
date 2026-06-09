# UniFi Protect Alarmprofile

Diese Instanz liest die in UniFi Protect angelegten Alarmprofile (Arm Profiles) aus, zeigt den
aktuellen Arm-Zustand (ArmMode) des NVR an und erlaubt es, den Alarm scharf bzw. unscharf zu schalten.

> Hinweis: Alarmprofile sind nur verfügbar, wenn der lokale Alarm-Manager von UniFi Protect verwendet wird.

## Voraussetzungen

- Eine konfigurierte **UnifiProtectGateway**-Instanz (Server-Adresse + API-Key).

## Funktionsweise

Die Instanz wird als Gerät unter dem UnifiProtectGateway angelegt und greift über das Gateway auf die
offizielle UniFi Protect Integration API (`/proxy/protect/integration/v1`) zu:

| Aktion | API |
| --- | --- |
| Profile auslesen | `GET /arm-profiles` |
| Arm-Status auslesen | `GET /nvrs` (Feld `armMode`) |
| Aktuelles Profil setzen | `PATCH /arm-profiles/settings` |
| Alarm scharf schalten | `POST /arm-profiles/enable` |
| Alarm unscharf schalten | `POST /arm-profiles/disable` |

## Statusvariablen

| Variable | Typ | Beschreibung |
| --- | --- | --- |
| `Alarmprofil` (`CurrentProfile`) | String (Auswahl) | Auswahl des zu verwendenden Alarmprofils. Bei Änderung wird das Profil sofort gesetzt. |
| `Alarm scharf` (`Armed`) | Boolean (Schalter) | Schaltet den Alarm scharf bzw. unscharf. |
| `Alarm-Status` (`ArmStatus`) | String | Aktueller ArmMode-Status (`disabled`, `arming`, `armed`, `disarming`, `breach`). |
| `Aktives Profil` (`ActiveProfile`) | String | Aktuell auf dem Controller aktives Profil (Name). |
| `Scharf seit` (`ArmedAt`) | Integer (Zeitstempel) | Zeitpunkt des Scharfschaltens. |
| `Auslösung erkannt` (`Breach`) | Boolean | Zeigt an, ob eine Alarm-Auslösung erkannt wurde. |

### Scharfschalten

- Beim Scharfschalten wird zunächst das gewählte Profil als aktuelles Profil gesetzt.
- Anschließend wird der Alarm aktiviert – **nur** wenn der aktuelle ArmMode-Status `disabled` ist
  (Vorgabe der UniFi Protect API). Andernfalls bleibt der Zustand unverändert und es wird eine
  Warnung im Log ausgegeben.

## Konfiguration

- **Aktualisierungsintervall (s):** Intervall, in dem der ArmMode-Status (`/nvrs`) neu eingelesen wird (0 = aus).
- **Profile auslesen:** Liest die verfügbaren Alarmprofile vom Controller und aktualisiert die Auswahl.
- **Status aktualisieren:** Liest den aktuellen ArmMode-Status sofort neu ein.
