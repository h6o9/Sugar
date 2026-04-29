-- ========================================
-- FINAL RAW SQL: Add Complementary Product Column
-- Run this SQL in your live database
-- ========================================

-- 1. Add complementary_product column to products table
ALTER TABLE products 
ADD COLUMN complementary_product BIGINT UNSIGNED NULL AFTER menu_id;

-- 2. Add foreign key constraint
ALTER TABLE products 
ADD CONSTRAINT products_complementary_product_foreign 
FOREIGN KEY (complementary_product) 
REFERENCES products(id) 
ON DELETE SET NULL 
ON UPDATE CASCADE;

-- 3. Add index for better performance
CREATE INDEX products_complementary_product_index 
ON products(complementary_product);

-- 4. Verify the column was added
DESCRIBE products;

-- 5. Test the relationship (optional)
-- Set product 1's complementary product to product 2
-- UPDATE products SET complementary_product = 2 WHERE id = 1;

-- 6. Check the relationship
SELECT 
    p1.id as main_product_id,
    p1.name as main_product_name,
    p1.complementary_product,
    p2.name as complementary_product_name,
    p2.image as complementary_product_image
FROM products p1
LEFT JOIN products p2 ON p1.complementary_product = p2.id
WHERE p1.complementary_product IS NOT NULL;

-- ========================================
-- ✅ MIGRATION COMPLETED SUCCESSFULLY
-- Column 'complementary_product' added to products table
-- Foreign key constraint added
-- Index added for performance
-- ========================================
