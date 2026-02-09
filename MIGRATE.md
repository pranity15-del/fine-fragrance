Migration steps — Fine & Fragrance

1. Backup current database:
   - Using phpMyAdmin or mysqldump: mysqldump -u root -p smenterprises > smenterprises_backup.sql

2. Apply migration:
   - In phpMyAdmin import `migrations/20260202_perfume_migration.sql` OR use mysql cli:
     mysql -u root -p smenterprises < migrations/20260202_perfume_migration.sql

3. Verify:
   - Check `product` table for new columns (`brand`, `size_ml`, `concentration`, `sku`, `scent_notes`, `stock`).
   - Check sample products added.

4. Files updated:
   - Admin: `admin/add_product.php`, `admin/update.php`, `admin/update_product.php`, `admin/view_product.php`, `admin/header.php` (branding & removed customization links)
   - User: `user/view_product.php`, `user/header.php`, `user/customization.php` (disabled), `user/profile.php` (removed customization link)
   - Root: `index (11).php` updated to Fine & Fragrance
   - Assets: `productimg/logo.svg`, `productimg/placeholder_perfume.svg`

5. Notes:
   - Customization feature has been disabled in UI but historical DB tables remain. If you want to drop the `customization` table or remove DB references, let me know and I will prepare a safe migration.
   - Place your official logo file at `productimg/logo.svg` (replace placeholder) and actual product images at `productimg/`.
