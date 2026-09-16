<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'Méthode non autorisée.']); exit; }
session_start();
if (isset($_SESSION['last_contact']) && time() - (int)$_SESSION['last_contact'] < 30) { http_response_code(429); echo json_encode(['error'=>'Veuillez patienter avant un nouvel envoi.']); exit; }
function clean(string $key, int $max): string { $value=trim((string)($_POST[$key]??'')); return mb_substr(strip_tags($value),0,$max); }
if (!empty($_POST['website'])) { echo json_encode(['ok'=>true]); exit; }
$name=clean('name',80); $email=filter_var(trim((string)($_POST['email']??'')),FILTER_VALIDATE_EMAIL); $phone=clean('phone',30); $message=clean('message',1500); $privacy=(string)($_POST['privacy']??'');
if (mb_strlen($name)<2 || !$email || mb_strlen($phone)<6 || mb_strlen($message)<10 || $privacy!=='accepted') { http_response_code(422); echo json_encode(['error'=>'Veuillez vérifier les informations saisies.']); exit; }
$to='contact@fidcc.ma'; $subject='Nouvelle demande depuis fidcc.ma';
$body="Nom : $name\nEmail : $email\nTéléphone : $phone\n\nDemande :\n$message\n";
$headers=['From: Site FIDCC <contact@fidcc.ma>','Reply-To: '.$email,'Content-Type: text/plain; charset=UTF-8','X-Mailer: PHP/'.phpversion()];
if (!mail($to,$subject,$body,implode("\r\n",$headers))) { http_response_code(503); echo json_encode(['error'=>'Le message n’a pas pu être transmis. Écrivez-nous à contact@fidcc.ma.']); exit; }
$_SESSION['last_contact']=time(); echo json_encode(['ok'=>true]);
