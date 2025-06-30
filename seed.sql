-- Sample Users
INSERT INTO users (name, email, phone, address, password_hash, is_admin) VALUES 
('Krish Roy', 'krishroy@gmail.com',     '72898989899', 'Guwahati, Assam ', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE),
('Jeson Theik', 'jesonthiek@gmail.com', '98765432109', 'Dibrugarh, Assam', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE),
('Admin User', 'admin@pepecomics.com',  '44422200069', 'Guwahati, Assam', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

-- Sample Categories
INSERT INTO categories (name) VALUES 
('Action'),
('Adventure'),
('Comedy'),
('Drama'),
('Fantasy'),
('Horror'),
('Mystery'),
('Romance'),
('Sci-Fi'),
('Superhero');

-- Sample Comics
INSERT INTO comics (title, author, publisher, price, stock, image_name, description) VALUES 
('The Amazing Adventures of Pepe', 'John Writer', 'Pepe Publishing', 19.99, 50, 'pepe-adventures.jpg', 'Join Pepe on his most incredible journey yet! This action-packed adventure follows our favorite frog through dangerous missions and hilarious encounters.'),
('Mystery at Midnight', 'Sarah Author', 'Mystery Books', 14.99, 30, 'mystery-midnight.jpg', 'A thrilling mystery that will keep you guessing until the very end. Who stole the precious memes? Only Detective Pepe can solve this case!'),
('Space Warriors', 'Mike Scribe', 'Galaxy Press', 24.99, 25, 'space-warriors.jpg', 'An epic space opera featuring Pepe and his crew as they defend the galaxy from the forces of normies. Packed with action and interstellar adventure!'),
('The Last Stand', 'Tom Penman', 'Action Comics', 29.99, 40, 'last-stand.jpg', 'When all hope seems lost, one brave frog stands between chaos and order. An action-packed tale of courage and determination.'),
('Laughing with Pepe', 'Comedy King', 'Meme Comics', 9.99, 100, 'laughing-pepe.jpg', 'A collection of the funniest Pepe stories ever told! Guaranteed to make you laugh out loud with its witty humor and clever jokes.'),
('Dark Shadows', 'Horror Master', 'Spooky Books', 19.99, 20, 'dark-shadows.jpg', 'A spine-chilling horror story that will make you sleep with the lights on. Follow Pepe as he investigates a haunted mansion.'),
('Love in Paris', 'Romance Queen', 'Heart Press', 12.99, 45, 'love-paris.jpg', 'A heartwarming romance set in the city of love. Will Pepe finally find his soulmate under the Eiffel Tower?'),
('Dragon Quest', 'Fantasy Writer', 'Magic Books', 34.99, 15, 'dragon-quest.jpg', 'An epic fantasy adventure where Pepe must save the realm from an ancient dragon. Magic, monsters, and mayhem await!'),
('Detective Pepe', 'Mystery Man', 'Clue Comics', 17.99, 35, 'detective-pepe.jpg', 'Follow the brilliant Detective Pepe as he solves the most perplexing cases in the city. A classic noir mystery with a modern twist.'),
('Superhero Squad', 'Power Writer', 'Hero Comics', 39.99, 60, 'superhero-squad.jpg', 'Join Pepe and his superhero friends as they protect the city from supervillains. Action-packed with amazing superpowers and teamwork!'),
('Time Travelers', 'Future Author', 'Time Press', 22.99, 25, 'time-travelers.jpg', 'A mind-bending journey through time as Pepe attempts to fix the timeline and save the future. Past, present, and future collide!'),
('Zombie Apocalypse', 'Horror Writer', 'Scary Comics', 16.99, 40, 'zombie-apocalypse.jpg', 'Can Pepe survive the zombie apocalypse? A thrilling tale of survival, friendship, and courage in a world overrun by the undead.');

-- Sample Comic Categories (assigning categories to comics)
INSERT INTO comic_categories (comic_id, category_id) VALUES 
(1, 1), (1, 2), -- The Amazing Adventures of Pepe: Action, Adventure
(2, 7),         -- Mystery at Midnight: Mystery
(3, 9),         -- Space Warriors: Sci-Fi
(4, 1),         -- The Last Stand: Action
(5, 3),         -- Laughing with Pepe: Comedy
(6, 6),         -- Dark Shadows: Horror
(7, 8),         -- Love in Paris: Romance
(8, 5),         -- Dragon Quest: Fantasy
(9, 7),         -- Detective Pepe: Mystery
(10, 10),       -- Superhero Squad: Superhero
(11, 9),        -- Time Travelers: Sci-Fi
(12, 6);        -- Zombie Apocalypse: Horror

-- Sample Reviews
INSERT INTO reviews (comic_id, user_id, rating, comment) VALUES 
(1, 1, 5, 'Amazing comic! The adventures are truly epic!'),
(1, 2, 4, 'Great storyline and artwork. Highly recommended.'),
(2, 1, 4, 'Intriguing mystery that keeps you guessing.'),
(3, 2, 5, 'Best sci-fi comic I''ve read this year!'),
(4, 1, 3, 'Good action scenes but predictable plot.'),
(5, 2, 5, 'Hilarious! Couldn''t stop laughing.'),
(6, 1, 4, 'Genuinely scary and well-drawn.'),
(7, 2, 4, 'A beautiful love story with great artwork.'),
(8, 1, 5, 'Epic fantasy adventure! Loved every page.'),
(9, 2, 4, 'Clever detective story with unexpected twists.'),
(10, 1, 5, 'Amazing superhero action and great character development.');

-- Sample Cart Items
INSERT INTO cart (user_id, comic_id, quantity) VALUES 
(1, 3, 2),
(1, 5, 1),
(2, 7, 1),
(2, 10, 2);

-- Sample Orders
INSERT INTO orders (user_id, order_date) VALUES 
(1, NOW() - INTERVAL 7 DAY),
(2, NOW() - INTERVAL 5 DAY),
(1, NOW() - INTERVAL 2 DAY);

-- Sample Order Items
INSERT INTO order_items (order_id, comic_id, quantity, total_price) VALUES 
(1, 1, 2, 39.98),
(1, 4, 1, 29.99),
(2, 7, 1, 12.99),
(2, 8, 1, 34.99),
(3, 10, 1, 39.99),
(3, 11, 2, 45.98);

-- Sample Payments
INSERT INTO payments (order_id, amount, payment_method, status) VALUES 
(1, 69.97, 'Credit Card', 'Completed'),
(2, 47.98, 'UPI', 'Completed'),
(3, 85.97, 'Credit Card', 'Completed'); 