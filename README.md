# PeerTube Repository Plugin für Moodle

Dieses Plugin ermöglicht es, Videos von einer PeerTube-Instanz direkt in Moodle zu suchen und einzubetten.

## Installation

1. Kopieren Sie den Plugin-Ordner `peertube` in das Verzeichnis `moodledata/repository/` Ihrer Moodle-Installation.
2. Führen Sie die Moodle-Installation/Upgrade-Routine aus.
3. Aktivieren Sie das Plugin in den Repository-Einstellungen.

## Konfiguration

1. Gehen Sie zu **Website-Administration > Plugins > Repositories > PeerTube videos**
2. Geben Sie die **PeerTube Instance URL** ein (z.B. `https://peertube.example.com`)
3. Geben Sie Ihren **Access Token** ein (kann in den PeerTube-Kontoeinstellungen unter "Applications" oder "API" generiert werden)

## Video-Einbettung in Moodle

Um ein PeerTube-Video direkt in Moodle einzubetten:

1. Erstellen Sie eine **Link/URL-Aktivität** in Ihrem Moodle-Kurs
2. Klicken Sie auf **"Datei auswählen"** oder **"Link auswählen"**
3. Wählen Sie **"PeerTube videos"** als Quelle
4. Suchen Sie nach dem gewünschten Video
5. Wählen Sie das Video aus
6. Unter **"Darstellung"** wählen Sie **"Einbetten"** aus
7. Speichern Sie die Aktivität

Das Video wird nun direkt in Moodle eingebettet und abgespielt.

## Verwendung

1. Erstellen Sie eine **Link/URL-Aktivität** in Ihrem Moodle-Kurs
2. Klicken Sie auf **"Datei auswählen"** oder **"Link auswählen"**
3. Wählen Sie **"PeerTube videos"** als Quelle
4. Suchen Sie nach Videos (auch unlisted Videos werden angezeigt, wenn Sie die entsprechenden Berechtigungen haben)
5. Wählen Sie ein Video aus
6. Unter **"Darstellung"** wählen Sie **"Einbetten"** aus, damit das Video direkt in Moodle angezeigt wird

## Unterstützte Funktionen

- Suche nach Videos auf der PeerTube-Instanz
- Zugriff auf alle Videos, auf die der Benutzer Berechtigung hat (inklusive unlisted Videos)
- Sortierung nach Datum, Views, Likes
- Embed-URLs für direkte Einbettung

## Anforderungen

- Moodle 4.0 oder höher
- Eine PeerTube-Instanz mit aktivierter API
- Ein gültiger Access Token mit entsprechenden Berechtigungen

## Hinweise zur Video-Einbettung

- Die Embed-URLs werden automatisch vom Plugin bereitgestellt
- Wählen Sie unter "Darstellung" die Option "Einbetten" aus, damit das Video direkt in Moodle angezeigt wird
- Stellen Sie sicher, dass Ihre PeerTube-Instanz Embedding erlaubt (Standard-Einstellung)

## Support

Bei Problemen oder Fragen wenden Sie sich bitte an Ihren Moodle-Administrator oder die PeerTube-Community.
