<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar se está logado
if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

// Configuração do banco
$host = 'localhost';
$dbname = 'fila_vet';
$username = 'vet_user';
$password = 'senhasecreta';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // Buscar TODA a fila (aguardando + posições)
            $stmt = $pdo->query("SELECT id, nome_pet, nome_dono, 
                                 DATE_FORMAT(data_entrada, '%d/%m') as data,
                                 TIME_FORMAT(hora_entrada, '%H:%i') as hora
                                 FROM fila 
                                 WHERE status = 'aguardando' 
                                 ORDER BY created_at ASC");
            $fila = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($fila);
            break;
            
        case 'DELETE':
            // Remover da fila (atender)
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['erro' => 'ID não fornecido']);
                break;
            }
            
            $stmt = $pdo->prepare("UPDATE fila SET status = 'atendido' WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['sucesso' => true]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['erro' => 'Método não permitido']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no banco']);
}
?>
