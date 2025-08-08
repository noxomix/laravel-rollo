# Phase 5 – Aufräumen und Nicht‑Kernfeatures (mit Begründung)

Diese Datei sammelt bewusst Dinge, die nicht zu den Grundfeatures des Pakets gehören oder aktuell (noch) nicht konsistent/umgesetzt sind. Ziel: Kern fokussieren, Extras später gezielt nachziehen.

## Grundfeatures (Scope)
- Polymorphes RBAC-Grundmodell: `RolloPermission`, `RolloRole`, `RolloContext` + Pivot-Tabellen.
- Traits für App‑Modelle: `HasRolloRoles`, `HasRolloPermissions` inkl. Kontextauflösung.
- Zentrale Lese‑APIs: `Rollo::can()`, `permissionsFor()`, `rolesFor()` mit rekursiver Rollenhierarchie.
- Migrationen und minimale Konfiguration (`config/rollo.php`).
- Keine automatische Durchsetzung (kein Middleware/Policy-Autowire) – die App nutzt nur die bereitgestellten Abfragen/Hilfen.

## Nicht‑Kernfeatures / später umsetzen

- Caching‑Layer und Cache‑Kommandos
  - Status: In Code nicht implementiert; nur Spuren in README und ein auskommentierter Command‑Eintrag im Service Provider.
  - Begründung: Performance ist wichtig, aber erst nach echter Nutzung sinnvoll zu optimieren. Ein Cache beeinflusst Korrektheit, Invalidierungslogik und API‑Design. Darum später mit klaren Benchmarks und Testabdeckung einführen.
  - To‑do: README‑Hinweise zu Cache entfernen/entschärfen, Command implementieren oder Erwähnung streichen.

 

- Erweiterte `config`‑Validierung (Schema‑basiert)
  - Status: `RolloValidator::validateConfig()` unterstützt optionales Schema und Feld‑Typprüfungen; aktuell wird kein Schema im Paket geliefert.
  - Begründung: Overhead ohne unmittelbaren Nutzen. Für den Kern reicht: `config` ist Array (oder null). Typ‑/Schema‑Validierung erst einführen, wenn ein offiziell unterstütztes Konfig‑Schema existiert.
  - To‑do: Schema‑Teil erst in einer späteren Phase aktiv nutzen oder den Code vereinfachen; README entsprechend klarstellen.

- Veraltete/inkonsistente Doku und Tests
  - Status: README erwähnt Cache und Befehle, die es (noch) nicht gibt; Tests/Skripte referenzieren `greet()`/`isEnabled()` und eine „rollo_table“, die nicht existieren.
  - Begründung: Verwirrt Nutzer und bricht CI. Nicht Teil des Kerns.
  - To‑do: README überarbeiten (Cache entfernen/als „geplant“ markieren), Tests an aktuelle Modelle/Flows anpassen oder vorerst entfernen.

- `allowed_models` Standardinhalt
  - Status: Enthält `App\Models\User` und fälschlich auch `RolloRole::class`.
  - Begründung: Whitelist soll App‑Modelle schützen, nicht Paket‑eigene. Das reduziert Angriffsfläche bei dynamischen Abfragen und hält die Intention klar.
  - To‑do: `RolloRole::class` aus Default entfernen; README präzisieren, wie/warum die Whitelist gepflegt wird.

## allowed_models – wann geprüft und warum dort gerechtfertigt?
- Wo: Prüfung erfolgt ausschließlich in `ModelValidator::validateModelClass()`.
  - Verwendungsstellen:
    - `RolloContext::modelsWithPermissions(string $modelClass)`
    - `RolloContext::modelsWithRoles(string $modelClass)`
- Warum dort: In diesen Pfaden kommt der Klassenname als String aus Aufrufer‑Kontext und wird für Joins/Queries genutzt. Die Whitelist verhindert, dass beliebige (unerwartete) Klassen/Tabelle verknüpft werden. Das reduziert:
  - Missbrauch (Auslesen anderer Tabellen),
  - Seiteneffekte durch instanziierte Klassen,
  - Angriffsfläche bei dynamischer Query‑Zusammenstellung.
- Hinweis (Verbesserung): Derzeit wird in `validateModelClass()` das Model instanziiert, bevor die Whitelist geprüft wird. Besser: Zuerst `allowed_models` prüfen, dann erst instanziieren (Side‑Effects vermeiden). Diese Änderung ist für eine spätere Phase vorgesehen.
- Warum nicht in Traits/Rollo::can(): Dort arbeiten wir mit konkreten Model‑Instanzen bzw. Typsicherheit via Eloquent‑Beziehungen; kein direkter String‑zu‑Klasse‑Sprung → geringeres Risiko, daher keine Whitelist nötig.

## Input‑Validierung – was und warum?
- Namensvalidierung (Permissions/Rollen) via `RolloValidator`:
  - Pattern, Länge, reservierte Wörter; erzwungen beim Erstellen/Aktualisieren von `RolloPermission`/`RolloRole` und bei Zuweisung via String in Traits.
  - Zweck: Konsistente, sichere Keys (keine problematischen Zeichen), einfache Indizierung, weniger Fehlerquellen.
- `config`‑Validierung:
  - Minimal: `config` muss Array oder null sein.
  - Optional: Schema‑Validation (siehe oben als Nicht‑Kernfeature).
- Tabellenname‑Sanitizing in `RolloContext`‑Abfragen:
  - `ModelValidator::sanitizeTableName()` schützt vor ungültigen/unerwarteten Tabellennamen bei dynamischen Joins.

## Caching – ist es komplett raus?
- Ja, zur Laufzeit gibt es aktuell keinen Caching‑Code: keine Cache‑Stores, keine Cache‑Keys, keine Invalidation. Einzig die README erwähnt Cache, und im Service Provider ist ein Cache‑Reset‑Command auskommentiert. Praktisch nicht aktiv.
- Maßnahme: Doku bereinigen oder Cache erst in späterer Phase sauber implementieren (inkl. Invalidation und Tests).
