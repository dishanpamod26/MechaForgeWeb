<?php
session_start();
require_once 'config/db.php';

// Fetch settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Defaults for settings (updated to reflect new assets folders)
$defaults = [
    'slogan' => 'Reliable Engineering. Exceptional Results.',
    'email' => 'contact@mechaforge.com',
    'phone' => '+94 77 123 4567',
    'logo' => 'assets/images/logo.jpeg',
    'backgrounds' => json_encode(['assets/images/hero-bg.png'])
];
foreach ($defaults as $key => $val) {
    if (!isset($settings[$key]) || empty($settings[$key])) {
        $settings[$key] = $val;
    }
}

// Fetch projects
$stmt = $pdo->query("SELECT * FROM projects ORDER BY id DESC");
$projects = $stmt->fetchAll();

$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MechaForge Engineering | Precision Automation Solutions</title>
    <meta name="description"
        content="MechaForge Engineering provides state-of-the-art industrial machine automation solutions. Custom machines, robotics, and smart manufacturing.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Orbitron:wght@400;700&display=swap"
        rel="stylesheet">
    <script src="https://kit.fontawesome.com/d6f3576e01.js" crossorigin="anonymous"></script>
</head>

<body>
    <nav class="navbar">
        <div class="logo">
            <img id="site-logo" src="<?php echo htmlspecialchars($settings['logo']); ?>" alt="MechaForge Logo" style="height: 40px; display: block;">
        </div>
        <ul class="nav-links">
            <li><a href="#home">Home</a></li>
            <li><a href="#projects">Industries</a></li>
            <li><a href="#capabilities">Capabilities</a></li>
            <li><a href="#contact">Contact</a></li>
            <?php if ($isLoggedIn): ?>
                <li><a href="admin/dashboard.php" class="btn-secondary" style="text-decoration: none;">Dashboard</a></li>
            <?php else: ?>
                <li><button id="admin-btn" class="btn-secondary">Admin</button></li>
            <?php endif; ?>
        </ul>
    </nav>

    <section id="home" class="hero">
        <div id="hero-bg-container" class="hero-bg-slideshow">
            <?php 
            $backgrounds = json_decode($settings['backgrounds'], true);
            if (!is_array($backgrounds)) {
                $backgrounds = ['assets/images/hero-bg.png'];
            }
            foreach ($backgrounds as $index => $bg): 
                $activeClass = ($index === 0) ? 'active' : '';
            ?>
                <div class="hero-bg-image <?php echo $activeClass; ?>" style="background-image: url('<?php echo htmlspecialchars($bg); ?>');"></div>
            <?php endforeach; ?>
        </div>
        <div class="hero-content">
            <h1 id="hero-slogan" class="animate-up"><?php echo str_replace('Future', '<span class="gradient-text">Future</span>', htmlspecialchars($settings['slogan'])); ?></h1>
            <p class="animate-up delay-1">At Mecha Forge Engineering, we specialize in precision engineering, stainless steel fabrication, industrial machinery, and custom-built solutions for modern industries. Proudly manufacturing in Sri Lanka, we combine quality craftsmanship, innovation, and technical expertise to deliver reliable, durable, and high-performance engineering solutions tailored to our clients’ needs. We are committed to maintaining the highest level of quality and reliability to earn and uphold our customers’ trust in every project we undertake.</p>
            <div class="hero-btns animate-up delay-2">
                <a href="#projects" class="btn-primary">View Projects</a>
                <a href="#contact" class="btn-outline">Get a Quote</a>
            </div>
        </div>
        <div class="hero-overlay"></div>
    </section>

    <section id="projects" class="section">
        <div class="container">
            <div class="section-header">
                <h2>Industries <span class="gradient-text">We Service</span></h2>
                <p>Explore our latest machine builds and automated systems.</p>
            </div>
            <div id="projects-grid" class="projects-grid">
                <?php if (empty($projects)): ?>
                    <p style="text-align: center; grid-column: 1/-1; color: var(--text-dim);">No projects uploaded yet.</p>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <div class="project-card animate-up">
                            <a href="project.php?id=<?php echo $project['id']; ?>" target="_blank" style="text-decoration: none; color: inherit; display: block;">
                                <?php if ($project['type'] === 'video'): ?>
                                    <video class="project-media" src="<?php echo htmlspecialchars($project['url']); ?>" muted loop onmouseover="this.play()" onmouseout="this.pause()"></video>
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars($project['url']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="project-media">
                                <?php endif; ?>
                                <div class="project-info">
                                    <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                                    <p><?php echo htmlspecialchars(strlen($project['description']) > 100 ? substr($project['description'], 0, 100) . '...' : $project['description']); ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section id="capabilities" class="section bg-dark">
        <div class="container">
            <div class="section-header">
                <h2>Our <span class="gradient-text">Capabilities</span></h2>
                <p style="max-width: 900px; margin: 1rem auto 0; color: var(--text-dim); text-align: center;">At Mecha Forge Engineering, we specialize in precision engineering, stainless steel fabrication, industrial machinery, and custom-built solutions for modern industries. Proudly manufacturing in Sri Lanka, we combine quality craftsmanship, innovation, and technical expertise to deliver reliable, durable, and high-performance engineering solutions tailored to our clients’ needs. We are committed to maintaining the highest level of quality and reliability to earn and uphold our customers’ trust in every project we undertake.</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <i class="fas fa-industry"></i>
                    <h3>Manufacturing & Industrial Sector</h3>
                    <p>Supporting modern industries with high-quality stainless steel fabrication, industrial equipment, and custom-engineered solutions designed for durability, efficiency, and long-term performance. All products are locally manufactured in Sri Lanka using precision fabrication techniques and reliable engineering standards.</p>
                    <div class="products-include">
                        <h4>Products Include:</h4>
                        <ul>
                            <li>Stainless Steel Work Tables</li>
                            <li>Industrial Workstations</li>
                            <li>SS Cabinets</li>
                            <li>Machine Frames</li>
                            <li>Material Handling Trolleys</li>
                            <li>Industrial Platforms</li>
                            <li>Safety Guarding Systems</li>
                            <li>Storage Units</li>
                            <li>Structural Fabrication</li>
                            <li>Custom Industrial Equipment</li>
                        </ul>
                    </div>
                </div>
                <div class="service-card">
                    <i class="fas fa-hospital"></i>
                    <h3>Healthcare & Hospital Sector</h3>
                    <p>Providing stainless steel hospital equipment, medical support systems, and healthcare-related machinery designed for hospitals, medical centers, and healthcare facilities. Products are manufactured with hygienic finishing, functionality, and durability to support modern healthcare requirements.</p>
                    <div class="products-include">
                        <h4>Products Include:</h4>
                        <ul>
                            <li>Hospital Beds</li>
                            <li>Medical Trolleys</li>
                            <li>Instrument Cabinets</li>
                            <li>Scrub Stations</li>
                            <li>Stainless Steel Wash Units</li>
                            <li>Medical Storage Cabinets</li>
                            <li>Utility Trolleys</li>
                            <li>Sterile Storage Systems</li>
                            <li>Medical Workstations</li>
                            <li>Custom Hospital Machines</li>
                        </ul>
                    </div>
                </div>
                <div class="service-card">
                    <i class="fas fa-flask"></i>
                    <h3>Laboratory & Research Facilities</h3>
                    <p>Providing advanced laboratory equipment, stainless steel laboratory furniture, and research support systems for laboratories, educational institutions, and scientific research facilities. All products are locally manufactured in Sri Lanka with attention to safety, functionality, and precision engineering.</p>
                    <div class="products-include">
                        <h4>Products Include:</h4>
                        <ul>
                            <li>Biosafety Cabinets (BSC)</li>
                            <li>Laminar Air Flow Cabinets (LAF)</li>
                            <li>Laboratory Work Benches</li>
                            <li>Fume Hoods</li>
                            <li>Chemical Storage Cabinets</li>
                            <li>Laboratory Sinks</li>
                            <li>Island Benches</li>
                            <li>Mobile Trolleys</li>
                            <li>Storage Cabinets</li>
                            <li>Custom Laboratory Equipment</li>
                        </ul>
                    </div>
                </div>
                <div class="service-card">
                    <i class="fas fa-fan"></i>
                    <h3>Clean Room & Controlled Environment</h3>
                    <p>Providing precision-fabricated clean room equipment and controlled environment solutions for pharmaceutical, laboratory, healthcare, and industrial applications. Every solution is locally manufactured in Sri Lanka with hygienic finishing and compliance with clean room standards.</p>
                    <div class="products-include">
                        <h4>Products Include:</h4>
                        <ul>
                            <li>Pass Boxes</li>
                            <li>Air Showers</li>
                            <li>Clean Room Furniture</li>
                            <li>Stainless Steel Cabinets</li>
                            <li>Garment Storage Units</li>
                            <li>Dynamic & Static Pass Boxes</li>
                            <li>HEPA Filter Housings</li>
                            <li>Material Transfer Units</li>
                            <li>Clean Room Workstations</li>
                            <li>Custom Clean Room Equipment</li>
                        </ul>
                    </div>
                </div>
                <div class="service-card">
                    <i class="fas fa-utensils"></i>
                    <h3>Food Processing & Commercial Kitchen</h3>
                    <p>Delivering durable stainless steel equipment and fabrication solutions for food processing facilities, restaurants, hotels, factories, and commercial kitchens with emphasis on hygiene, efficiency, and long-term performance.</p>
                    <div class="products-include">
                        <h4>Products Include:</h4>
                        <ul>
                            <li>Pantries</li>
                            <li>Kitchen Work Tables</li>
                            <li>Commercial Sinks</li>
                            <li>Exhaust Hoods</li>
                            <li>Storage Cabinets</li>
                            <li>Dish Racks</li>
                            <li>Food Preparation Tables</li>
                            <li>Stainless Steel Shelving</li>
                            <li>Trolley Systems</li>
                            <li>Custom Kitchen Equipment</li>
                        </ul>
                    </div>
                </div>
                <div class="service-card">
                    <i class="fas fa-hard-hat"></i>
                    <h3>Construction & Engineering Sector</h3>
                    <p>Supporting construction and engineering projects with stainless steel fabrication, structural solutions, and custom metal works designed for reliability, strength, and professional finishing. All products are locally manufactured in Sri Lanka to meet project-specific requirements.</p>
                    <div class="products-include">
                        <h4>Products Include:</h4>
                        <ul>
                            <li>Canopies</li>
                            <li>Handrails</li>
                            <li>Guard Rails</li>
                            <li>Structural Frames</li>
                            <li>Stainless Steel Stair Components</li>
                            <li>Roofing Structures</li>
                            <li>Support Systems</li>
                            <li>Metal Covers</li>
                            <li>Industrial Fabrication Components</li>
                            <li>Custom Structural Works</li>
                        </ul>
                    </div>
                </div>
                <div class="service-card">
                    <i class="fas fa-tractor"></i>
                    <h3>Agriculture & Cultural Equipment</h3>
                    <p>Providing durable agricultural and cultural equipment, custom machinery, and stainless steel fabrication solutions to support farming, processing, and industrial operational requirements with reliable performance and practical functionality.</p>
                    <div class="products-include">
                        <h4>Products Include:</h4>
                        <ul>
                            <li>Agricultural Processing Machines</li>
                            <li>Stainless Steel Tanks</li>
                            <li>Drying Equipment</li>
                            <li>Custom Fabricated Machines</li>
                            <li>Storage Systems</li>
                            <li>Material Handling Equipment</li>
                            <li>Washing Units</li>
                            <li>Processing Tables</li>
                            <li>Special Purpose Agricultural Equipment</li>
                        </ul>
                    </div>
                </div>
                <div class="service-card">
                    <i class="fas fa-robot"></i>
                    <h3>Industrial Automation & Smart Solutions</h3>
                    <p>Offering innovative manufacturing solutions designed to optimize production processes, improve operational efficiency, and support smart industrial operations through precision-engineered systems and modern fabrication technologies.</p>
                    <div class="products-include">
                        <h4>Products Include:</h4>
                        <ul>
                            <li>Custom Production Machines</li>
                            <li>Smart Material Handling Systems</li>
                            <li>Conveyor Solutions</li>
                            <li>Process Automation Equipment</li>
                            <li>Assembly Support Machines</li>
                            <li>Industrial Machine Frames</li>
                            <li>Semi-Automated Systems</li>
                            <li>Production Support Equipment</li>
                            <li>Custom Engineering Solutions</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="trusted-brands" class="section">
        <div class="container">
            <div class="section-header">
                <h2>Trusted brands <span class="gradient-text">we work with</span></h2>
            </div>
            <div class="brands-marquee">
                <img src="assets/images/brands/WhatsApp Image 2026-05-17 at 18.35.34 (1).jpeg" alt="Brand 1">
                <img src="assets/images/brands/WhatsApp Image 2026-05-17 at 18.35.34 (2).jpeg" alt="Brand 2">
                <img src="assets/images/brands/WhatsApp Image 2026-05-17 at 18.35.34 (3).jpeg" alt="Brand 3">
                <img src="assets/images/brands/WhatsApp Image 2026-05-17 at 18.35.34.jpeg" alt="Brand 4">
            </div>
        </div>
    </section>

    <section id="contact" class="section">
        <div class="container">
            <div class="contact-card glass">
                <div class="contact-info">
                    <h2>Work with <span class="gradient-text">Experts</span></h2>
                    <p>Ready to automate? Contact us for a consultation.</p>
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <span id="contact-email"><?php echo htmlspecialchars($settings['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <span id="contact-phone"><?php echo htmlspecialchars($settings['phone']); ?></span>
                    </div>
                </div>
                <form class="contact-form" id="contactForm">
                    <input type="text" id="waName" placeholder="Your Name" required>
                    <input type="email" id="waEmail" placeholder="Your Email" required>
                    <textarea id="waMessage" placeholder="Project Details" rows="5" required></textarea>
                    <button type="submit" class="btn-primary">Send via WhatsApp <i class="fab fa-whatsapp"></i></button>
                </form>
            </div>
        </div>
    </section>

    <!-- Admin Modal -->
    <div id="admin-modal" class="modal">
        <div id="modal-container" class="modal-content glass">
            <span class="close-modal">&times;</span>
            <form action="admin/login.php" method="POST" id="login-form" class="login-box">
                <h3><i class="fas fa-lock"></i> Admin Access</h3>
                <?php if (isset($_GET['login_error'])): ?>
                    <p id="login-error-msg" style="color: var(--primary); font-size: 0.9rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($_GET['login_error']); ?></p>
                <?php endif; ?>
                <div class="input-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">Enter Control Center</button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 MechaForge Engineering. All Rights Reserved.</p>
    </footer>

    <script>
        // Dynamically pass phone number from PHP to JS for WhatsApp API
        const siteSettings = {
            phone: <?php echo json_encode($settings['phone']); ?>
        };
    </script>
    <script src="assets/js/script.js"></script>
</body>

</html>
