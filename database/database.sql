CREATE DATABASE IF NOT EXISTS ar_tourism CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ar_tourism;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'editor') DEFAULT 'admin',
    status ENUM('active', 'suspended') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE destinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    short_description TEXT,
    full_description LONGTEXT,
    cover_image VARCHAR(255),
    location VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    google_maps_url VARCHAR(255),
    category_id INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attractions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destination_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    short_description TEXT,
    full_description LONGTEXT,
    main_image VARCHAR(255),
    youtube_url VARCHAR(255),
    website_url VARCHAR(255),
    google_maps_url VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    opening_hours VARCHAR(255),
    entry_information VARCHAR(255),
    category VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    INDEX idx_destination (destination_id),
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attraction_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attraction_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption VARCHAR(255),
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attraction_id) REFERENCES attractions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ar_posters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    description TEXT,
    poster_image VARCHAR(255),
    target_file VARCHAR(255),
    target_status ENUM('not_compiled', 'compiling', 'ready') DEFAULT 'not_compiled',
    target_compiled_at DATETIME,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ar_hotspots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poster_id INT NOT NULL,
    attraction_id INT,
    hotspot_name VARCHAR(100),
    x DECIMAL(5, 4) DEFAULT 0.5,
    y DECIMAL(5, 4) DEFAULT 0.5,
    width DECIMAL(5, 4) DEFAULT 0.2,
    height DECIMAL(5, 4) DEFAULT 0.2,
    content_type ENUM('info', 'video', 'image') DEFAULT 'info',
    z_index INT DEFAULT 1,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (poster_id) REFERENCES ar_posters(id) ON DELETE CASCADE,
    FOREIGN KEY (attraction_id) REFERENCES attractions(id) ON DELETE SET NULL,
    INDEX idx_poster (poster_id),
    INDEX idx_attraction (attraction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_status ON users(status);

INSERT INTO users (username, email, password, full_name, role, status) VALUES
('admin', 'admin@artourism.com', '$2a$12$QUOLL1zzYcuy6Ue9hEH3K.M609ar5W7laRc6TtUI9/QnnIt/l9f.O', 'System Administrator', 'admin', 'active');

INSERT INTO categories (name, slug, description, icon, status, display_order) VALUES
('Historical', 'historical', 'Ancient temples, colonial buildings, and heritage sites', 'fa-landmark', 'active', 1),
('Nature', 'nature', 'Parks, gardens, mountains, and natural wonders', 'fa-mountain-sun', 'active', 2),
('Cultural', 'cultural', 'Museums, cultural villages, and traditional sites', 'fa-theater-masks', 'active', 3),
('Food', 'food', 'Local cuisine, hawker centers, and food streets', 'fa-utensils', 'active', 4),
('Beach', 'beach', 'Beaches, coastal areas, and water activities', 'fa-umbrella-beach', 'active', 5),
('Religious', 'religious', 'Temples, mosques, churches, and places of worship', 'fa-place-of-worship', 'active', 6),
('Architecture', 'architecture', 'Notable buildings, bridges, and architectural marvels', 'fa-building', 'active', 7),
('Shopping', 'shopping', 'Markets, malls, and shopping destinations', 'fa-shopping-bag', 'active', 8),
('Adventure', 'adventure', 'Adventure parks, outdoor activities, and thrills', 'fa-hiking', 'active', 9);

INSERT INTO destinations (name, slug, short_description, full_description, location, latitude, longitude, google_maps_url, category_id, status) VALUES
('Heritage District', 'heritage-district', 'Explore the rich colonial and cultural heritage of George Town', 'George Town, the capital of Penang, is a UNESCO World Heritage site known for its well-preserved colonial architecture, vibrant street art, and multicultural heritage. The Heritage District encompasses centuries-old shophouses, temples, mosques, and colonial buildings that tell the story of Penang\'s colorful past.', 'George Town, Penang', 5.4141, 100.3288, 'https://maps.google.com/?q=George+Town+Penang', 1, 'active'),
('Nature Escape', 'nature-escape', 'Discover Penang\'s natural beauty from hill stations to botanical gardens', 'Penang offers stunning natural attractions from the cool highlands of Penang Hill to the lush greenery of the Botanical Gardens. Nature enthusiasts can explore rainforests, orchid valleys, and enjoy panoramic views of the island and mainland.', 'Penang Hill & Surrounds', 5.4225, 100.2739, 'https://maps.google.com/?q=Penang+Hill', 2, 'active'),
('Cultural Village', 'cultural-village', 'Experience traditional Malay, Chinese, and Indian cultures', 'Penang is a melting pot of cultures, with beautiful temples, mosques, and churches standing side by side. Experience traditional crafts, watch cultural performances, and taste authentic cuisine from different communities.', 'George Town, Penang', 5.4164, 100.3371, 'https://maps.google.com/?q=Penang+Cultural+Village', 3, 'active'),
('Coastal Paradise', 'coastal-paradise', 'Beautiful beaches and coastal attractions', 'Penang\'s coastline offers pristine beaches, seafood restaurants, and stunning views of the Penang Bridge. From the popular Batu Ferringhi to quieter beaches like Teluk Bahang, there\'s something for everyone.', 'Batu Ferringhi, Penang', 5.4697, 100.2931, 'https://maps.google.com/?q=Batu+Ferringhi+Penang', 5, 'active'),
('Food Heritage Area', 'food-heritage', 'Explore Penang\'s world-renowned street food scene', 'Penang is被称为 a food paradise with its hawker centers, night markets, and famous street food. From char kway teow to laksa, satay bee hoon to cendol, every corner offers a delicious culinary adventure.', 'Chulia Street, George Town', 5.4184, 100.3358, 'https://maps.google.com/?q=Chulia+Street+Food+Penang', 4, 'active');

INSERT INTO attractions (destination_id, name, slug, short_description, full_description, category, opening_hours, entry_information, latitude, longitude, google_maps_url, youtube_url, website_url, status, display_order) VALUES
(1, 'Kek Lok Si Temple', 'kek-luo-si-temple', 'Penang\'s largest and most beautiful Buddhist temple complex', 'Kek Lok Si Temple is the largest Buddhist temple in Penang and one of the most significant in Southeast Asia. Built in 1890s, the temple complex features stunning Chinese, Thai, and Burmese architectural styles. The famous 30-meter tall statue of Guanyin (Goddess of Mercy) and the seven-storey Pagoda of Friendship are must-see attractions.', 'Historical', '9:00 AM - 10:00 PM', 'Free entry', 5.4015, 100.2750, 'https://maps.google.com/?q=Kek+Luo+Si+Temple+Penang', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'https://keklotsi.org.my', 'active', 1),
(1, 'Komtar Tower', 'komtar-tower', 'Iconic skyscraper with observation deck and shopping mall', 'Komtar is Penang\'s most iconic landmark, standing at 65 stories tall. The observation deck on the 71st floor offers panoramic views of George Town and the surrounding island. The complex also houses a shopping mall, cinema, and various restaurants.', 'Architecture', '10:00 AM - 10:00 PM', 'RM 10 for observation deck', 5.4142, 100.3299, 'https://maps.google.com/?q=Komtar+Penang', NULL, 'https://www.komtar.com.my', 'active', 2),
(1, 'Penang Heritage Trams', 'penang-heritage-trams', 'Vintage trams offering tours through George Town', 'Experience George Town like never before aboard the Heritage Trams. These vintage trams take you on a guided tour through the historic streets, stopping at major landmarks and providing commentary about the area\'s rich history and cultural significance.', 'Cultural', '10:00 AM - 6:00 PM', 'RM 20 per person', 5.4164, 100.3371, 'https://maps.google.com/?q=Penang+Heritage+Tram', NULL, NULL, 'active', 3),
(1, 'Cheong Fatt Tze Mansion', 'cheong-fatt-tze-mansion', 'Historic blue mansion showcasing Peranakan heritage', 'Also known as the Blue Mansion, this UNESCO-recognized heritage building is a stunning example of Peranakan architecture. The mansion features intricate wood carvings, beautiful courtyards, and exhibits that tell the story of the influential Cheong family.', 'Historical', '9:00 AM - 6:00 PM', 'RM 25 adults, RM 15 children', 5.4156, 100.3369, 'https://maps.google.com/?q=Cheong+Fatt+Tze+Mansion', NULL, 'https://bluemansion.com.my', 'active', 4),
(1, 'The Clan Jetties', 'the-clan-jetties', 'Traditional floating villages on stilts', 'The Clan Jetties are a unique collection of traditional Chinese clan houses built on stilts over the water. These floating villages offer a glimpse into Penang\'s maritime heritage and are home to communities that have lived there for generations.', 'Cultural', '24 hours', 'Free entry', 5.4170, 100.3400, 'https://maps.google.com/?q=Clan+Jetties+Penang', NULL, NULL, 'active', 5),

(2, 'Penang Hill', 'penang-hill', 'Cool highland retreat with panoramic views', 'Penang Hill (Bukit Bendera) is a popular highland retreat just 835 meters above sea level. The funicular train takes visitors from the base to the summit in about 10 minutes. At the top, visitors can enjoy cool temperatures, lush forests, and breathtaking views of the island and straits.', 'Nature', '8:00 AM - 10:00 PM', 'RM 30 return ticket', 5.4225, 100.2739, 'https://maps.google.com/?q=Penang+Hill', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'https://www.penanghill.com.my', 'active', 1),
(2, 'Penang Botanical Gardens', 'penang-botanical-gardens', 'Lush gardens with diverse plant collections', 'Established in 1884, the Penang Botanical Gardens span 60 hectares and feature an impressive collection of tropical plants, orchids, and trees. The Orchid Valley and Fern Valley are highlights, and the hilltop offers stunning views of the island.', 'Nature', '8:00 AM - 6:00 PM', 'RM 2 entry fee', 5.4253, 100.2709, 'https://maps.google.com/?q=Penang+Botanical+Gardens', NULL, NULL, 'active', 2),
(2, 'Batu Ferringhi Beach', 'batu-ferringhi-beach', 'Popular beach destination with water sports', 'Batu Ferringhi is Penang\'s most popular beach area, stretching along the northwest coast. Known for its white sandy beach, colorful parasols, and vibrant night market. Water sports like parasailing and jet skiing are available.', 'Beach', '24 hours', 'Free entry', 5.4697, 100.2931, 'https://maps.google.com/?q=Batu+Ferringhi+Beach', NULL, NULL, 'active', 3),
(2, 'Bukit Bendera Rising Landslide Trail', 'bukit-bendera-trail', 'Scenic hiking trail through rainforest', 'This nature trail takes hikers through the lush rainforest of Penang Hill, offering opportunities to spot wildlife and enjoy the cool mountain air. The trail ranges from easy to moderate difficulty.', 'Nature', '7:00 AM - 6:00 PM', 'Free entry', 5.4280, 100.2650, 'https://maps.google.com/?q=Bukit+Bendera+Trail', NULL, NULL, 'active', 4),

(3, 'Penang Islamic Museum', 'penang-islamic-museum', 'Showcasing Islamic heritage in Penang', 'The Penang Islamic Museum is housed in a beautiful colonial building and showcases the history and culture of Islam in Penang. Exhibits include Quranic manuscripts, traditional Islamic artifacts, and information about Muslim communities in the region.', 'Religious', '9:00 AM - 5:00 PM', 'RM 5 entry fee', 5.4144, 100.3297, 'https://maps.google.com/?q=Penang+Islamic+Museum', NULL, NULL, 'active', 1),
(3, 'Sri Mahamariamman Temple', 'sri-mahamariamman-temple', 'Oldest Hindu temple in Penang', 'Built in 1833, the Sri Mahamariamman Temple is the oldest Hindu temple in Penang. The temple is a fine example of Dravidian architecture with intricate sculptures and vibrant colors adorning its gopuram (tower).', 'Religious', '6:00 AM - 9:00 PM', 'Free entry', 5.4153, 100.3380, 'https://maps.google.com/?q=Sri+Mahamariamman+Temple+Penang', NULL, NULL, 'active', 2),
(3, 'Penang Peranakan Mansion', 'penang-peranakan-mansion', 'Museum of Peranakan culture and artifacts', 'This museum showcases the unique Peranakan (Straits Chinese) culture with an impressive collection of antique furniture, ceramics, and household items. The mansion itself is a beautiful example of Peranakan architectural style.', 'Cultural', '9:00 AM - 6:00 PM', 'RM 20 adults', 5.4147, 100.3356, 'https://maps.google.com/?q=Penang+Peranakan+Mansion', NULL, NULL, 'active', 3),
(3, 'Kapitan Keling Mosque', 'kapitan-keling-mosque', 'Beautiful mosque with Indian Muslim heritage', 'The Kapitan Keling Mosque is one of the oldest mosques in Penang, built by the Indian Muslim community in the early 19th century. Its distinctive red brick architecture and elegant domes make it a notable landmark.', 'Religious', '9:00 AM - 5:00 PM', 'Free entry (modest dress required)', 5.4138, 100.3375, 'https://maps.google.com/?q=Kapitan+Keling+Mosque+Penang', NULL, NULL, 'active', 4),

(4, 'Penang Bridge', 'penang-bridge', 'Iconic bridge connecting Penang island to mainland', 'The Penang Bridge is one of the longest bridges in Southeast Asia, spanning 13.5 kilometers over the Penang Strait. It connects the island of Penang to the mainland and offers stunning views, especially at sunset.', 'Architecture', '24 hours', 'Toll fees apply', 5.4536, 100.2967, 'https://maps.google.com/?q=Penang+Bridge', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'https://www.bumiputera-combines.com', 'active', 1),
(4, 'Gurney Drive Park', 'gurney-drive-park', 'Waterfront park with sea views', 'Gurney Drive Park is a popular waterfront park offering beautiful sea views and a relaxed atmosphere. The nearby Gurney Drive Hawker Centre is famous for its street food, especially the grilled stingray.', 'Beach', '24 hours', 'Free entry', 5.4333, 100.3089, 'https://maps.google.com/?q=Gurney+Drive+Penang', NULL, NULL, 'active', 2),
(4, 'Teluk Bahang Naval Museum', 'teluk-bahang-naval-museum', 'Former naval base turned museum', 'The Teluk Bahang Naval Museum is housed in a former Royal Navy base and showcases Penang\'s maritime military history. Exhibits include weapons, vehicles, and aircraft from different eras.', 'Historical', '9:00 AM - 5:00 PM', 'RM 3 entry fee', 5.4667, 100.2500, 'https://maps.google.com/?q=Teluk+Bahang+Naval+Museum', NULL, NULL, 'active', 3),

(5, 'Chulia Street Food Street', 'chulia-street-food', 'Famous night food street in George Town', 'Chulia Street is Penang\'s most famous food street, coming alive at night with hawker stalls selling local delicacies. Must-try items include char kway teow, satay bee hoon, Hokkien mee, and various Chinese and Malay dishes.', 'Food', '5:00 PM - 2:00 AM', 'Free entry', 5.4184, 100.3358, 'https://maps.google.com/?q=Chulia+Street+Food+Penang', NULL, NULL, 'active', 1),
(5, 'Gurney Drive Hawker Centre', 'gurney-drive-hawker', 'Popular hawker center with diverse local food', 'Gurney Drive Hawker Centre is one of Penang\'s most popular food destinations, offering a wide variety of local specialties. The grilled stingray is a must-try, along with various seafood dishes and local desserts.', 'Food', '4:00 PM - 2:00 AM', 'Free entry', 5.4333, 100.3089, 'https://maps.google.com/?q=Gurney+Drive+Hawker+Centre', NULL, NULL, 'active', 2),
(5, 'New Lane Hawker Centre', 'new-lane-hawker', 'Local favorite for authentic Penang food', 'New Lane Hawker Centre is a local favorite, offering authentic Penang dishes at affordable prices. Known for its chicken rice, roti canai, and various traditional snacks.', 'Food', '6:00 AM - 3:00 PM', 'Free entry', 5.4175, 100.3320, 'https://maps.google.com/?q=New+Lane+Hawker+Penang', NULL, NULL, 'active', 3),
(5, 'Air Itam Laksa', 'air-itam-laksa', 'Famous laksa stall near Kek Lok Si', 'This legendary laksa stall near Kek Lok Si Temple has been serving delicious Penang laksa for decades. The noodles are made fresh daily, and the soup is known for its perfect balance of sour and spicy flavors.', 'Food', '9:00 AM - 5:00 PM', 'Free entry', 5.4025, 100.2745, 'https://maps.google.com/?q=Air+Itam+Laksa+Penang', NULL, NULL, 'active', 4);

INSERT INTO ar_posters (name, slug, description, poster_image, target_file, target_status, target_compiled_at, status) VALUES
('Penang Heritage Tour Poster', 'penang-heritage-tour-poster', 'AR poster for Penang Heritage District tourism brochure', 'poster-heritage-tour.jpg', NULL, 'not_compiled', NULL, 'active'),
('Penang Hill Experience Poster', 'penang-hill-experience-poster', 'AR poster for Penang Hill promotional material', 'poster-penang-hill.jpg', NULL, 'not_compiled', NULL, 'active'),
('Penang Food Guide Poster', 'penang-food-guide-poster', 'AR poster for Penang street food guide', 'poster-food-guide.jpg', NULL, 'not_compiled', NULL, 'active');

INSERT INTO settings (setting_key, setting_value, description) VALUES
('site_title', 'AR Tourism Explorer', 'Website title'),
('site_description', 'Discover destinations through augmented reality', 'Website description'),
('site_keywords', 'AR, tourism, augmented reality, heritage, culture', 'SEO keywords'),
('contact_email', 'info@artourism.example.com', 'Contact email address'),
('contact_phone', '+60 12-345 6789', 'Contact phone number'),
('default_language', 'en', 'Default website language');
