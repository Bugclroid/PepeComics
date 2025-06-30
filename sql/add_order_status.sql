-- Add status column to orders table if it doesn't exist
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS status 
ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') 
NOT NULL DEFAULT 'pending' 
AFTER order_date; 