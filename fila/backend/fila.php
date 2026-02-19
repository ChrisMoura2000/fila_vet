<?php
// Configuração do banco
$host = 'localhost';
$dbname = 'fila_vet';
$username = 'vet_user';
$password = 'senhasecreta';

// Cabeçalhos para API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Responder preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

error_log("=== INICIANDO FILA.PHP ===");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);

try {
    // Conexão com o banco
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // Buscar fila (só aguardando)
            $stmt = $pdo->query("SELECT id, nome_pet, nome_dono, 
                                 DATE_FORMAT(data_entrada, '%d/%m') as data,
                                 TIME_FORMAT(hora_entrada, '%H:%i') as hora
                                 FROM fila 
                                 WHERE status = 'aguardando' 
                                 ORDER BY created_at ASC");
            $fila = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($fila);
            break;
            
        case 'POST':
            // Adicionar na fila
            $dados = json_decode(file_get_contents('php://input'), true);
            
            if (!$dados || !isset($dados['nome_pet']) || !isset($dados['nome_dono'])) {
                http_response_code(400);
                echo json_encode(['erro' => 'Dados incompletos']);
                break;
            }
            
            $stmt = $pdo->prepare("INSERT INTO fila (nome_pet, nome_dono, data_entrada, hora_entrada) 
                                   VALUES (?, ?, CURDATE(), CURTIME())");
            $stmt->execute([$dados['nome_pet'], $dados['nome_dono']]);
            
            echo json_encode(['sucesso' => true]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['erro' => 'Método não permitido']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no banco de dados']);
}
?>
