-- ========================================
-- FINAL: Add Complementary Product Column to Products Table
-- Run this SQL in your live database
-- ========================================

-- 1. Add complementary_product column to products table
ALTER TABLE products 
ADD COLUMN complementary_product INT NULL 
AFTER menu_id;

-- 2. Add foreign key constraint for data integrity
ALTER TABLE products 
ADD CONSTRAINT fk_products_complementary_product 
FOREIGN KEY (complementary_product) 
REFERENCES products(id) 
ON DELETE SET NULL 
ON UPDATE CASCADE;

-- 3. Add index for better query performance
CREATE INDEX idx_products_complementary_product 
ON products(complementary_product);

-- 4. Verify the column was added successfully
DESCRIBE products;

-- 5. Test the relationship with sample data (optional)
-- First, let's see what products exist
SELECT id, name, menu_id FROM products LIMIT 5;

-- Set product 1's complementary product to product 2 (example)
-- UPDATE products SET complementary_product = 2 WHERE id = 1;

-- 6. Verify the relationship works
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
-- USAGE INSTRUCTIONS:
-- 1. Run this SQL in your database
-- 2. Update products by setting complementary_product = ID
-- 3. Use the relationship in your code
-- ========================================
