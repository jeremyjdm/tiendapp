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

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Manejar adición de proveedor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $direccion = $_POST['direccion'] ?? '';

    // Validar los datos antes de insertarlos
    if ($nombre && $telefono && $email && $direccion) {
        $stmt = $conn->prepare("INSERT INTO proveedores (nombre, telefono, email, direccion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $telefono, $email, $direccion);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Manejar eliminación de proveedor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar'])) {
    $id_proveedor = $_POST['id_proveedor'] ?? '';
    
    // Validar el ID del proveedor
    if ($id_proveedor) {
        $stmt = $conn->prepare("DELETE FROM proveedores WHERE id_proveedor = ?");
        $stmt->bind_param("i", $id_proveedor);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Obtener proveedores
$result = $conn->query("SELECT * FROM proveedores");
$proveedores = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proveedores</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
            color: #333;
        }
        h2 {
            color: #007BFF;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .button-container {
            text-align: center;
            margin-top: 20px;
        }
        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
            gap: 10px;
        }
        input[type="text"], input[type="email"], input[type="tel"] {
            padding: 10px;
            width: 100%;
            max-width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        input[type="submit"] {
            padding: 10px 20px;
            background-color: #28a745;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

    <h2>Gestión de Proveedores</h2>

    <table>
        <thead>
            <tr>
                <th>ID Proveedor</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Dirección</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($proveedores)): ?>
                <tr>
                    <td colspan="6" style="text-align: center;">No hay proveedores disponibles.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($proveedores as $proveedor): ?>
                    <tr>
                        <td><?= $proveedor['id_proveedor'] ?></td>
                        <td><?= htmlspecialchars($proveedor['nombre']) ?></td>
                        <td><?= htmlspecialchars($proveedor['telefono']) ?></td>
                        <td><?= htmlspecialchars($proveedor['email']) ?></td>
                        <td><?= htmlspecialchars($proveedor['direccion']) ?></td>
                            <form style="display:inline;" method="POST">
                                <input type="hidden" name="id_proveedor" value="<?= $proveedor['id_proveedor'] ?>">
                            </form>
                        </td>
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
