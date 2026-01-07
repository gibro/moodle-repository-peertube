# PeerTube Repository Plugin für Moodle

Dieses Plugin ermöglicht es, Videos von einer PeerTube-Instanz direkt in Moodle zu suchen und einzubetten.

## Installation

1. Kopiere den Plugin-Ordner `peertube` in das Verzeichnis `moodledata/repository/` Deiner Moodle-Installation.
2. Führe die Moodle-Installation/Upgrade-Routine aus.
3. Aktiviere das Plugin in den Repository-Einstellungen.

## Konfiguration

1. Gehe zu **Website-Administration > Plugins > Repositories > PeerTube videos**
2. Gebe die **PeerTube Instance URL** ein (z.B. `https://peertube.example.com`)
3. Gebe Deinen **Access Token** ein. Den bekommst du mit diesem Befehl:

  curl -X POST \
-d "client_id=Is6jenqfqprnxtsbikucz8zr7jjyae7ly&client_secret=IHR_CLIENT_SECRET&grant_type=password&response_type=code&username=IHR_BENUTZERNAME&password=IHR_PASSWORT" \
https://peertube.example.com/api/v1/users/token

## Video-Einbettung in Moodle

Um ein PeerTube-Video direkt in Moodle einzubetten:

1. Erstelle eine **Link/URL-Aktivität** in Deinem Moodle-Kurs
2. Klicke auf **"Link auswählen"**
3. Wähle **"PeerTube videos"** als Quelle
4. Suche nach dem gewünschten Video
5. Wähle das Video aus
6. Unter **"Darstellung"** wähle **"Einbetten"** aus
7. Speichere die Aktivität

Das Video wird nun direkt in Moodle eingebettet und abgespielt.

## Unterstützte Funktionen

- Suche nach Videos auf der PeerTube-Instanz
- Zugriff auf alle Videos, auf die der Benutzer (jeder Nutzer hat einen anderen token, es gitb keine peertubeweiten, nutzendenunanhängigen token) Berechtigung hat (inklusive unlisted Videos)
- Sortierung nach Datum, Views, Likes
- Embed-URLs für direkte Einbettung

## Anforderungen

- Moodle 4.0 oder höher
- Eine PeerTube-Instanz mit aktivierter API
- Ein gültiger Access Token mit entsprechenden Berechtigungen
