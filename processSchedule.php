<?php
$propertyDetails = null;
$properties = [];

try {
    // Fetch all available properties
    $query = "SELECT 
        properties.property_id,
        properties.title,
        properties.description,
        properties.price,
        properties.address,
        properties.city,
        properties.state,
        properties.zip_code,
        properties.property_type,
        properties.listing_type,
        properties.status,
        properties.features,
        users.name AS agent_name,
        agents.agent_id,
        agents.profile_picture,
        agents.agency_name,
        users.phone_number AS agent_phone_number,
        users.email AS agent_email
    FROM properties
    JOIN agents ON properties.agent_id = agents.agent_id
    JOIN users ON agents.user_id = users.user_id
    WHERE properties.status = 'available'";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set first property as default if none selected
    // if (!isset($propertyDetails) && !empty($properties)) {
    //     $propertyDetails = $properties[0];
    // } 
} catch(PDOException $e) {
    $properties = [];
    $_SESSION['error_msg'] = "Error fetching properties: " . $e->getMessage();
}

// Fetch all available agents
$agents = [];
try {
    $agent_query = "SELECT 
        agents.agent_id,
        users.name AS agent_name,
        users.phone_number AS agent_phone_number,
        users.email AS agent_email
    FROM agents
    JOIN users ON agents.user_id = users.user_id";
    
    $agent_stmt = $pdo->prepare($agent_query);
    $agent_stmt->execute();
    $agents = $agent_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $agents = [];
    $_SESSION['error_msg'] = "Error fetching agents: " . $e->getMessage();
}

