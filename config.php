<?php
// Database configuration
define('DB_HOST', 'localhost');     // Database host
define('DB_USER', 'root');         // Database username
define('DB_PASS', '');             // Database password
define('DB_NAME', 'loja_virtual');  // Database name

// Create database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Erro ao conectar ao banco de dados: " . $conn->connect_error);
    }
    
    // Set charset to utf8
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    // Log error (in production, you should log to a file instead of displaying)
    error_log("Erro ao conectar ao banco de dados: " . $e->getMessage());
    
    // Display user-friendly message (in production, show a generic message)
    die("Desculpe, houve um erro ao conectar ao banco de dados. Por favor, tente novamente mais tarde.");
}

// Function to sanitize input data
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Function to close database connection
function close_connection() {
    global $conn;
    if ($conn) {
        $conn->close();
    }
}

// Register shutdown function to ensure connection is closed
register_shutdown_function('close_connection');
?> 