<?php
/**
 * Auto-pay Registration Form Handler
 * Jetcode Innovationb Co. Ltd
 */

// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    die('Method Not Allowed');
}

// Configuration
$receiving_email_address = 'info@jetcode.co.tz';
$admin_email = 'admin@jetcode.co.tz';

// Sanitize and validate form data
$fullname = validate_input($_POST['fullname'] ?? '');
$phone = validate_input($_POST['phone'] ?? '');
$email = validate_input($_POST['email'] ?? '');
$meter_number = validate_input($_POST['meter_number'] ?? '');
$recharge_amount = validate_input($_POST['recharge_amount'] ?? '');
$trigger_balance = validate_input($_POST['trigger_balance'] ?? '');
$payment_method = validate_input($_POST['payment_method'] ?? '');
$address = validate_input($_POST['address'] ?? '');
$terms_agreed = isset($_POST['terms_agreed']) ? 'Yes' : 'No';
$sms_notifications = isset($_POST['sms_notifications']) ? 'Yes' : 'No';

// Validation
$errors = [];

if (empty($fullname)) {
    $errors[] = 'Full name is required';
}

if (empty($phone)) {
    $errors[] = 'Phone number is required';
} elseif (!preg_match('/^\+?[0-9\s\-\(\)]{10,15}$/', $phone)) {
    $errors[] = 'Please enter a valid phone number';
}

if (empty($email)) {
    $errors[] = 'Email address is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address';
}

if (empty($meter_number)) {
    $errors[] = 'Meter number is required';
}

if (empty($recharge_amount)) {
    $errors[] = 'Preferred recharge amount is required';
}

if (empty($trigger_balance)) {
    $errors[] = 'Trigger balance amount is required';
}

if (empty($payment_method)) {
    $errors[] = 'Payment method is required';
}

if ($terms_agreed !== 'Yes') {
    $errors[] = 'You must agree to the Terms and Conditions';
}

// If there are validation errors, return them
if (!empty($errors)) {
    http_response_code(400);
    echo 'Validation errors: ' . implode(', ', $errors);
    exit;
}

// Prepare email content
$subject = 'New Auto-pay Registration - ' . $fullname;

$message = "
<html>
<head>
    <title>New Auto-pay Registration</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: #e21d26; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #e21d26; }
        .value { margin-left: 10px; }
        .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class='header'>
        <h2>🔥 New Auto-pay Registration</h2>
    </div>
    
    <div class='content'>
        <h3>Customer Information:</h3>
        
        <div class='field'>
            <span class='label'>Full Name:</span>
            <span class='value'>" . htmlspecialchars($fullname) . "</span>
        </div>
        
        <div class='field'>
            <span class='label'>Phone Number:</span>
            <span class='value'>" . htmlspecialchars($phone) . "</span>
        </div>
        
        <div class='field'>
            <span class='label'>Email Address:</span>
            <span class='value'>" . htmlspecialchars($email) . "</span>
        </div>
        
        <div class='field'>
            <span class='label'>Meter Number:</span>
            <span class='value'>" . htmlspecialchars($meter_number) . "</span>
        </div>
        
        <h3>Auto-pay Configuration:</h3>
        
        <div class='field'>
            <span class='label'>Preferred Recharge Amount:</span>
            <span class='value'>" . htmlspecialchars($recharge_amount) . " TSh</span>
        </div>
        
        <div class='field'>
            <span class='label'>Trigger When Balance Below:</span>
            <span class='value'>" . htmlspecialchars($trigger_balance) . " TSh</span>
        </div>
        
        <div class='field'>
            <span class='label'>Preferred Payment Method:</span>
            <span class='value'>" . htmlspecialchars($payment_method) . "</span>
        </div>
        
        <div class='field'>
            <span class='label'>Installation Address:</span>
            <span class='value'>" . htmlspecialchars($address ?: 'Not provided') . "</span>
        </div>
        
        <h3>Preferences:</h3>
        
        <div class='field'>
            <span class='label'>SMS Notifications:</span>
            <span class='value'>" . $sms_notifications . "</span>
        </div>
        
        <div class='field'>
            <span class='label'>Terms & Conditions Agreed:</span>
            <span class='value'>" . $terms_agreed . "</span>
        </div>
        
        <div class='field'>
            <span class='label'>Registration Time:</span>
            <span class='value'>" . date('Y-m-d H:i:s') . "</span>
        </div>
    </div>
    
    <div class='footer'>
        <p>This registration was submitted through the Jetcode website auto-pay form.</p>
        <p>Please contact the customer within 24 hours to complete the setup process.</p>
    </div>
