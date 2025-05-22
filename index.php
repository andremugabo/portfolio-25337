<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch data from backend API
$api_url = 'http://localhost:3001/api/profile';
$json_data = file_get_contents($api_url);

if ($json_data === false) {
    die('Failed to fetch data from API. Check if the API endpoint is correct and accessible.');
}

$profile_data = json_decode($json_data, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die('Invalid JSON response: ' . json_last_error_msg());
}

// Debug: Uncomment to see the complete API response structure
//echo '<pre>'; print_r($profile_data); echo '</pre>'; exit;

// Safely extract nested user data with proper validation
$user = $profile_data['user'] ?? [];
$name = $user['name'] ?? 'Unknown';
$title = $user['title'] ?? '';
$about = $user['about'] ?? '';
$picture = $user['picture'] ?? 'assets/images/profile.jpg';
$location = $user['location'] ?? '';
$availability = $user['availability'] ?? '';

// Extract other nested data
$contact = isset($profile_data['contact']) && is_array($profile_data['contact']) ? $profile_data['contact'] : [];
$socials = isset($profile_data['socials']) && is_array($profile_data['socials']) ? $profile_data['socials'] : [];
$skills = isset($profile_data['skills']) && is_array($profile_data['skills']) ? $profile_data['skills'] : [];
$experience = isset($profile_data['experience']) && is_array($profile_data['experience']) ? $profile_data['experience'] : [];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($name); ?> - Portfolio</title>
    <style>
        <?php include 'styles.css'; ?>
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo"><?php echo htmlspecialchars($name); ?></div>
        <ul class="nav-links">
            <li><a href="#about">About</a></li>
            <li><a href="#skills">Skills</a></li>
            <li><a href="#experience">Experience</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
        <div class="theme-switch">
            <label>
                <input type="checkbox" id="theme-toggle">
                <span>Dark Mode</span>
            </label>
        </div>
    </nav>

    <div id="app">
        <header id="about">
            <div class="description">
                <h1>Hi, I'm <span><?php echo htmlspecialchars($name); ?></span></h1>
                <h2><?php echo htmlspecialchars($title); ?></h2>
                <p><?php echo htmlspecialchars($about); ?></p>
                <p><i><?php echo htmlspecialchars($location); ?></i> | <?php echo htmlspecialchars($availability); ?></p>
                <button id="contact-btn">Contact Me</button>
            </div>
            <div class="profileImage">
                <img src="<?php echo htmlspecialchars($picture); ?>" alt="<?php echo htmlspecialchars($name); ?>">
            </div>
        </header>

        <section id="skills">
            <h2>Skills</h2>
            <div id="skills-container">
                <?php if (!empty($skills) && is_array($skills)): ?>
                    <?php foreach ($skills as $category => $items): ?>
                        <div>
                            <h3><?php echo htmlspecialchars($category); ?></h3>
                            <ul>
                                <?php if (is_array($items)): ?>
                                    <?php foreach ($items as $skill): ?>
                                        <li><?php echo htmlspecialchars($skill); ?></li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li><?php echo htmlspecialchars($items); ?></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No skills information available</p>
                <?php endif; ?>
            </div>
        </section>

        <section id="experience">
            <h2>Experience</h2>
            <div id="experience-container">
                <?php if (!empty($experience) && is_array($experience)): ?>
                    <?php foreach ($experience as $job): ?>
                        <div>
                            <h3><?php echo htmlspecialchars($job['role'] ?? 'Unknown position'); ?></h3>
                            <h4><?php echo htmlspecialchars($job['company'] ?? 'Unknown company'); ?></h4>
                            <p><em><?php echo htmlspecialchars($job['duration'] ?? 'Duration not specified'); ?></em></p>
                            <p><?php echo htmlspecialchars($job['details'] ?? 'No details provided'); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No experience information available</p>
                <?php endif; ?>
            </div>
        </section>

        <section id="contact">
            <h2>Contact</h2>
            <div>
                <p>Email: <?php echo htmlspecialchars($contact['email'] ?? 'Not provided'); ?></p>
                <p>Phone: <?php echo htmlspecialchars($contact['phone'] ?? 'Not provided'); ?></p>
            </div>
            <div id="social-links">
                <?php if (!empty($socials['github'])): ?>
                    <a href="<?php echo htmlspecialchars($socials['github']); ?>" target="_blank">GitHub</a>
                <?php endif; ?>
                <?php if (!empty($socials['linkedin'])): ?>
                    <a href="<?php echo htmlspecialchars($socials['linkedin']); ?>" target="_blank">LinkedIn</a>
                <?php endif; ?>
                <?php if (!empty($socials['twitter'])): ?>
                    <a href="<?php echo htmlspecialchars($socials['twitter']); ?>" target="_blank">Twitter</a>
                <?php endif; ?>
                <?php if (!empty($socials['dribbble'])): ?>
                    <a href="<?php echo htmlspecialchars($socials['dribbble']); ?>" target="_blank">Dribbble</a>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($name); ?>
    </footer>

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;

        // Check for saved theme preference or use preferred color scheme
        const savedTheme = localStorage.getItem('theme') || 
                         (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
        if (savedTheme) {
            html.setAttribute('data-theme', savedTheme);
            themeToggle.checked = (savedTheme === 'dark');
        }

        themeToggle.addEventListener('change', () => {
            const newTheme = themeToggle.checked ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>