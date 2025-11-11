# CSV Import Guide

## Client Import

### Quick Import

1. **Place your CSV file:**
   ```
   /var/www/erp/app/storage/app/imports/clients.csv
   ```

2. **Run the import:**
   ```bash
   docker compose exec erp_app php artisan import:clients
   ```

   Or for a specific file:
   ```bash
   docker compose exec erp_app php artisan import:clients your-file.csv
   ```

### CSV Format

The import expects these columns (in Romanian):

| Column Name | Required | Example |
|-------------|----------|---------|
| Nume | Yes | Mihaela Tatu |
| Companie | No | Simplead S.R.L. |
| CUI | No | 41501661 |
| Nr. Înregistrare | No | J171/488/2019 |
| Adresă | No | Str. Podul Înalt, 5, București |
| Email | No | contact@example.com |
| Telefon | No | 0721234567 |
| Persoană Contact | No | John Doe |
| Status | No | Mentenanta |
| Plătitor TVA | No | Da / Nu |
| Notițe | No | Any notes here |
| Data Creare | No | 29.10.2025 |

### Import Features

✓ **Automatic slug generation** - Creates URL-friendly slugs from client names
✓ **Duplicate handling** - Adds numbers to duplicate slugs automatically
✓ **VAT payer detection** - Converts "Da"/"Nu" to boolean
✓ **Empty field handling** - Null values for empty fields
✓ **Progress bar** - Visual feedback during import
✓ **Error reporting** - Shows which rows failed and why
✓ **Transaction safety** - Rolls back everything if import fails

### Status Values

Recognized status values:
- Mentenanta → Mentenanță
- In progress → In Progress
- Canceled → Canceled
- Supraveghere → Supraveghere

### Best Practices

1. **Always backup before import:**
   ```bash
   ./backup_database.sh "before_my_import"
   ```

2. **Test with small CSV first:**
   - Create a test CSV with 2-3 rows
   - Run import
   - Verify results in web interface
   - Then import full file

3. **Check for duplicates:**
   - The import will skip rows with duplicate tax_id for the same user
   - Check error output for skipped rows

4. **Encoding:**
   - Use UTF-8 encoding for Romanian characters
   - CSV delimiter: comma (,)
   - Text qualifier: double quotes (")

### Example CSV

```csv
Nume,Companie,CUI,Nr. Înregistrare,Adresă,Email,Telefon,Persoană Contact,Status,Plătitor TVA,Notițe,Data Creare
Test Client,Test SRL,12345678,J40/123/2024,"Str. Test 1, București",test@example.com,0721234567,John Doe,Mentenanta,Da,Test notes,10.11.2025
```

### Troubleshooting

**Command not found:**
```bash
docker compose exec erp_app composer dump-autoload
docker compose exec erp_app php artisan optimize:clear
```

**Permission denied:**
```bash
chmod -R 755 /var/www/erp/app/storage/app/imports
```

**File not found:**
- Check file is in `/var/www/erp/app/storage/app/imports/`
- Check filename matches the command argument
- Check file has `.csv` extension

**Import failed:**
- Check CSV format matches expected columns
- Check for invalid characters or encoding issues
- Review error messages in output
- Check database connection

### Import Statistics

After import, you'll see:
- Total rows processed
- Successfully imported count
- Skipped rows (with reasons)
- Errors (with row numbers and details)

### Latest Import Results

**Date:** 2025-11-11 14:36
**File:** clienti_2025-11-11.csv
**Results:**
- ✓ Imported: 19 clients
- ✓ VAT Payers: 10
- ✓ Unique Companies: 18
- ✓ Errors: 0

---

**Created:** 2025-11-11
**Command Location:** [ImportClientsCommand.php](app/app/Console/Commands/ImportClientsCommand.php)
