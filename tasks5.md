# Tasks 5 – Priorisierte Aufgaben aus Phase 5

1. Entferne alle Cache‑Behauptungen und Beispiele aus der README und Skripten, bis ein Cache implementiert ist, und entferne den ungenutzten Import `RolloCacheResetCommand` aus `LaravelRolloServiceProvider`.
2. Belasse `Noxomix\\LaravelRollo\\Models\\RolloRole` standardmäßig in `config('rollo.allowed_models')` und aktualisiere die Kommentartexte der Konfiguration, sodass klar ist, dass paket­eigene Modelle gelistet sein dürfen, wenn sie die Traits verwenden.
3. Prüfe in `ModelValidator::validateModelClass` die `allowed_models`‑Whitelist, bevor die Klasse instanziiert wird, und wirf bei nicht erlaubter Klasse eine `InvalidArgumentException`.
4. Aktualisiere die README so, dass klar dokumentiert ist, dass das Paket keine Berechtigungen automatisch durchsetzt und ausschließlich Hilfs‑APIs zum eigenen RBAC bereitstellt.
5. Ersetze oder entferne veraltete Tests und Skripte, die `greet()`, `isEnabled()` oder nicht existente Migrationen referenzieren, und füge minimale, zutreffende Tests für das Publishen von Config/Migrationen hinzu.
6. Dokumentiere das aktuelle Verhalten der `config`‑Validierung (Array oder null) und formuliere Schema‑Validierung als optional und derzeit ungenutzt, ohne Änderungen an der Kernlogik vorzunehmen.
7. Implementiere zu einem späteren Zeitpunkt einen sauberen Cache‑Layer mit Invalidierungsregeln und einen `rollo:clear-cache`‑Befehl und führe die Dokumentation erst dann wieder ein.

