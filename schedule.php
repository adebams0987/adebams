<?php
session_start();
include "pdo.php";
include "processSchedule.php";
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule a Viewing or Consultation</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Favicon Links -->
    <link rel="apple-touch-icon" sizes="180x180" href="/Fonts/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/Fonts/favicon-32x32.png">

    <!-- CSS Links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .appointment-card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 15px;
            transition: transform 0.3s ease;
        }

        .appointment-card:hover {
            transform: translateY(-5px);
        }

        .time-slot {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .time-slot:hover {
            background-color: #f8f9fa;
            border-color: #0d6efd !important;
        }

        .time-slot.selected {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd !important;
        }

        .property-preview {
            border-radius: 10px;
            overflow: hidden;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }

        .header-section {
            background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
    </style>
</head>

<?php include "header.php"; 

if (isset($_SESSION['success_msg'])) {
    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
            {$_SESSION['success_msg']}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
          </div>";
    unset($_SESSION['success_msg']);
}

if (isset($_SESSION['error_msg'])) {
    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
            {$_SESSION['error_msg']}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
          </div>";
    unset($_SESSION['error_msg']);
}
?>

<body class="bg-light">
    <!-- Header Section -->
    <div class="header-section">
        <div class="container">
            <h1 class="text-center mb-3">Schedule a Visit</h1>
            <p class="text-center lead">Book an open house viewing or consultation with our real estate experts</p>
        </div>
    </div>

    <div class="container py-4">
        <!-- Appointment Type Selection -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-6 col-lg-5 mb-4">
                <div class="appointment-card card h-100">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <i class="fas fa-home fa-3x text-primary"></i>
                        </div>
                        <h3>Open House Viewing</h3>
                        <p class="text-muted">Tour available properties with our agents</p>
                        <button class="btn btn-outline-primary" id="openhouseBtn">Schedule Viewing</button>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-5 mb-4">
                <div class="appointment-card card h-100">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <i class="fas fa-handshake fa-3x text-primary"></i>
                        </div>
                        <h3>Consultation</h3>
                        <p class="text-muted">Meet with our real estate experts</p>
                        <button class="btn btn-outline-primary" id="consultationBtn">Schedule Consultation</button>
                    </div>
                </div>
            </div>
        </div>

        
       <!-- Scheduling Form -->
       <div id="scheduleForm" class="row justify-content-center" style="display: none;">
            <div class="col-lg-8">
                <div class="appointment-card card">
                    <div class="card-body p-4">
                        <h4 class="mb-4" id="formTitle">Schedule Your Appointment</h4>
                        
                        <form id="appointmentForm" method="POST">
                            <input type="hidden" name="appointment_type" id="appointmentType">
                            <input type="hidden" name="time_slot" id="selectedTimeSlot">
                            <input type="hidden" name="property_id" id="propertyId">
                            <input type="hidden" name="agent_id" id="agentId">
                            <!-- Property Selection (for Open House) -->
                            <div id="propertySection" style="display: none;">
                                <div class="mb-4">
                                    <label class="form-label">Select Property</label>
                                    <select class="form-select mb-3" name="property_id" id="propertyDropdown">
                                        <option value="">Choose a property...</option>
                                        <?php foreach ($properties as $property): ?>
                                            <option value="<?php echo htmlspecialchars($property['property_id']); ?>" 
                                                    data-agent-id="<?php echo htmlspecialchars($property['agent_id']); ?>" 
                                                    <?php echo isset($propertyDetails) && $property['property_id'] == $propertyDetails['property_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($property['title']) . " - $" . number_format($property['price'], 2); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div id="propertyDetails" class="property-preview bg-light p-4 rounded shadow-sm">
                                    <?php if ($propertyDetails): ?>
                                        <!-- Display Selected Property Details -->
                                        <h5 class="text-primary mb-3 mt-3"><?php echo htmlspecialchars($propertyDetails['title']); ?></h5>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <p><strong>Price:</strong> $<?php echo number_format($propertyDetails['price'], 2); ?></p>
                                                <p><strong>Property Type:</strong> <?php echo ucfirst(htmlspecialchars($propertyDetails['property_type'])); ?></p>
                                                <p><strong>Listing Type:</strong> <?php echo ucfirst(htmlspecialchars($propertyDetails['listing_type'])); ?></p>
                                                <p><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($propertyDetails['status'])); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Location:</strong></p>
                                                <p class="ms-3">
                                                    <?php echo htmlspecialchars($propertyDetails['address']); ?><br>
                                                    <?php echo htmlspecialchars($propertyDetails['city'] . ', ' . $propertyDetails['state']); ?>
                                                </p>
                                                <p><strong>Features:</strong></p>
                                                <p class="ms-3">
                                                    <?php echo nl2br(htmlspecialchars($propertyDetails['features'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <hr>
                                        <h6 class="text-secondary mb-3">Agent Information</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Name:</strong> <?php echo htmlspecialchars($propertyDetails['agent_name']); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($propertyDetails['agent_phone_number'] ?? 'N/A'); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Email:</strong> <?php echo htmlspecialchars($propertyDetails['agent_email']); ?></p>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Display message when no property is selected -->
                                        <h5 class="text-muted text-center mb-3 mt-3">Please Select a Property from the Menu</h5>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Date Selection -->
                            <div class="mb-4 mt-4">
                                <label class="form-label">Select Date</label>
                                <input type="date" class="form-control" name="appointment_date" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <!-- Time Slots -->
                            <div class="mb-4">
                                <label class="form-label">Available Time Slots</label>
                                <div class="row g-2">
                                    <div class="col-4 col-md-3">
                                        <div class="time-slot border rounded p-2 text-center">9:00 AM</div>
                                    </div>
                                    <div class="col-4 col-md-3">
                                        <div class="time-slot border rounded p-2 text-center">10:00 AM</div>
                                    </div>
                                    <div class="col-4 col-md-3">
                                        <div class="time-slot border rounded p-2 text-center">11:00 AM</div>
                                    </div>
                                    <div class="col-4 col-md-3">
                                        <div class="time-slot border rounded p-2 text-center">1:00 PM</div>
                                    </div>
                                    <div class="col-4 col-md-3">
                                        <div class="time-slot border rounded p-2 text-center">2:00 PM</div>
                                    </div>
                                    <div class="col-4 col-md-3">
                                        <div class="time-slot border rounded p-2 text-center">3:00 PM</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" placeholder="First Name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" placeholder="Last Name" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" placeholder="Email" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" placeholder="Phone Number">
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Additional Notes</label>
                                <textarea class="form-control" name="notes" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Schedule Appointment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>

        const propertiesData = <?php echo json_encode($properties); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners to buttons
            document.getElementById('openhouseBtn').addEventListener('click', () => showScheduleForm('openhouse'));
            document.getElementById('consultationBtn').addEventListener('click', () => showScheduleForm('consultation'));
            
            // Property dropdown change event
            document.getElementById('propertyDropdown').addEventListener('change', function() {
                const propertyId = this.value;
                console.log('Selected property ID:', propertyId); // Debugging line

                // Check if propertyId is valid (non-empty string and not undefined)
                if (propertyId) {
                    const selectedProperty = propertiesData.find(p => p.property_id == propertyId);
                    console.log('Selected property:', selectedProperty); // Debugging line

                    if (selectedProperty) {
                        // Continue with updating the property details
                        const detailsHTML = `
                            <h5 class="text-primary mb-3">${selectedProperty.title}</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p><strong>Price:</strong> $${Number(selectedProperty.price).toLocaleString()}</p>
                                    <p><strong>Property Type:</strong> ${selectedProperty.property_type}</p>
                                    <p><strong>Listing Type:</strong> ${selectedProperty.listing_type}</p>
                                    <p><strong>Status:</strong> ${selectedProperty.status}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Location:</strong></p>
                                    <p class="ms-3">
                                        ${selectedProperty.address}<br>
                                        ${selectedProperty.city}, ${selectedProperty.state}
                                    </p>
                                    <p><strong>Features:</strong></p>
                                    <p class="ms-3">${selectedProperty.features || 'No features listed'}</p>
                                </div>
                            </div>
                            <hr>
                            <h6 class="text-secondary mb-3">Agent Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> ${selectedProperty.agent_name}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Phone:</strong> ${selectedProperty.agent_phone_number || 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Email:</strong> ${selectedProperty.agent_email}</p>
                                </div>
                            </div>
                        `;
                        document.getElementById('propertyDetails').innerHTML = detailsHTML;
                        document.getElementById('agentId').value = selectedProperty.agent_id;
                    } else {
                        console.error('Property not found for ID:', propertyId); // Debugging line
                    }
                } else {
                    // Clear the property details section and display the message
                    document.getElementById('propertyDetails').innerHTML = `
                        <h5 class="text-muted text-center mb-3 mt-3">Please Select a Property from the Menu</h5>
                    `;
                    document.getElementById('agentId').value = '';
                }
            });

            // Time slot selection
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.addEventListener('click', function() {
                    document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
                    this.classList.add('selected');
                    document.getElementById('selectedTimeSlot').value = this.textContent.trim();
                });
            });

            // Form submission
            document.getElementById('appointmentForm').addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent the form from submitting

                // Create a FormData object from the form
                const formData = new FormData(this);

                // Print the form data
                // console.log('Form Data:');
                // formData.forEach((value, key) => {
                //     console.log(key + ': ' + value);
                // });

                // Optionally, you can alert the form data
                // let formDataString = '';
                // formData.forEach((value, key) => {
                //     formDataString += key + ': ' + value + '\n';
                // });
                // alert(formDataString);

                // After printing, you can submit the form if needed
                this.submit();
            });
        });

        function showScheduleForm(type) {
            // Verify DOM elements exist
            const propertyDropdown = document.getElementById('propertyDropdown');
            const propertyDetails = document.getElementById('propertyDetails');

            document.querySelector('input[name="appointment_type"]').value = type;
            document.getElementById('scheduleForm').style.display = 'flex';
            document.getElementById('propertySection').style.display = type === 'openhouse' ? 'block' : 'none';
            document.getElementById('formTitle').textContent = type === 'openhouse' ? 'Schedule Open House Viewing' : 'Schedule Consultation';

            // Set default values for consultation
            if (type === 'consultation') {
                document.getElementById('propertyId').value = '';
                document.getElementById('agentId').value = '';
                propertyDropdown.removeAttribute('required');
            } else {
                propertyDropdown.setAttribute('required', 'required');
            }

            // Smooth scroll to form
            document.getElementById('scheduleForm').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>