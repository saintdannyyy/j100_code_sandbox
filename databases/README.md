# SQL Databases Directory

This folder contains SQLite database files for SQL snippets.

## File Naming Convention

- `snippet_{id}.db` - Permanent database for saved snippets
- `temp_{userid}.db` - Temporary database for unsaved work (auto-deleted after 24h)
- `readonly_{userid}_{hash}.db` - Read-only copies for non-owners (auto-deleted after 1h)

## Security

- This folder is protected by `.htaccess`
- Direct web access is denied
- Only the PHP backend can read/write these files

## Maintenance

Old temporary files are automatically cleaned up by the SQL executor.
To manually clean up:

```bash
# Delete temp files older than 24 hours
find . -name "temp_*.db" -mtime +1 -delete

# Delete readonly files older than 1 hour
find . -name "readonly_*.db" -mmin +60 -delete
```

## Backup

To backup all user databases:

```bash
tar -czvf sql_databases_backup.tar.gz snippet_*.db
```