// Check if both property_id and agent_id are provided
if (isset($_GET['property_id']) && isset($_GET['agent_id'])) {
    // Improve Input Validation
    $property_id = filter_input(INPUT_GET, 'property_id', FILTER_VALIDATE_INT);
    $agent_id = filter_input(INPUT_GET, 'agent_id', FILTER_VALIDATE_INT);

    try {
        // Fetch details for the specific property
        $query = "SELECT 
            properties.property_id,
            properties.title,
            properties.description,
            properties.price,
            properties.address,
            properties.city,
            properties.state,
            properties.zip_code,
            properties.property_type,
            properties.listing_type,
            properties.status,
            properties.features,
            users.name AS agent_name,
            agents.agent_id,
            agents.profile_picture,
            agents.agency_name,
            users.phone_number AS agent_phone_number,
            users.email AS agent_email
        FROM properties
        JOIN agents ON properties.agent_id = agents.agent_id
        JOIN users ON agents.user_id = users.user_id
        WHERE properties.property_id = :property_id 
          AND agents.agent_id = :agent_id";

        $stmt = $pdo->prepare($query);
        $stmt->execute(['property_id' => $property_id, 'agent_id' => $agent_id]);
        $propertyDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$propertyDetails) {
            $_SESSION['error_msg'] = "Property not found or unavailable.";
           
        }
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error fetching property details: " . $e->getMessage();
        error_log("Database error: " . $e->getMessage());
    }
} 

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['appointment_date'], 
        $_POST['time_slot'], $_POST['appointment_type'])) {
        
        // Get form inputs
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
        $appointment_date = trim($_POST['appointment_date']);
        $time_slot = trim($_POST['time_slot']);
        $appointment_type = trim($_POST['appointment_type']);
        $property_id = trim($_POST['property_id']);
        $agent_id = trim($_POST['agent_id']);
        $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

        // Set property_id and agent_id to NULL if they are empty strings
        $property_id = $property_id === '' ? null : $property_id;
        $agent_id = $agent_id === '' ? null : $agent_id;

        // Randomly assign an agent for consultations
        if ($appointment_type === 'consultation' && empty($agent_id)) {
            if (!empty($agents)) {
                $random_agent = $agents[array_rand($agents)];
                $agent_id = $random_agent['agent_id'];
            } else {
                $_SESSION['error_msg'] = "No available agents for consultation.";
            }
        }

        // Validate form inputs
        if (empty($first_name) || empty($last_name) || empty($email)) {
            $_SESSION['error_msg'] = "First name, last name, and email are required.";
        } else {
            // Validate phone number if provided
            if (!empty($phone) && !preg_match("/^[0-9]{1,20}$/", $phone)) {
                $_SESSION['error_msg'] = "Please enter a valid phone number.";
            }
            // Validate email format
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error_msg'] = "Please enter a valid email address.";
            }
            // Validate appointment type
            elseif (!in_array($appointment_type, ['openhouse', 'consultation'])) {
                $_SESSION['error_msg'] = "Invalid appointment type.";
            }
            // If no errors, proceed with database checks
            else {
                try {
                    // Start transaction
                    $pdo->beginTransaction();

                    // Check if agent exists for openhouse
                    if ($appointment_type === 'openhouse') {
                        $agent_query = "SELECT agent_id FROM agents WHERE agent_id = :agent_id";
                        $agent_stmt = $pdo->prepare($agent_query);
                        $agent_stmt->bindParam(':agent_id', $agent_id);
                        $agent_stmt->execute();
                        
                        if (!$agent_stmt->fetch()) {
                            throw new Exception("Invalid agent selected.");
                        }

                        // Check if property exists
                        $property_query = "SELECT property_id FROM properties WHERE property_id = :property_id";
                        $property_stmt = $pdo->prepare($property_query);
                        $property_stmt->bindParam(':property_id', $property_id);
                        $property_stmt->execute();
                        
                        if (!$property_stmt->fetch()) {
                            throw new Exception("Invalid property selected.");
                        }
                    }

                    // Check if the time slot is available for the agent
                    $availability_query = "SELECT id FROM appointments 
                                        WHERE agent_id = :agent_id 
                                        AND appointment_date = :appointment_date 
                                        AND time_slot = :time_slot";
                    $availability_stmt = $pdo->prepare($availability_query);
                    $availability_stmt->bindParam(':agent_id', $agent_id);
                    $availability_stmt->bindParam(':appointment_date', $appointment_date);
                    $availability_stmt->bindParam(':time_slot', $time_slot);
                    $availability_stmt->execute();

                    if ($availability_stmt->fetch()) {
                        throw new Exception("This time slot is already booked for the selected agent.");
                    }

                    // Insert the new appointment
                    $insert_query = "INSERT INTO appointments (
                        first_name, last_name, email, phone, appointment_date, 
                        time_slot, appointment_type, property_id, agent_id, notes
                    ) VALUES (
                        :first_name, :last_name, :email, :phone, :appointment_date,
                        :time_slot, :appointment_type, :property_id, :agent_id, :notes
                    )";
                    
                    $insert_stmt = $pdo->prepare($insert_query);
                    $insert_stmt->bindParam(':first_name', $first_name);
                    $insert_stmt->bindParam(':last_name', $last_name);
                    $insert_stmt->bindParam(':email', $email);
                    $insert_stmt->bindParam(':phone', $phone);
                    $insert_stmt->bindParam(':appointment_date', $appointment_date);
                    $insert_stmt->bindParam(':time_slot', $time_slot);
                    $insert_stmt->bindParam(':appointment_type', $appointment_type);
                    $insert_stmt->bindParam(':property_id', $property_id, PDO::PARAM_INT);
                    $insert_stmt->bindParam(':agent_id', $agent_id, PDO::PARAM_INT);
                    $insert_stmt->bindParam(':notes', $notes);

                    if ($insert_stmt->execute()) {
                        $pdo->commit();
                        $_SESSION['success_msg'] = "Appointment booked successfully!";
                        header("Location: schedule.php");
                        exit();
                    } else {
                        throw new Exception("Failed to insert appointment.");
                    }
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $_SESSION['error_msg'] = $e->getMessage();
                }
            }
        }
    } else {
        $_SESSION['error_msg'] = "All required fields must be provided.";
    }
}
?>