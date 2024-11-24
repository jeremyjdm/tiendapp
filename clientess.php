<?php
session_start();
if ($_SESSION['perfil'] !== 'secretaria') {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bdtienda";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Manejar adición de cliente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';

    // Validar los datos antes de insertarlos
    if ($nombre && $email && $telefono && $direccion) {
        $stmt = $conn->prepare("INSERT INTO clientes (nombre, email, telefono, direccion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $email, $telefono, $direccion);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Manejar eliminación de cliente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar'])) {
    $id_cliente = $_POST['id_cliente'] ?? '';
    
    // Validar el ID del cliente
    if ($id_cliente) {
        $stmt = $conn->prepare("DELETE FROM clientes WHERE id_cliente = ?");
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Obtener clientes
$result = $conn->query("SELECT * FROM clientes");
$clientes = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        h2 {
            color: #333;
        }
        table {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .button-container {
            margin-top: 20px;
        }
        button {
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <h2>Gestión de Clientes</h2>
    <table>
        <thead>
            <tr>
                <th>ID Cliente</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Dirección</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($clientes)): ?>
                <tr>
                    <td colspan="5">No hay clientes disponibles.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?= $cliente['id_cliente'] ?></td>
                        <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                        <td><?= htmlspecialchars($cliente['email']) ?></td>
                        <td><?= htmlspecialchars($cliente['telefono']) ?></td>
                        <td><?= htmlspecialchars($cliente['direccion']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="button-container">
        <form action="homes.php" method="POST"> <!-- Acción para regresar -->
            <button type="submit">Regresar</button> 
        </form>
    </div>

</body>
</html>
