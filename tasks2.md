# Laravel Rollo - Sicherheits- und Komplexitätsanalyse Tasks

## 🔴 Kritische Sicherheitsprobleme (Priorität: HOCH)

### Task 1: SQL Injection Vulnerability beheben
- [ ] **1.1** Fix SQL Injection in `RolloContext::modelsWithPermissions()` (Zeile 108-110)
  - Problem: Tabellenname wird unsicher konkateniert
  - Lösung: Query Builder mit Parameter Binding verwenden
  
### Task 2: Input Validierung implementieren
- [ ] **2.1** Validierung für Permission-Namen hinzufügen (alphanumerisch + Bindestrich/Unterstrich)
- [ ] **2.2** Validierung für Role-Namen implementieren
- [ ] **2.3** JSON-Config Felder gegen Schema validieren
- [ ] **2.4** Context-IDs auf Existenz prüfen vor Zuweisung

### Task 3: Autorisierung für Admin-Operationen
- [ ] **3.1** Gate/Policy für Permission-Zuweisung erstellen
- [ ] **3.2** Gate/Policy für Rollen-Zuweisung erstellen
- [ ] **3.3** Middleware für geschützte Operationen hinzufügen

## 🟡 Performance-Optimierungen (Priorität: MITTEL)

### Task 4: N+1 Query Probleme beheben
- [ ] **4.1** Eager Loading in `getPermissionsThroughRoles()` implementieren
- [ ] **4.2** Batch-Loading für Rollenvererbung hinzufügen
- [ ] **4.3** Query-Optimierung für `modelsWithPermissions()`

### Task 5: Caching-Layer implementieren
- [ ] **5.1** In-Memory Cache für Rollenvererbung während Request
- [ ] **5.2** Optional: Redis-basiertes Caching mit automatischer Invalidierung
- [ ] **5.3** Cache-Warming für häufig genutzte Permissions

### Task 6: Datenbank-Indizes optimieren
- [ ] **6.1** Compound Index für `model_type` + `model_id` erstellen
- [ ] **6.2** Index für `context_id` in Pivot-Tabellen
- [ ] **6.3** Index für `name` Felder in roles/permissions Tabellen

## 🟢 Code-Qualität & Wartbarkeit (Priorität: NIEDRIG)

### Task 7: Interfaces und Contracts einführen
- [ ] **7.1** `HasRolloPermissionsContract` Interface erstellen
- [ ] **7.2** `HasRolloRolesContract` Interface erstellen
- [ ] **7.3** `RolloContextContract` Interface erstellen

### Task 8: Fehlerbehandlung standardisieren
- [ ] **8.1** Custom Exception-Klassen erstellen (RolloException, RolloValidationException)
- [ ] **8.2** Konsistente Fehlerbehandlung implementieren
- [ ] **8.3** Logging für alle kritischen Operationen hinzufügen

### Task 9: Magic Numbers eliminieren
- [ ] **9.1** Konstanten-Klasse für Default-Werte erstellen
- [ ] **9.2** Konfigurierbare Werte in config/rollo.php verschieben
- [ ] **9.3** Maximale Vererbungstiefe konfigurierbar machen

## 🔵 Logische Korrektheit (Priorität: MITTEL)

### Task 10: Race Conditions beheben
- [ ] **10.1** Atomare Operation für Context-Erstellung mit DB-Transaction
- [ ] **10.2** Optimistic Locking für konkurrierende Updates
- [ ] **10.3** Unique Constraints wo nötig hinzufügen

### Task 11: Konsistente Context-Behandlung
- [ ] **11.1** Einheitliches Verhalten bei Context-Mismatches
- [ ] **11.2** Klare Dokumentation der Context-Semantik
- [ ] **11.3** Helper-Methoden für Context-Validierung

### Task 12: Verbesserte Zirkularitätsprüfung
- [ ] **12.1** Maximale Vererbungstiefe einführen
- [ ] **12.2** Erweiterte Zirkularitätserkennung implementieren
- [ ] **12.3** Performance-Optimierung der Rekursion

## ⚡ Zusätzliche Features (Priorität: NIEDRIG)

### Task 13: Audit Trail implementieren
- [ ] **13.1** Migration für `rollo_audit_logs` Tabelle
- [ ] **13.2** Automatisches Logging aller Permission/Role Änderungen
- [ ] **13.3** Audit-Report Generator

### Task 14: Erweiterte Funktionalität
- [ ] **14.1** Bulk-Operationen mit Transaktionen
- [ ] **14.2** Permission-Gruppen/Sets
- [ ] **14.3** Zeitbasierte Permissions (Gültigkeit von/bis)
- [ ] **14.4** Permission-Delegation (User kann seine Permissions weitergeben)

### Task 15: Developer Experience
- [ ] **15.1** Artisan Commands für Permission/Role Management
- [ ] **15.2** Bessere PHPDoc Dokumentation
- [ ] **15.3** IDE Helper Files generieren
- [ ] **15.4** Debug-Modus mit Query-Logging

## 📊 Metriken für Erfolg

- Alle Sicherheitslücken geschlossen
- Performance: < 10ms für Standard Permission-Checks
- 100% Test-Coverage für kritische Pfade
- Keine N+1 Query Probleme
- Vollständige PHPDoc Coverage

## 🚀 Implementierungsreihenfolge

1. **Phase 1 (Sicherheit)**: Tasks 1-3
2. **Phase 2 (Performance)**: Tasks 4-6
3. **Phase 3 (Stabilität)**: Tasks 10-12
4. **Phase 4 (Qualität)**: Tasks 7-9
5. **Phase 5 (Features)**: Tasks 13-15