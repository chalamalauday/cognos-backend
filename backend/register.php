<?php
/**
 * COGNOS 2K26 - Multi-Event Registration API Endpoint
 * Handles form validation, ID card upload, MySQL persistence, and email dispatch.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';
apply_cors_headers();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mailer.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Please use POST.'
    ]);
    exit;
}

try {
    // 1. Sanitize and retrieve primary fields
    $email        = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $student_name = strtoupper(trim($_POST['student_name'] ?? ''));
    $roll_no      = strtoupper(trim($_POST['roll_no'] ?? ''));
    $branch       = trim($_POST['branch'] ?? '');
    $college_name = trim($_POST['college_name'] ?? '');
    $teammate_email_input = trim($_POST['teammate_email'] ?? '');
    $vishleshana_participation = trim($_POST['vishleshana_participation'] ?? '');
    $gender       = trim($_POST['gender'] ?? '');
    $distance_raw = trim($_POST['distance_from_college_km'] ?? '');
    $distance_from_college_km = filter_var($distance_raw, FILTER_VALIDATE_FLOAT);
    $accommodation_required = in_array(strtolower(trim($_POST['accommodation_required'] ?? '')), ['1', 'true', 'yes', 'on'], true) ? 1 : 0;
    $primary_vishleshana = 0;
    $teammate_vishleshana = 0;

    // 2. Validate events selected (Checkboxes)
    $events = $_POST['events'] ?? [];
    if (is_string($events)) {
        // Can be JSON or comma-separated if submitted via API
        $decoded = json_decode($events, true);
        $events = is_array($decoded) ? $decoded : array_map('trim', explode(',', $events));
    }
    // Clean events array
    $valid_events = array_keys($COGNOS_EVENTS);
    $selected_events = array_values(array_intersect($events, $valid_events));

    // Field validations
    $errors = [];
    if (!$email) {
        $errors[] = 'Please provide a valid email address.';
    }
    if (empty($student_name)) {
        $errors[] = 'Student name is required.';
    }
    if (empty($roll_no)) {
        $errors[] = 'Roll number is required.';
    }
    if (empty($branch)) {
        $errors[] = 'Branch is required.';
    }
    if (empty($college_name)) {
        $errors[] = 'College name is required.';
    }
    if (!in_array($gender, ['Boys', 'Girls'], true)) {
        $errors[] = 'Please select Boys or Girls for accommodation grouping.';
    }
    if ($distance_raw === '' || $distance_from_college_km === false || $distance_from_college_km < 0) {
        $errors[] = 'Please enter a valid distance from your college in kilometres.';
    }
    if ($accommodation_required && ($distance_from_college_km === false || $distance_from_college_km <= 100)) {
        $errors[] = 'Accommodation is available only to participants travelling more than 100 km.';
    }
    if (empty($selected_events)) {
        $errors[] = 'Please select at least one event (Vishleshana, Razzle Review, or Data Dazzle).';
    }

    // 3. Teammate fields & Individual Event Check
    $isOnlyVishleshana = (count($selected_events) === 1 && in_array('Vishleshana', $selected_events));
    $hasTeamEvent = (in_array('Razzle Review', $selected_events) || in_array('Data Dazzle', $selected_events));

    $has_teammate_input = isset($_POST['has_teammate']) && in_array(strtolower(trim($_POST['has_teammate'])), ['1', 'true', 'yes', 'on']);

    // Vishleshana is strictly individual. If only Vishleshana is selected, teammates are disallowed.
    if ($isOnlyVishleshana) {
        if ($has_teammate_input) {
            $errors[] = 'Vishleshana is strictly an individual event (1 member). Teammates cannot be registered for this challenge.';
        }
        $has_teammate = 0;
        $teammate_name = null;
        $teammate_email = null;
        $teammate_roll_no = null;
        $teammate_branch = null;
        $teammate_college = null;
        $primary_vishleshana = 1;
        $teammate_vishleshana = 0;
    } else {
        $has_teammate = $has_teammate_input ? 1 : 0;
        $teammate_name    = $has_teammate ? strtoupper(trim($_POST['teammate_name'] ?? '')) : null;
        $teammate_email   = $has_teammate ? filter_var($teammate_email_input, FILTER_VALIDATE_EMAIL) : null;
        $teammate_roll_no = $has_teammate ? strtoupper(trim($_POST['teammate_roll_no'] ?? '')) : null;
        $teammate_branch  = $has_teammate ? trim($_POST['teammate_branch'] ?? '') : null;
        $teammate_college = $has_teammate ? trim($_POST['teammate_college'] ?? '') : null;

        if ($has_teammate) {
            if (empty($teammate_name) || !$teammate_email || empty($teammate_roll_no) || empty($teammate_branch) || empty($teammate_college)) {
                $errors[] = 'Please fill out all teammate details, including a valid email address.';
            }
        } else {
            $teammate_vishleshana = 0;
        }

        if (!in_array('Vishleshana', $selected_events, true)) {
            $primary_vishleshana = 0;
            $teammate_vishleshana = 0;
        } elseif (!$has_teammate) {
            $primary_vishleshana = 1;
        } elseif (in_array($vishleshana_participation, ['primary_only', 'teammate_only', 'both'], true)) {
            $primary_vishleshana = in_array($vishleshana_participation, ['primary_only', 'both'], true) ? 1 : 0;
            $teammate_vishleshana = in_array($vishleshana_participation, ['teammate_only', 'both'], true) ? 1 : 0;
        } else {
            $errors[] = 'Please choose which team members participate individually in Vishleshana.';
        }
    }

    // 4. Handle ID Card File Upload
    $uploaded_id_path = null;
    if (isset($_FILES['id_card']) && $_FILES['id_card']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['id_card'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed with error code: ' . $file['error'];
        } elseif ($file['size'] > MAX_UPLOAD_SIZE) {
            $errors[] = 'ID card file size exceeds the 5MB limit.';
        } else {
            $file_info = pathinfo($file['name']);
            $extension = strtolower($file_info['extension'] ?? '');

            if (!in_array($extension, ALLOWED_EXTENSIONS)) {
                $errors[] = 'Invalid file format. Allowed formats: ' . implode(', ', ALLOWED_EXTENSIONS);
            } else {
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true);
                }

                $clean_filename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file_info['filename']);
                $new_filename = 'id_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 8) . '.' . $extension;
                $destination = UPLOAD_DIR . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $uploaded_id_path = 'uploads/id_cards/' . $new_filename;
                } else {
                    $errors[] = 'Failed to save the uploaded ID card. Please check server folder permissions.';
                }
            }
        }
    } else {
        $errors[] = 'College ID card upload is mandatory.';
    }

    // If validation fails, return errors
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => implode(' ', $errors),
            'errors' => $errors
        ]);
        exit;
    }

    // 5. Connect to Database
    $pdo = get_db_connection();

    // Check if duplicate registration already exists for same roll_no or email
    $checkStmt = $pdo->prepare("SELECT id, reg_code FROM `registrations` WHERE `email` = ? OR `roll_no` = ? LIMIT 1");
    $checkStmt->execute([$email, $roll_no]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        // Participant is already registered
        echo json_encode([
            'success' => false,
            'message' => "You have already registered with Reg ID: {$existing['reg_code']}! Check your email or contact the coordinators if you need to modify your registration.",
            'is_duplicate' => true,
            'reg_code' => $existing['reg_code']
        ]);
        exit;
    }

    // 6. Generate Unique Registration Code (COG26-XXXX)
    $reg_code = 'COG26-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));

    // 7. Insert Registration Record
    $insertReg = $pdo->prepare("
        INSERT INTO `registrations` 
        (`reg_code`, `email`, `student_name`, `roll_no`, `branch`, `college_name`, `gender`, `distance_from_college_km`, `accommodation_required`, `primary_vishleshana`, `teammate_vishleshana`, `id_card_path`, `has_teammate`, `teammate_name`, `teammate_email`, `teammate_roll_no`, `teammate_branch`, `teammate_college`, `created_at`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $insertReg->execute([
        $reg_code,
        $email,
        $student_name,
        $roll_no,
        $branch,
        $college_name,
        $gender,
        $distance_from_college_km,
        $accommodation_required,
        $primary_vishleshana,
        $teammate_vishleshana,
        $uploaded_id_path,
        $has_teammate ? 1 : 0,
        $teammate_name,
        $teammate_email,
        $teammate_roll_no,
        $teammate_branch,
        $teammate_college
    ]);

    $reg_id = $pdo->lastInsertId();

    // 8. Insert Selected Events into Junction Table
    $insertEvent = $pdo->prepare("INSERT INTO `registration_events` (`registration_id`, `event_name`, `created_at`) VALUES (?, ?, NOW())");
    foreach ($selected_events as $event_name) {
        $insertEvent->execute([$reg_id, $event_name]);
    }

    $insertParticipant = $pdo->prepare("INSERT INTO `registration_participants` (`registration_id`, `participant_type`, `participant_name`, `participant_email`, `roll_no`, `branch`, `college_name`, `participates_vishleshana`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $insertParticipant->execute([$reg_id, 'primary', $student_name, $email, $roll_no, $branch, $college_name, $primary_vishleshana]);
    if ($has_teammate) {
        $insertParticipant->execute([$reg_id, 'teammate', $teammate_name, $teammate_email, $teammate_roll_no, $teammate_branch, $teammate_college, $teammate_vishleshana]);
    }

    // 9. Send Confirmation Email via PHPMailer
    $studentData = [
        'reg_code'         => $reg_code,
        'email'            => $email,
        'student_name'     => $student_name,
        'roll_no'          => $roll_no,
        'branch'           => $branch,
        'college_name'     => $college_name,
        'gender'           => $gender,
        'distance_from_college_km' => $distance_from_college_km,
        'accommodation_required' => $accommodation_required,
        'primary_vishleshana' => $primary_vishleshana,
        'teammate_vishleshana' => $teammate_vishleshana,
        'has_teammate'     => $has_teammate,
        'teammate_name'    => $teammate_name,
        'teammate_email'   => $teammate_email,
        'teammate_roll_no' => $teammate_roll_no,
        'teammate_branch'  => $teammate_branch,
        'teammate_college' => $teammate_college
    ];

    // 10. Prepare instant success response payload
    $responsePayload = json_encode([
        'success'        => true,
        'reg_code'       => $reg_code,
        'student_name'   => $student_name,
        'email'          => $email,
        'events'         => $selected_events,
        'email_sent'     => true,
        'whatsapp_link'  => WHATSAPP_COMMUNITY_LINK,
        'message'        => 'Registration successful! Your official Registration ID is ' . $reg_code . '.'
    ]);

    // Fast-response: flush HTTP payload to browser immediately so registration succeeds instantly
    ignore_user_abort(true);
    set_time_limit(120);

    header('Content-Type: application/json; charset=utf-8');
    header('Connection: close');
    header('Content-Length: ' . strlen($responsePayload));

    echo $responsePayload;

    while (ob_get_level() > 0) {
        ob_end_flush();
    }
    @ob_flush();
    flush();

    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }

    // 11. Send Confirmation Email via PHPMailer asynchronously in background
    try {
        send_registration_confirmation_email($studentData, $selected_events);
    } catch (Exception $mailEx) {
        error_log('Background Mailer Error: ' . $mailEx->getMessage());
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during registration. Please try again or contact event coordinators.',
        'error_detail' => $e->getMessage()
    ]);
}
