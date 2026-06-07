# UniFi Protect Alarmprofile

Diese Instanz liest die in UniFi Protect angelegten Alarmprofile (Arm Profiles) aus und erlaubt es,
ein Profil scharf bzw. unscharf zu schalten.

> Hinweis: Alarmprofile sind nur verfügbar, wenn der lokale Alarm-Manager von UniFi Protect verwendet wird.

## Voraussetzungen

- Eine konfigurierte **UnifiProtectGateway**-Instanz (Server-Adresse + API-Key).

## Funktionsweise

Die Instanz wird als Gerät unter dem UnifiProtectGateway angelegt und greift über das Gateway auf die
offizielle UniFi Protect Integration API (`/proxy/protect/integration/v1`) zu:

| Aktion | API |
| --- | --- |
| Profile auslesen | `GET /arm-profiles` |
| Aktuelles Profil setzen | `PATCH /arm-profiles/settings` |
| Alarm scharf schalten | `POST /arm-profiles/enable` |
| Alarm unscharf schalten | `POST /arm-profiles/disable` |

## Statusvariablen

| Variable | Typ | Beschreibung |
| --- | --- | --- |
| `Alarmprofil` (`CurrentProfile`) | String (Auswahl) | Auswahl des aktuell zu verwendenden Alarmprofils. Bei Änderung wird das Profil sofort gesetzt. |
| `Alarm scharf` (`Armed`) | Boolean (Schalter) | Schaltet den Alarm mit dem gewählten Profil scharf bzw. unscharf. |

Beim Scharfschalten wird zuvor automatisch das gewählte Profil als aktuelles Profil gesetzt.

## Konfiguration

- **Aktualisierungsintervall (s):** Optionales Intervall, in dem die Profilliste neu eingelesen wird (0 = aus).
- **Profile auslesen:** Liest die verfügbaren Alarmprofile vom Controller und aktualisiert die Auswahl.

> Die UniFi Protect API liefert keinen Status zurück, welches Profil aktuell ausgewählt bzw. ob der Alarm
> scharf ist. Der angezeigte Zustand entspricht daher dem zuletzt über diese Instanz gesetzten Wert.
