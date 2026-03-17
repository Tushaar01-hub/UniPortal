<header class="main-header">
    <div class="top-bar">
        <div class="container">
            <div class="contact-info">
                <span>Contact Us: 0172-2993512</span>
            </div>
            <div class="header-right">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="logout.php" class="btn-small">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn-small">Login</a>
                <?php endif; ?>
                <select class="language-selector">
                    <option>Select Language</option>
                    <option value="en">English</option>
                    <option value="hi">Hindi</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="header-main">
        <div class="container">
            <div class="logo-section">
                <img src="assets/images/haryana-logo.png" alt="Haryana Logo" class="logo" onerror="this.style.display='none'">
                <div class="title-section">
                    <h1>Haryana State Board of Technical Education</h1>
                    <p>Department of Technical Education, Government of Haryana</p>
                    <p class="address">Government Polytechnic Campus, Sector 26, Panchkula Extension, Haryana - 134116</p>
                </div>
                <div class="header-logos">
                    <img src="assets/images/g2-logo.png" alt="G2 Logo" class="header-logo" onerror="this.style.display='none'">
                    <img src="assets/images/india-logo.png" alt="India Logo" class="header-logo" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </div>
    
    <nav class="main-nav">
        <div class="container">
            <ul class="nav-menu">
                <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">HOME</a></li>
                <li><a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">ABOUT</a></li>
                <li><a href="institutes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'institutes.php' ? 'active' : ''; ?>">INSTITUTES</a></li>
                <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'faculty'): ?>
                    <li><a href="faculty/dashboard.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'faculty') !== false ? 'active' : ''; ?>">FACULTY</a></li>
                <?php endif; ?>
                <li><a href="#">SYLLABUS</a></li>
                <li><a href="#">EXAMINATIONS</a></li>
                <li><a href="#">RESULT</a></li>
                <li><a href="#">STUDENTS TRAINING</a></li>
                <li><a href="#">PAY FEE ONLINE</a></li>
                <li><a href="#">DIGITAL ELEARNING</a></li>
            </ul>
        </div>
    </nav>
</header>
