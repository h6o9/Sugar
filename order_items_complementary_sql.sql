-- ========================================
-- FINAL RAW SQL: Add product_complementary_id to order_items Table
-- Run this SQL in your live database
-- ========================================

-- 1. Add product_complementary_id column to order_items table
ALTER TABLE order_items 
ADD COLUMN product_complementary_id BIGINT UNSIGNED NULL AFTER product_id;

-- 2. Add foreign key constraint
ALTER TABLE order_items 
ADD CONSTRAINT order_items_product_complementary_id_foreign 
FOREIGN KEY (product_complementary_id) 
REFERENCES products(id) 
ON DELETE SET NULL 
ON UPDATE CASCADE;

-- 3. Verify the column was added
DESCRIBE order_items;

-- 4. Test the relationship (optional)
-- Insert test data with complementary product
-- UPDATE order_items SET product_complementary_id = 2 WHERE id = 1;

-- 5. Check the relationship
SELECT 
    oi.id as order_item_id,
    oi.product_name,
    oi.product_complementary_id,
    p.name as complementary_product_name,
    p.image as complementary_product_image
FROM order_items oi
LEFT JOIN products p ON oi.product_complementary_id = p.id
WHERE oi.product_complementary_id IS NOT NULL;

-- ========================================
-- ✅ ORDER_ITEMS TABLE UPDATED
-- Column 'product_complementary_id' added to order_items table
-- Foreign key constraint added
-- Ready for complementary product functionality
-- ========================================
