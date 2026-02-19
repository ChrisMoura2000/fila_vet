<?php
session_start();
header('Content-Type: application/json');

// Credenciais fixas (você pode mudar)
$ADMIN_USER = 'admin';
$ADMIN_PASS = 'vet2025';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['user']) || !isset($data['pass'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados incompletos']);
    exit;
}

if ($data['user'] === $ADMIN_USER && $data['pass'] === $ADMIN_PASS) {
    $_SESSION['admin_logado'] = true;
    $_SESSION['admin_user'] = $data['user'];
    echo json_encode(['sucesso' => true]);
} else {
    http_response_code(401);
    echo json_encode(['erro' => 'Usuário ou senha inválidos']);
}
?>
