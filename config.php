<?php
// Configuration SMTP
// Choisissez la configuration selon votre fournisseur

// Configuration pour Gmail
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'haha.omrane@gmail.com'); // Votre email Gmail
define('SMTP_PASS', 'fcxr mzgf eese uihc'); // Mot de passe d'application
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls'); // 'tls' ou 'ssl'

/*
// Configuration pour Outlook/Office 365
define('SMTP_HOST', 'smtp.office365.com');
define('SMTP_USER', 'votre-email@outlook.com');
define('SMTP_PASS', 'votre-mot-de-passe');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');
*/

/*
// Configuration pour OVH
define('SMTP_HOST', 'ssl0.ovh.net');
define('SMTP_USER', 'contact@votre-domaine.com');
define('SMTP_PASS', 'votre-mot-de-passe');
define('SMTP_PORT', 465);
define('SMTP_ENCRYPTION', 'ssl');
*/

// Email de destination
define('DESTINATION_EMAIL', 'omranehaha78@gmail.com');
define('DESTINATION_NAME', 'Omrane Haha');

// Expéditeur
define('FROM_EMAIL', SMTP_USER); // Doit correspondre au SMTP_USER pour Gmail
define('FROM_NAME', 'Portfolio Omrane Haha');

// Configuration anti-spam
define('MIN_TIME_BETWEEN_SUBMITS', 30); // Secondes entre deux envois

// Mode debug
define('DEBUG_MODE', true); // Mettre à false en production
?>