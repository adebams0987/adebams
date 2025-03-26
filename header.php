<?php
$current_page = basename($_SERVER['PHP_SELF']);  // Get the current page name
$current_dir = basename(dirname($_SERVER['PHP_SELF']));  // Get the current directory name

// Determine the base path for the image
$image_path = ($current_dir == 'agent' || $current_dir == 'admin') ? '../Images/TransparentLogo.png' : 'Images/TransparentLogo.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Estates</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<header class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <!-- Common Logo -->
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="<?php echo $image_path; ?>" alt="Project Estates Logo" style="max-width: 50px;">
            <span class="ms-2 h4 text-white">Project Estates</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">

            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'user')): ?>
                <!-- User Navigation Bar -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'user.php') ? 'active' : ''; ?>" href="user.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'buy.php') ? 'active' : ''; ?>" href="buy.php">Buy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'rent.php') ? 'active' : ''; ?>" href="rent.php">Rent</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'saved_properties.php') ? 'active' : ''; ?>" href="saved_properties.php">Saved Properties</a>
                    </li>
                </ul>

                <!-- Dropdown for User's Profile -->
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-dark dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            My Account
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="user_profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="contact.php">Contact Us</a></li>
                            <li><a class="dropdown-item" href="about.php">About Us</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>

            <?php elseif (isset($_SESSION['role']) && ($_SESSION['role'] == 'agent')): ?>
                <!-- Agent Navigation Bar -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'agent.php') ? 'active' : ''; ?>" href="agent.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'agentProperties.php' || $current_page == 'editProperties.php' || $current_page == 'addProperties.php' || $current_page == 'deleteProperties.php') ? 'active' : ''; ?>" href="agentProperties.php">My Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'agentSchedule.php' || $current_page == 'details.php') ? 'active' : ''; ?>" href="agentSchedule.php">Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'agentAnalytics.php') ? 'active' : ''; ?>" href="agentAnalytics.php">Analytics</a>
                    </li>
                </ul>

                <!-- Dropdown for Agent's Profile -->
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-dark dropdown-toggle" 
                                type="button" 
                                id="agentDropdown" 
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                            My Account
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="agentDropdown">
                            <li><a class="dropdown-item" href="agentProfile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            
                <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <!-- Admin Navigation Bar -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>" href="admin.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (in_array($current_page, ['manageProperties.php', 'editProperties.php', 'deleteProperties.php', 'property_details.php'])) ? 'active' : ''; ?>" href="manageProperties.php">Properties</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'manageUsers.php') ? 'active' : ''; ?>" href="manageUsers.php">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'manageAgents.php') ? 'active' : ''; ?>" href="manageAgents.php">Agents</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-dark dropdown-toggle" type="button" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Admin Panel
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                            <li><a class="dropdown-item" href="adminProfile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>

            <?php else: ?>
                <!-- Default Navigation Bar -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'properties.php') ? 'active' : ''; ?>" href="properties.php">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>" href="contact.php">Contact</a>
                    </li>
                    <?php if (!isset($_SESSION['role'])): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'login.php' || $current_page == 'register.php') ? 'active' : ''; ?>" href="login.php">Login/Register</a>
                        </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['role'])): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'logout.php') ? 'active' : ''; ?>" href="logout.php">Logout</a>
                        </li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>

        </div>
    </div>
</header>

<!-- Include Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
