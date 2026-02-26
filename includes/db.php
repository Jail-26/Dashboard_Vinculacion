<?php
require_once 'config.php';

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $port = DB_PORT;
    private $dbh; // Database handler
    private $stmt;
    private $error;
    
    public function __construct() {
        // Set DSN (Data Source Name) including port if provided
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        if (!empty($this->port)) {
            $dsn .= ';port=' . $this->port;
        }
        
        // Set options
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        );
        
        // Create PDO instance
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            error_log('Database connection error: ' . $this->error);
            // Show a generic message in production — do not expose internals
            if (ini_get('display_errors')) {
                echo 'Error de conexión a la base de datos.';
            }
        }
    }
    
    // Prepare statement with query
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }
    
    // Bind values
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }
    
    // Execute the prepared statement
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            // Log the detailed error and show a generic message
            error_log('Query execution error: ' . $e->getMessage());
            if (ini_get('display_errors')) {
                echo 'Error en la ejecución de la consulta.';
            }
            return false;
        }
    }
    
    // Get result set as array of objects
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }
    
    // Get single record as object
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }
    
    // Get row count
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    // Get last inserted ID
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
    
    // Transactions
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }
    
    public function endTransaction() {
        return $this->dbh->commit();
    }
    
    public function cancelTransaction() {
        return $this->dbh->rollBack();
    }
    
    // Debug dump parameters
    public function debugDumpParams() {
        return $this->stmt->debugDumpParams();
    }
    
    // Función para registrar cambios en el historial
    public function registrarCambio($id_usuario, $tabla, $id_registro, $tipo, $datos_anteriores = null, $datos_nuevos = null) {
        $datos_anteriores = $datos_anteriores ? json_encode($datos_anteriores) : null;
        $datos_nuevos = $datos_nuevos ? json_encode($datos_nuevos) : null;
        $ip = $_SERVER['REMOTE_ADDR'];

        $this->query("INSERT INTO historial_cambios (id_usuario, tabla_afectada, id_registro, tipo_cambio, datos_anteriores, datos_nuevos, ip_usuario, fecha_cambio) 
                      VALUES (:id_usuario, :tabla_afectada, :id_registro, :tipo_cambio, :datos_anteriores, :datos_nuevos, :ip_usuario, NOW())");
        $this->bind(':id_usuario', $id_usuario);
        $this->bind(':tabla_afectada', $tabla);
        $this->bind(':id_registro', $id_registro);
        $this->bind(':tipo_cambio', $tipo);
        $this->bind(':datos_anteriores', $datos_anteriores);
        $this->bind(':datos_nuevos', $datos_nuevos);
        $this->bind(':ip_usuario', $ip);

        return $this->execute();
    }
}
?>