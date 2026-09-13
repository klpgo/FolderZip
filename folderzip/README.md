# folderzip

SeedDMS-Extension: lädt alle Dokumente eines Ordners als ZIP herunter.

## Installation

`ext/folderzip/` in `<seeddms-webroot>/ext/` legen, dann in
**Admin Tools → Extension Manager** aktivieren.

## Benutzung

In der "Informationen"-Box der geöffneten Ordneransicht (oben, neben
ID/Besitzer/Erstellt am) erscheint eine "Download"-Zeile: lädt
**nur die Dokumente dieses Ordners** als ZIP (keine Unterordner,
kein Wurzelverzeichnis im Archiv).

Zusätzlich bekommt jede Unterordner-Zeile in einer Ordnerliste ein
Download-Icon in der Aktion-Spalte (neben Löschen/Bearbeiten/
Zwischenablage): lädt diesen Unterordner **inkl. aller Unterordner**
herunter, mit dem Ordner selbst als Wurzelverzeichnis im ZIP
(vorher Bestätigungsdialog, da das bei großen Bäumen lange dauern
kann).

Direkter Link, unabhängig vom UI:

```
ext/folderzip/out/out.FolderZip.php?folderid=<ID>
ext/folderzip/out/out.FolderZip.php?folderid=<ID>&recursive=1
```

## Details

- Nur die neueste Version jedes Dokuments wird eingepackt.
- Es werden nur Dokumente/Ordner eingepackt, auf die der eingeloggte
  Nutzer mindestens Lesezugriff hat.
- Namensgleiche Dateien im selben Zielordner werden automatisch
  durchnummeriert.
- Benötigt die PHP-Erweiterung `zip` (Klasse `ZipArchive`).
- Der rekursive Download fragt vorher per Bestätigungsdialog nach
  (Schutz vor versehentlichem Download des ganzen Dokumentenbaums).
- Zusätzliches Sicherheitsnetz: bricht sauber mit Fehlermeldung ab,
  wenn zu viele Dokumente zusammenkommen würden. Grenze einstellbar
  in Admin Tools → Extension Manager → folderzip → Zahnrad-Symbol
  ("Max. Anzahl Dateien pro ZIP"), Standard: 500.
