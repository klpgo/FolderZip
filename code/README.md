<<<<<<< HEAD
# folderzip

SeedDMS-Extension: lädt alle Dokumente eines Ordners als ZIP herunter.

## Installation

`ext/folderzip/` in `<seeddms-webroot>/ext/` legen, dann in
**Admin Tools → Extension Manager** aktivieren.

## Benutzung

Unten in jeder Ordneransicht erscheinen zwei kleine Links:
"Als ZIP herunterladen" (nur dieser Ordner) und "inkl. Unterordner"
(rekursiv, mit Verzeichnisstruktur im ZIP).

Direkter Link, unabhängig vom UI:

```
ext/folderzip/out/out.FolderZip.php?folderid=<ID>
ext/folderzip/out/out.FolderZip.php?folderid=<ID>&recursive=1
```

## Details

- Nur die neueste Version jedes Dokuments wird eingepackt.
- Es werden nur Dokumente/Ordner eingepackt, auf die der eingeloggte
  Nutzer mindestens Lesezugriff hat.
- Der Name des SeedDMS Dokuments wird als Dateiname übernommen. Falls
  keine Extension vorhanden ist, dann wird sie vom Filename des 
  hochgeladenen Files übernommen.
- Namensgleiche Dateien im selben Zielordner werden automatisch
  durchnummeriert.
- Benötigt die PHP-Erweiterung `zip` (Klasse `ZipArchive`).
- Der rekursive Download fragt vorher per Bestätigungsdialog nach
  (Schutz vor versehentlichem Download des ganzen Dokumentenbaums).
- Zusätzliches Sicherheitsnetz: bricht sauber mit Fehlermeldung ab,
  wenn zu viele Dokumente zusammenkommen würden. Grenze einstellbar
  in Admin Tools → Extension Manager → folderzip → Zahnrad-Symbol
  ("Max. Anzahl Dateien pro ZIP"), Standard: 500.
=======
# FolderZip
SeedDMS extension to download (optional recursive) a folder and its documents.
>>>>>>> 72e757170ef985a7f37dce19cee96ec21cb2d3a6
