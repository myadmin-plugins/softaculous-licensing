# Caliber Learnings

Accumulated patterns and anti-patterns from development sessions.
Auto-managed by [caliber](https://github.com/caliber-ai-org/ai-setup) — do not edit manually.

- **[gotcha]** `src/SoftaculousNOC.php` is too large to read in one shot — always pass a `limit` parameter (e.g. `limit: 150`) when reading it, or the Read tool returns an empty response
- **[gotcha]** `src/SoftaculousNOC.php` contains a duplicate `ArrayToXML` class definition at the bottom of the file — `tests/bootstrap.php` truncates it before loading to avoid class redeclaration errors. Be aware of this when editing the end of that file
- **[gotcha]** Glob patterns with deep wildcards across `vendor/` (e.g. `/home/sites/*/vendor/detain/*/src/Plugin.php`) may fail or timeout — use more specific paths when searching vendor packages
