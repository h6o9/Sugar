-- ========================================
-- Test Order with Complementary Products
-- Run this SQL in your live database
-- ========================================

-- 1. Create Test Order
INSERT INTO orders (user_id, order_number, subtotal, total_amount, status, payment_status, created_at, updated_at) 
VALUES (1, 'TEST-001', 25.50, 25.50, 'completed', 'paid', NOW(), NOW());

-- Get the order ID (use LAST_INSERT_ID() for dynamic ID)
SET @order_id = LAST_INSERT_ID();

-- 2. Create Main Product with Complementary Product
INSERT INTO order_items (
    order_id, 
    product_id, 
    product_complementary_id, 
    product_name, 
    product_size, 
    product_price, 
    quantity, 
    sub_total, 
    branch_id, 
    delivery_status, 
    created_at, 
    updated_at
) VALUES (
    @order_id, 
    1, -- Main product ID (change to your actual product ID)
    2, -- Complementary product ID (change to your actual complementary product ID)
    'Test Main Product', 
    'regular', 
    15.50, 
    1, 
    15.50, 
    1, 
    1, 
    NOW(), 
    NOW()
);

-- 3. Verify the data was inserted correctly
SELECT 
    oi.id as order_item_id,
    oi.order_id,
    oi.product_name,
    oi.product_price,
    oi.quantity,
    oi.product_complementary_id,
    cp.name as complementary_product_name,
    cp.image as complementary_product_image,
    o.order_number,
    o.status as order_status
FROM order_items oi
LEFT JOIN products cp ON oi.product_complementary_id = cp.id
LEFT JOIN orders o ON oi.order_id = o.id
WHERE oi.order_id = @order_id;

-- 4. Check if complementary_products table has the relationship
SELECT 
    cp.id,
    cp.product_id,
    cp.complementary_product_id,
    p.name as main_product_name,
    comp.name as complementary_product_name
FROM complementary_products cp
LEFT JOIN products p ON cp.product_id = p.id
LEFT JOIN products comp ON cp.complementary_product_id = comp.id
WHERE cp.product_id = 1 OR cp.complementary_product_id = 2;
