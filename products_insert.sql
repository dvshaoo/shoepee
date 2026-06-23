-- SHOEPEE Product Inserts
-- Run this after creating the database tables

USE shoepee_db;

-- Admin account (password: admin123)
INSERT INTO tbl_admins (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Nike Products
INSERT INTO tbl_products (brand, model_name, size_8, size_85, size_9, size_95, size_10, size_105, size_11, size_115, price, stock_quantity, description, img_url, product_archive, prod_status) VALUES
('Nike', 'Lebron 20', 1, 1, 1, 1, 1, 1, 1, 0, 199.99, 100, 'The LeBron 20 is built for the next generation of basketball players. Lightweight, responsive, and designed for speed.', '1702902538-Lebron 20.webp', 'FALSE', 'Bestseller'),
('Nike', 'KD16', 1, 1, 1, 1, 1, 0, 1, 0, 179.99, 80, 'Kevin Durant''s 16th signature shoe. Ultra-lightweight with Zoom Air cushioning for court-ready comfort.', '1702902667-KD16.webp', 'FALSE', 'Bestseller'),
('Nike', 'Alphafly 3', 1, 1, 1, 1, 1, 1, 0, 0, 284.99, 50, 'The ultimate racing shoe with ZoomX foam and two Zoom Air pods for maximum energy return.', '1702908615-Alphafly 3.webp', 'FALSE', 'Just In'),
('Nike', 'SuperRep', 1, 1, 1, 1, 1, 0, 0, 0, 129.99, 70, 'Designed for high-intensity training. Flexible, breathable, and ready for any workout.', '1702908706-SuperRep.webp', 'FALSE', 'Just In'),
('Nike', 'Gt Hustle', 1, 1, 1, 1, 1, 1, 1, 1, 159.99, 90, 'Speed and agility combined. Built for fast-paced basketball with responsive cushioning.', '1702908726-Gt Hustle.webp', 'FALSE', 'Just In'),
('Nike', 'Court Zoom Pro', 1, 1, 1, 0, 1, 0, 0, 0, 139.99, 60, 'Tennis-inspired performance shoe with Zoom Air technology for quick court movements.', '1702908766-Court Zoom Pro.webp', 'FALSE', 'Just In'),
('Nike', 'KD15', 1, 1, 1, 1, 1, 1, 0, 0, 169.99, 85, 'KD''s 15th signature shoe delivers full-length Zoom Air cushioning and a lightweight fit.', '1702908781-KD15.webp', 'FALSE', 'Bestseller'),
('Nike', 'Gt Jump', 1, 1, 1, 1, 1, 1, 1, 0, 189.99, 55, 'Maximum vertical leap support with Zoom Air strobel and responsive foam.', '1702908857-Gt Jump.webp', 'FALSE', 'Just In'),
('Nike', 'Go FlyEase', 1, 1, 1, 1, 1, 0, 0, 0, 149.99, 40, 'Hands-free entry shoe. Step in and go with the revolutionary FlyEase technology.', '1702908869-Go FlyEase.webp', 'FALSE', 'Just In'),
('Nike', 'Lebron Witness 8', 1, 1, 1, 1, 1, 1, 1, 1, 119.99, 110, 'Affordable performance basketball shoe with Air Max cushioning and durable traction.', '1702908901-Lebron Witness 8.webp', 'FALSE', 'Bestseller'),
('Nike', 'Stay Loyal 2', 1, 1, 1, 1, 1, 0, 0, 0, 109.99, 75, 'Loyal to the game. Comfortable everyday basketball shoe with classic styling.', '1702908920-Stay Loyal 2.webp', 'FALSE', 'Just In'),
('Nike', 'Zion 3', 1, 1, 1, 1, 1, 1, 0, 0, 149.99, 65, 'Zion Williamson''s 3rd signature shoe. Built for explosive power and court dominance.', '1702908939-Zion 3.webp', 'FALSE', 'Just In'),
('Nike', 'Cortez', 1, 1, 1, 1, 1, 1, 1, 1, 89.99, 150, 'The original Nike running shoe. A timeless classic that never goes out of style.', '1702908953-Cortez.webp', 'FALSE', 'Bestseller'),
('Nike', 'Air Jordan 11', 1, 1, 1, 1, 1, 1, 1, 0, 224.99, 45, 'The iconic Air Jordan 11. Patent leather, carbon fiber, and pure basketball heritage.', '1702909078-Air Jordan 11.webp', 'FALSE', 'Bestseller'),
('Nike', 'ISPA', 1, 1, 1, 1, 1, 0, 0, 0, 199.99, 35, 'Improvise, Scavenge, Protect, Adapt. A futuristic shoe built for urban exploration.', '1702909105-ISPA.webp', 'FALSE', 'Just In'),
('Nike', 'Off White', 1, 1, 1, 1, 1, 0, 0, 0, 299.99, 25, 'Virgil Abloh''s deconstructed masterpiece. High fashion meets streetwear culture.', '1702909182-Off white.webp', 'FALSE', 'Just In'),
('Nike', 'Full Force Low', 1, 1, 1, 1, 1, 1, 1, 1, 94.99, 120, 'Classic low-top silhouette with modern comfort. Perfect for everyday wear.', '1702909203-Full Force Low.webp', 'FALSE', 'Just In'),
('Nike', 'Dunk Low Retro', 1, 1, 1, 1, 1, 1, 1, 1, 114.99, 130, 'The Nike Dunk Low. Originally a basketball icon, now a streetwear staple.', '1702914569-dunk-low-retro-shoes-Zc0601.jpeg', 'FALSE', 'Bestseller'),
('Nike', 'Ja 1', 1, 1, 1, 1, 1, 1, 0, 0, 129.99, 70, 'Ja Morant''s first signature shoe. Built for explosive speed and fearless play.', '1702915382-Ja 1.webp', 'FALSE', 'Just In'),
('Nike', 'Lebron 21', 1, 1, 1, 1, 1, 1, 1, 0, 209.99, 60, 'LeBron''s 21st signature shoe. Premium materials and maximum performance.', '1702915398-Lebron 21.webp', 'FALSE', 'Bestseller'),
('Nike', 'Killshot 2 Leather', 1, 1, 1, 1, 1, 1, 1, 1, 89.99, 140, 'Vintage tennis shoe with premium leather upper. A clean, classic look.', '1702915426-Killshot 2 leather.webp', 'FALSE', 'Just In'),
('Nike', 'Sabrina 1', 1, 1, 1, 1, 1, 0, 0, 0, 129.99, 55, 'Sabrina Ionescu''s first signature shoe. Built for the modern game.', '1702915444-Sabrina 1.webp', 'FALSE', 'Just In'),
('Nike', 'Air Trainer 1', 1, 1, 1, 1, 1, 1, 0, 0, 129.99, 80, 'The original cross-training shoe. Versatile performance for any activity.', '1702915484-air-trainer-1-shoes-ZLtGj0.jpeg', 'FALSE', 'Just In'),
('Nike', 'Air Jordan 1 Low', 1, 1, 1, 1, 1, 1, 1, 1, 109.99, 160, 'The low-top version of the iconic Air Jordan 1. Classic style, all-day comfort.', '1702915500-air-jordan-1-low-shoes-6Q1tFM.jpeg', 'FALSE', 'Bestseller'),
('Nike', 'Air Max 1', 1, 1, 1, 1, 1, 1, 1, 0, 149.99, 100, 'The shoe that started it all. Visible Air cushioning changed everything.', '1702915728-Air Max 1.webp', 'FALSE', 'Bestseller'),
('Nike', 'Jordan 1 Mid SE', 1, 1, 1, 1, 1, 1, 0, 0, 134.99, 90, 'Special edition Jordan 1 Mid with premium materials and unique colorways.', '1703016792-Jordan 1 Mid SE.webp', 'FALSE', 'Just In'),
('Nike', 'Jordan Stadium 90', 1, 1, 1, 1, 1, 1, 0, 0, 159.99, 50, 'Stadium-ready style with retro Jordan aesthetics and modern comfort.', '1702953541-Jordan Stadium 90.webp', 'FALSE', 'Just In'),
('Nike', 'Lil Drip', 1, 1, 1, 1, 1, 0, 0, 0, 119.99, 45, 'Bold and expressive. A shoe for those who like to make a statement.', '1702954229-Lil Drip.webp', 'FALSE', 'Just In'),
('Nike', 'Freak 5', 1, 1, 1, 1, 1, 1, 1, 0, 149.99, 85, 'Giannis Antetokounmpo''s 5th signature shoe. Built for versatility and dominance.', '1703021467-Freak 5.webp', 'FALSE', 'Bestseller'),
('Nike', 'AF 1''07', 1, 1, 1, 1, 1, 1, 1, 1, 109.99, 200, 'The Air Force 1 ''07. The classic basketball shoe that became a streetwear legend.', '1703022419-AF 1''07.webp', 'FALSE', 'Bestseller'),
('Nike', 'Air Max 2090', 1, 1, 1, 1, 1, 1, 0, 0, 159.99, 70, 'Inspired by the Air Max 90, built for the future. Visible Air and modern design.', '1703342779-air-max-2090-shoes-lqM65N.jpeg', 'FALSE', 'Just In'),
('Nike', 'Jordan Nu Retro 1 Low', 1, 1, 1, 1, 1, 0, 0, 0, 119.99, 65, 'A fresh take on the Jordan 1 Low with updated materials and styling.', '1703344947-jordan-nu-retro-1-low-shoes-8294mJ.jpeg', 'FALSE', 'Just In'),
('Nike', 'Alphafly 2', 1, 1, 1, 1, 1, 0, 0, 0, 279.99, 30, 'Second generation racing shoe with improved ZoomX foam and Air pods.', '1703345083-alphafly-2-road-racing-shoes-cVPHCD.jpeg', 'FALSE', 'Just In'),
('Nike', 'Air Jordan 13 Wheat', 1, 1, 1, 1, 1, 1, 0, 0, 199.99, 40, 'The Jordan 13 in a premium wheat colorway. Classic silhouette, fresh look.', '1703345164-air-jordan-13-wheat-shoes-pvTjVke9.jpeg', 'FALSE', 'Just In'),
('Nike', 'Air Jordan 7 Retro', 1, 1, 1, 1, 1, 1, 0, 0, 209.99, 35, 'The iconic Jordan 7 returns. Premium materials and classic basketball heritage.', '1703345235-air-jordan-7-retro-shoes-xbNFP8.jpeg', 'FALSE', 'Just In'),
('Nike', 'Air Jordan XXXVIII FIBA', 1, 1, 1, 1, 1, 0, 0, 0, 219.99, 30, 'FIBA special edition Jordan 38. Built for international competition.', '1703345326-air-jordan-xxxviii-fiba-pf-basketball-shoes-XnhFhP.jpeg', 'FALSE', 'Just In'),
('Nike', 'PSG Jumpman MVP', 1, 1, 1, 1, 1, 0, 0, 0, 174.99, 50, 'Paris Saint-Germain x Jordan collaboration. Football meets basketball culture.', '1703345398-paris-saint-germain-jumpman-mvp-shoes-LxppNW.jpeg', 'FALSE', 'Just In'),
('Nike', 'Custom Pegasus 40', 1, 1, 1, 1, 1, 1, 0, 0, 139.99, 100, 'The Pegasus 40, now customizable. Your everyday running companion.', '1703345448-custom-pegasus-40-by-you.jpeg', 'FALSE', 'Just In'),
('Nike', 'SB React Leo', 1, 1, 1, 1, 1, 0, 0, 0, 104.99, 75, 'Leo Baker''s signature skate shoe. React foam meets skateboarding durability.', '1703345574-sb-react-leo-skate-shoes-K7X9W0.jpeg', 'FALSE', 'Just In'),
('Nike', 'WIO 9 Shield', 1, 1, 1, 1, 1, 0, 0, 0, 159.99, 60, 'Weatherised running shoe for all conditions. Shield technology keeps you dry.', '1703345523-wio-9-shield-weatherised-road-running-shoes-VVX2ZW.jpeg', 'FALSE', 'Just In'),
('Nike', 'E-Series AD', 1, 1, 1, 1, 1, 0, 0, 0, 99.99, 80, 'Everyday comfort with a clean, minimal design. Perfect for daily wear.', '1703345636-e-series-ad-shoes-hLR5pR.jpeg', 'FALSE', 'Just In'),
('Nike', 'Jordan Max Aura 5', 1, 1, 1, 1, 1, 1, 0, 0, 129.99, 70, 'Jordan Brand''s affordable basketball shoe with Max Air cushioning.', '1702908806-jordan-max-aura-5-shoes-ZBZ4Pz.jpeg', 'FALSE', 'Just In'),
('Nike', 'Max Aura 4', 1, 1, 1, 1, 1, 0, 0, 0, 119.99, 65, 'Previous generation Max Aura with reliable cushioning and support.', '1703351365-Max Aura 4.webp', 'FALSE', 'Just In'),

-- Adidas Products
('Adidas', 'D Rose', 1, 1, 1, 1, 1, 1, 0, 0, 139.99, 70, 'Derrick Rose''s signature shoe. Built for explosive guards with Boost cushioning.', '1702914922-D rose.webp', 'FALSE', 'Just In'),
('Adidas', 'Forum Low', 1, 1, 1, 1, 1, 1, 1, 1, 99.99, 120, 'The iconic Forum silhouette in low-top form. Basketball heritage meets street style.', '1702914988-Forum Low.webp', 'FALSE', 'Bestseller'),
('Adidas', 'TY 3', 1, 1, 1, 1, 1, 0, 0, 0, 149.99, 45, 'Trae Young''s 3rd signature shoe. Speed and agility for the modern point guard.', '1702915023-TY 3.webp', 'FALSE', 'Just In'),
('Adidas', 'Samba', 1, 1, 1, 1, 1, 1, 1, 1, 99.99, 180, 'The classic Samba. Originally a football shoe, now a lifestyle icon.', '1702915065-Samba.webp', 'FALSE', 'Bestseller'),
('Adidas', 'JT 1', 1, 1, 1, 1, 1, 0, 0, 0, 129.99, 55, 'Jayson Tatum''s first signature shoe. Built for scoring from anywhere.', '1702915082-JT 1.jpg', 'FALSE', 'Just In'),
('Adidas', 'Luka 2', 1, 1, 1, 1, 1, 1, 0, 0, 139.99, 65, 'Luka Doncic''s 2nd signature shoe. Crafty, creative, and court-ready.', '1702915182-Luka 2.jpg', 'FALSE', 'Bestseller'),
('Adidas', 'Jordan 38', 1, 1, 1, 1, 1, 1, 1, 0, 199.99, 50, 'The Air Jordan 38. Latest in the iconic line with updated performance tech.', '1702915208-Jordan 38.jpg', 'FALSE', 'Just In'),
('Adidas', 'Jordan Legacy 312 Low', 1, 1, 1, 1, 1, 0, 0, 0, 149.99, 60, 'A hybrid of Jordan 1, 3, and Air Alpha Force. Don C''s legacy lives on.', '1702915247-Jordan Legacy 312 Low.webp', 'FALSE', 'Just In'),
('Adidas', 'Powerpuff A', 1, 1, 1, 1, 1, 0, 0, 0, 129.99, 35, 'Powerpuff Girls x Adidas collaboration. Bold, colorful, and fun.', '1702915786-PowerpuffA.webp', 'FALSE', 'Just In'),
('Adidas', 'Powerpuff B', 1, 1, 1, 1, 1, 0, 0, 0, 129.99, 35, 'Powerpuff Girls x Adidas collaboration. Blossom-inspired colorway.', '1702915770-Powerpuffb.webp', 'FALSE', 'Just In'),
('Adidas', 'Powerpuff C', 1, 1, 1, 1, 1, 0, 0, 0, 129.99, 35, 'Powerpuff Girls x Adidas collaboration. Buttercup-inspired colorway.', '1703021412-PowerpuffC.webp', 'FALSE', 'Just In'),
('Adidas', 'ADIZERO SL W', 1, 1, 1, 1, 1, 0, 0, 0, 139.99, 50, 'Lightweight racing flat for women. Built for speed and personal records.', '1702915747-ADIZERO_SL_W_Black_HQ1344_01_standard.jpg', 'FALSE', 'Just In'),
('Adidas', 'Stan Smith', 1, 1, 1, 1, 1, 1, 1, 1, 89.99, 200, 'The iconic Stan Smith. Clean, classic, and sustainable with Primegreen materials.', '1703070084-Stan Smith.webp', 'FALSE', 'Bestseller');
