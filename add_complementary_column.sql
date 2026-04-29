-- ========================================
-- Add Complementary Product Column to Products Table
-- Run this SQL in your live database
-- ========================================

-- 1. Add complementary_product column to products table
ALTER TABLE products 
ADD COLUMN complementary_product INT NULL 
AFTER menu_id;

-- 2. Add foreign key constraint (optional but recommended)
ALTER TABLE products 
ADD CONSTRAINT fk_products_complementary_product 
FOREIGN KEY (complementary_product) 
REFERENCES products(id) 
ON DELETE SET NULL 
ON UPDATE CASCADE;

-- 3. Add index for better performance
CREATE INDEX idx_products_complementary_product 
ON products(complementary_product);

-- 4. Verify the column was added
DESCRIBE products;

-- 5. Test with sample data (optional)
-- Update a product to have a complementary product
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
