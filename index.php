<?php
// Configuration des en-têtes pour le JSON
header('Content-Type: application/json; charset=utf-8');

// Activer l'affichage des erreurs (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Log de la requête
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content type: " . ($_SERVER['CONTENT_TYPE'] ?? 'none'));

// Démarrer la session (optionnel, pour la protection contre le spam)
session_start();

// Configuration anti-spam (limite d'envois)
if (!isset($_SESSION['last_submit'])) {
    $_SESSION['last_submit'] = 0;
}

$current_time = time();
$time_since_last_submit = $current_time - $_SESSION['last_submit'];

// Limiter à un envoi toutes les 30 secondes
if ($time_since_last_submit < 30) {
    echo json_encode([
        "success" => false,
        "message" => "Veuillez patienter quelques secondes avant d'envoyer un nouveau message."
    ]);
    exit;
}

// Vérifier la méthode de requête
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
    exit;
}

// Vérification du token CSRF (sécurité) - Désactivé pour formulaire statique
/*
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Token de sécurité invalide."]);
    exit;
}
*/

// Debug: Log des données reçues
error_log("POST data: " . print_r($_POST, true));
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Debug: Log des données reçues
error_log("POST data: " . print_r($_POST, true));

$name = clean_input($_POST['name'] ?? '');
$email = clean_input($_POST['email'] ?? '');
$subject = clean_input($_POST['subject'] ?? '');
$message = clean_input($_POST['message'] ?? '');

// Validation
$errors = [];

if (empty($name) || strlen($name) < 2) {
    $errors[] = "Le nom doit contenir au moins 2 caractères.";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Veuillez entrer une adresse email valide.";
}

if (empty($subject) || strlen($subject) < 5) {
    $errors[] = "Le sujet doit contenir au moins 5 caractères.";
}

if (empty($message) || strlen($message) < 10) {
    $errors[] = "Le message doit contenir au moins 10 caractères.";
}

// Vérification anti-bot (honeypot)
if (!empty($_POST['website'])) {
    // Champ caché rempli par un bot
    echo json_encode(["success" => true, "message" => "Message envoyé avec succès !"]); // Leurre
    exit;
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "errors" => $errors
    ]);
    exit;
}

// Configuration SMTP
require_once 'config.php';

$smtp_config = [
    'host' => SMTP_HOST,
    'username' => SMTP_USER,
    'password' => SMTP_PASS,
    'port' => SMTP_PORT,
    'encryption' => SMTP_ENCRYPTION,
    'from_email' => FROM_EMAIL,
    'from_name' => FROM_NAME,
    'to_email' => DESTINATION_EMAIL,
    'to_name' => DESTINATION_NAME
];

try {
    // Chemin vers PHPMailer - Ajustez selon votre structure
    $phpmailer_path = __DIR__ . '/PHPMailer/src/';
    
    // Vérifier si PHPMailer est installé via Composer
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require __DIR__ . '/vendor/autoload.php';
    } 
    // Vérifier si PHPMailer est téléchargé manuellement
    elseif (file_exists($phpmailer_path . 'PHPMailer.php')) {
        require $phpmailer_path . 'PHPMailer.php';
        require $phpmailer_path . 'SMTP.php';
        require $phpmailer_path . 'Exception.php';
    }
    // Si PHPMailer n'est pas trouvé
    else {
        throw new Exception('PHPMailer n\'est pas installé. Veuillez installer via Composer ou télécharger manuellement.');
    }
    
    // Utiliser PHPMailer avec des classes globales
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    // Configuration du serveur SMTP
    $mail->isSMTP();
    $mail->Host = $smtp_config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_config['username'];
    $mail->Password = $smtp_config['password'];
    $mail->SMTPSecure = $smtp_config['encryption'] === 'ssl' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $smtp_config['port'];
    
    // Options supplémentaires (optionnel)
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Encodage
    $mail->CharSet = 'UTF-8';
    
    // Expéditeur et destinataire
    $mail->setFrom($smtp_config['from_email'], $smtp_config['from_name']);
    $mail->addAddress($smtp_config['to_email'], $smtp_config['to_name']);
    $mail->addReplyTo($email, $name);
    
    // Contenu de l'email
    $mail->isHTML(true);
    $mail->Subject = 'Portfolio - ' . $subject;
    
    // Template HTML pour l'email
    $email_body = "
    <!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Nouveau message du portfolio</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #2563eb, #7c3aed); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
            .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
            .info-item { margin-bottom: 15px; }
            .label { font-weight: bold; color: #2563eb; }
            .message-box { background: white; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0; border-radius: 4px; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Nouveau message depuis votre portfolio</h2>
            </div>
            <div class='content'>
                <div class='info-item'>
                    <span class='label'>Nom :</span> {$name}
                </div>
                <div class='info-item'>
                    <span class='label'>Email :</span> {$email}
                </div>
                <div class='info-item'>
                    <span class='label'>Sujet :</span> {$subject}
                </div>
                <div class='info-item'>
                    <span class='label'>Date :</span> " . date('d/m/Y à H:i') . "
                </div>
                <div class='message-box'>
                    <strong>Message :</strong><br>
                    " . nl2br($message) . "
                </div>
                <div class='footer'>
                    <p>Cet email a été envoyé automatiquement depuis le formulaire de contact de votre portfolio.</p>
                    <p>Pour répondre, cliquez simplement sur \"Répondre\" dans votre client de messagerie.</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $mail->Body = $email_body;
    
    // Version texte brut (pour les clients qui ne supportent pas HTML)
    $mail->AltBody = "Nouveau message depuis le portfolio\n\n" .
                     "Nom: {$name}\n" .
                     "Email: {$email}\n" .
                     "Sujet: {$subject}\n" .
                     "Date: " . date('d/m/Y à H:i') . "\n\n" .
                     "Message:\n" . $message . "\n\n" .
                     "---\n" .
                     "Cet email a été envoyé automatiquement depuis le formulaire de contact de votre portfolio.";
    
    // Envoyer l'email
    if ($mail->send()) {
        // Mettre à jour le timestamp du dernier envoi
        $_SESSION['last_submit'] = $current_time;
        // Régénérer le token CSRF - Désactivé
        // $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        echo json_encode([
            "success" => true,
            "message" => "Merci {$name} ! Votre message a été envoyé avec succès. Je vous répondrai dans les plus brefs délais."
        ]);
    } else {
        throw new Exception("Échec de l'envoi de l'email.");
    }
    
} catch (Exception $e) {
    // Journalisation de l'erreur
    error_log("Erreur d'envoi d'email: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Une erreur technique est survenue lors de l'envoi de votre message. Veuillez réessayer plus tard ou me contacter directement à omranehaha78@gmail.com"
    ]);
}
?>