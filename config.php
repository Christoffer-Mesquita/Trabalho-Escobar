<?php
define('DB_HOST', 'localhost');     
define('DB_USER', 'root');         
define('DB_PASS', '');             
define('DB_NAME', 'loja_virtual');  

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Erro ao conectar ao banco de dados: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    error_log("Erro ao conectar ao banco de dados: " . $e->getMessage());
    
    die("Desculpe, houve um erro ao conectar ao banco de dados. Por favor, tente novamente mais tarde.");
}

function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

function close_connection() {
    global $conn;
    if ($conn) {
        $conn->close();
    }
}

register_shutdown_function('close_connection');
?> 