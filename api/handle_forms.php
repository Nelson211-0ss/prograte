<?php
// Configure CORS based on environment
$allowed_origins = [
    'http://localhost:8000',
    'http://localhost:5500',
    'http://localhost:5501',
    'http://localhost:5502',
    'http://127.0.0.1:5500',
    'http://127.0.0.1:5501',
    'http://127.0.0.1:5502',
    'https://progratecapital.com',
    'https://www.progratecapital.com'
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}

header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Load Composer's autoloader with correct path
require dirname(__DIR__) . '/vendor/autoload.php';

// Load environment variables with correct path
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Function to clean input data
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to send email using PHPMailer
function send_email($to, $subject, $message) {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USERNAME'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['SMTP_PORT'];

        // Recipients
        $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Handle POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = array('success' => false, 'message' => 'Unknown error occurred');
    
    // Handle Application Form
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'application') {
        // Collect and clean application form data
        $fullName = clean_input($_POST['fullName']);
        $phone = clean_input($_POST['phone']);
        $email = clean_input($_POST['email']);
        $location = clean_input($_POST['location']);
        $nin = clean_input($_POST['nin']);
        $businessName = clean_input($_POST['businessName']);
        $businessType = clean_input($_POST['businessType']);
        $yearsInBusiness = clean_input($_POST['yearsInBusiness']);
        $monthlyRevenue = clean_input($_POST['monthlyRevenue']);
        $businessDescription = clean_input($_POST['businessDescription']);

        // Prepare application email content
        $subject = "New Investment Application from $fullName";
        $message = "<h2>New Investment Application</h2>
                   <h3>Personal Information:</h3>
                   <p><strong>Name:</strong> $fullName</p>
                   <p><strong>Phone:</strong> $phone</p>
                   <p><strong>Email:</strong> $email</p>
                   <p><strong>Location:</strong> $location</p>
                   <p><strong>NIN:</strong> $nin</p>
                   <h3>Business Information:</h3>
                   <p><strong>Business Name:</strong> $businessName</p>
                   <p><strong>Type:</strong> $businessType</p>
                   <p><strong>Years in Business:</strong> $yearsInBusiness</p>
                   <p><strong>Monthly Revenue:</strong> $monthlyRevenue</p>
                   <p><strong>Business Description:</strong> $businessDescription</p>";

        // Send application email
        if (send_email($_ENV['ADMIN_EMAIL'], $subject, $message)) {
            $response = array('success' => true, 'message' => 'Your application has been submitted successfully. We will contact you soon.');
        } else {
            $response = array('success' => false, 'message' => 'Failed to submit application. Please try again later.');
        }
    }
    
    // Handle Contact Form
    elseif (isset($_POST['form_type']) && $_POST['form_type'] === 'contact') {
        // Collect and clean contact form data
        $fullName = clean_input($_POST['fullName']);
        $email = clean_input($_POST['email']);
        $subject = clean_input($_POST['subject']);
        $message_text = clean_input($_POST['message']);

        // Prepare contact email content
        $email_subject = "Contact Form Message: $subject";
        $message = "<h2>New Contact Form Message</h2>
                   <p><strong>From:</strong> $fullName</p>
                   <p><strong>Email:</strong> $email</p>
                   <p><strong>Subject:</strong> $subject</p>
                   <p><strong>Message:</strong></p>
                   <p>$message_text</p>";

        // Send contact email
        if (send_email($_ENV['ADMIN_EMAIL'], $email_subject, $message)) {
            $response = array('success' => true, 'message' => 'Your message has been sent successfully. We will get back to you soon.');
        } else {
            $response = array('success' => false, 'message' => 'Failed to send message. Please try again later.');
        }
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    // Handle non-POST requests
    header('HTTP/1.1 405 Method Not Allowed');
    header('Allow: POST');
    exit('This endpoint only accepts POST requests');
}
?>