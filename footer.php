<?php
$current_page = basename($_SERVER['PHP_SELF']);  // Get the current page name
$current_dir = basename(dirname($_SERVER['PHP_SELF']));  // Get the current directory name

// Determine the base path for the image
$image_path = ($current_dir == 'agent' || $current_dir == 'admin') ? '../Images/TransparentLogo.png' : 'Images/TransparentLogo.png';
?>

<footer class="bg-dark text-white p-0" style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);">
    <div class="container-fluid">
        <div class="row">
            <!-- Column 1: Logo and Description (Image next to the text) -->
            <div class="col-12 pt-3 mb-0">
                <div class="col-12 pt-3">
                    <div class="d-flex flex-column flex-lg-row align-items-center justify-content-center" style="padding: 2rem; border-radius: 10px;">
                        <!-- Image Section -->
                        <div class="footer-logo mb-3 mb-lg-0 mr-lg-4">
                            <a href="#">
                                <img class="img-fluid" src="<?php echo $image_path; ?>" alt="Project Estates Logo" style="max-width: 200px;">
                            </a>
                        </div>

                        <!-- Text Section -->
                        <p class="text-center text-lg-left" style="max-width: 70%; line-height: 1.6;">
                        Project Estates is a leading real estate platform dedicated to helping individuals find their dream homes, investment properties, and commercial spaces. With a user-friendly interface, we offer a wide range of properties for sale and rent, ensuring that every client’s needs are met. Whether you're looking to buy, sell, or lease, Project Estates provides comprehensive listings, detailed property descriptions, and expert advice to guide you through every step of the process. Our team is committed to delivering exceptional service and making property transactions simple, efficient, and transparent.
                        </p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center gx-5">
                <!-- Column 2: Support Links -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h4 class="widget-title mb-3 text-center">Support</h4>
                    <hr class="mb-3" style="border-color: #ffffff;">
                    <ul class="list-unstyled text-center">
                        <li class="mb-3 d-none d-lg-block"><a href="#" class="text-white text-decoration-none">Forum</a></li>
                        <li class="mb-3 d-none d-lg-block"><a href="#" class="text-white text-decoration-none">Terms and Conditions</a></li>
                        <li class="mb-3 d-none d-lg-block"><a href="#" class="text-white text-decoration-none">Frequently Asked Questions</a></li>
                        <li class="mb-3 d-none d-lg-block"><a href="contact.php" class="text-white text-decoration-none">Contact</a></li>

                        <!-- Dropdown for smaller screens -->
                        <div class="dropdown d-lg-none">
                            <button class="btn btn-dark dropdown-toggle w-100" type="button" id="supportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Support Links
                            </button>
                            <ul class="dropdown-menu w-100" aria-labelledby="supportDropdown">
                                <li><a class="dropdown-item" href="#">Forum</a></li>
                                <li><a class="dropdown-item" href="#">Terms and Conditions</a></li>
                                <li><a class="dropdown-item" href="#">Frequently Asked Questions</a></li>
                                <li><a class="dropdown-item" href="contact.php">Contact</a></li>
                            </ul>
                        </div>
                    </ul>
                </div>

                <!-- Column 3: Quick Links -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h4 class="widget-title mb-3 text-center">Quick Links</h4>
                    <hr class="mb-3" style="border-color: #ffffff;">
                    <ul class="list-unstyled text-center">
                        <li class="mb-3 d-none d-lg-block"><a href="about.php" class="text-white text-decoration-none">About Us</a></li>
                        <li class="mb-3 d-none d-lg-block"><a href="#" class="text-white text-decoration-none">Featured Properties</a></li>
                        <li class="mb-3 d-none d-lg-block"><a href="#" class="text-white text-decoration-none">Submit Property</a></li>
                        <li class="mb-3 d-none d-lg-block"><a href="agent.php" class="text-white text-decoration-none">Our Agents</a></li>

                        <!-- Dropdown for smaller screens -->
                        <div class="dropdown d-lg-none">
                            <button class="btn btn-dark dropdown-toggle w-100" type="button" id="quickLinksDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Quick Links
                            </button>
                            <ul class="dropdown-menu w-100" aria-labelledby="quickLinksDropdown">
                                <li><a class="dropdown-item" href="about.php">About Us</a></li>
                                <li><a class="dropdown-item" href="#">Featured Properties</a></li>
                                <li><a class="dropdown-item" href="#">Submit Property</a></li>
                                <li><a class="dropdown-item" href="agent.php">Our Agents</a></li>
                            </ul>
                        </div>
                    </ul>
                </div>

                <!-- Column 4: Contact Information and Social Media -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h4 class="widget-title mb-3 text-center">Contact Us</h4>
                    <hr class="mb-3" style="border-color: #ffffff;">
                    <ul class="list-unstyled text-center">
                        <li class="mb-3 d-none d-lg-block"><i class="fas fa-map-marker-alt mr-2 me-2"></i>27 Ingram Street, Dayton</li>
                        <li class="mb-3 d-none d-lg-block"><i class="fas fa-phone-alt mr-2 me-2"></i>+1 234-567-8910</li>
                        <li class="mb-3 d-none d-lg-block"><i class="fas fa-phone-alt mr-2 me-2"></i>+1 243-765-4321</li>
                        <li class="mb-3 d-none d-lg-block"><i class="fas fa-envelope mr-2 me-2"></i>helpline@realestatest.com</li>

                        <!-- Dropdown for smaller screens -->
                        <div class="dropdown d-lg-none">
                            <button class="btn btn-dark dropdown-toggle w-100" type="button" id="contactDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Contact Information
                            </button>
                            <ul class="dropdown-menu w-100" aria-labelledby="contactDropdown">
                                <li><a class="dropdown-item" href="#">27 Ingram Street, Dayton</a></li>
                                <li><a class="dropdown-item" href="#">+1 234-567-8910</a></li>
                                <li><a class="dropdown-item" href="#">+1 243-765-4321</a></li>
                                <li><a class="dropdown-item" href="mailto:helpline@realestatest.com">helpline@realestatest.com</a></li>
                            </ul>
                        </div>
                    </ul>
                </div>
            </div>

            <!-- Horizontal Line Between Rows -->
            <hr class="border-light -0">

            <!-- Row for Copyright and Legal Links -->
            <div class="row copyright justify-content-center">
                <div class="col-sm-12 text-center">
                    <div class="justify-content-center align-items-center">
                        <div class="me-3 pb-2">
                            <span>© <?php echo date('Y'); ?> Project Estates - Developed By Adeyanju Abdulmalik Bamidele</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Privacy Policy & Terms Links -->
            <div class="text-center text-white mt-4">
                <a href="privacy.php" class="text-white text-decoration-none mx-2">Privacy Policy</a> | 
                <a href="terms.php" class="text-white text-decoration-none mx-2">Terms & Conditions</a>
            </div>
        </div>
    </div>    
</footer>

<!-- Ensure Bootstrap JavaScript is included -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