</body>
</html>
";

// Headers for HTML email
$headers = array(
    'From' => 'noreply@jetcode.co.tz',
    'Reply-To' => $email,
    'X-Mailer' => 'PHP/' . phpversion(),
    'MIME-Version' => '1.0',
    'Content-Type' => 'text/html; charset=UTF-8'
);

// Convert headers array to string
$header_string = '';
foreach ($headers as $key => $value) {
    $header_string .= $key . ': ' . $value . "\r\n";
}

// Send email to admin
$admin_sent = mail($receiving_email_address, $subject, $message, $header_string);

// Send confirmation email to customer
$customer_subject = 'Auto-pay Registration Confirmation - Jetcode Innovationb';
$customer_message = "
<html>
<head>
    <title>Auto-pay Registration Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: #e21d26; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .highlight { background: #f8f9fa; padding: 15px; border-left: 4px solid #e21d26; margin: 20px 0; }
        .footer { background: #f8f9fa; padding: 15px; text-align: center; }
    </style>
</head>
<body>
    <div class='header'>
        <h2>🎉 Registration Successful!</h2>
    </div>
    
    <div class='content'>
        <p>Dear " . htmlspecialchars($fullname) . ",</p>
        
        <p>Thank you for registering for Jetcode Auto-pay service! We have received your registration and our team will contact you within 24 hours to complete the setup process.</p>
        
        <div class='highlight'>
            <h3>Your Registration Details:</h3>
            <p><strong>Meter Number:</strong> " . htmlspecialchars($meter_number) . "</p>
            <p><strong>Recharge Amount:</strong> " . htmlspecialchars($recharge_amount) . " TSh</p>
            <p><strong>Trigger Amount:</strong> " . htmlspecialchars($trigger_balance) . " TSh</p>
            <p><strong>Payment Method:</strong> " . htmlspecialchars($payment_method) . "</p>
        </div>
        
        <h3>What happens next?</h3>
        <ol>
            <li>Our team will call you at " . htmlspecialchars($phone) . " within 24 hours</li>
            <li>We'll verify your meter details and payment information</li>
            <li>Complete the auto-pay setup process</li>
            <li>Start enjoying uninterrupted power supply!</li>
        </ol>
        
        <p>If you have any immediate questions, please contact us at:</p>
        <p><strong>Phone:</strong> +255 657 743 547<br>
        <strong>Email:</strong> info@jetcode.co.tz</p>
    </div>
    
    <div class='footer'>
        <p>Thank you for choosing Jetcode Innovationb Co. Ltd</p>
        <p>Empowering Tanzania with Smart Energy Solutions</p>
    </div>
</body>
</html>
";

$customer_headers = array(
    'From' => 'noreply@jetcode.co.tz',
    'Reply-To' => 'info@jetcode.co.tz',
    'X-Mailer' => 'PHP/' . phpversion(),
    'MIME-Version' => '1.0',
    'Content-Type' => 'text/html; charset=UTF-8'
);

$customer_header_string = '';
foreach ($customer_headers as $key => $value) {
    $customer_header_string .= $key . ': ' . $value . "\r\n";
}

$customer_sent = mail($email, $customer_subject, $customer_message, $customer_header_string);

// Log the registration (optional - create a simple log file)
$log_entry = date('Y-m-d H:i:s') . " - Auto-pay Registration: " . $fullname . " (" . $email . ") - Meter: " . $meter_number . "\n";
file_put_contents('autopay_registrations.log', $log_entry, FILE_APPEND | LOCK_EX);

// Return success response
if ($admin_sent) {
    echo 'Registration successful! We will contact you within 24 hours to complete the setup.';
} else {
    http_response_code(500);
    echo 'Registration failed. Please try again or contact us directly at +255 657 743 547.';
}

/**
 * Sanitize and validate input data
 */
function validate_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?> 