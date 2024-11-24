<?php
session_start();
if ($_SESSION['perfil'] !== 'Empleado') {
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
            color: #333;
        }
        h2 {
            color: #007BFF;
            text-align: center;
            margin-bottom: 20px;
        }
        h3 {
            margin-top: 30px;
            color: #555;
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
        .form-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-width: 400px;
            margin: 0 auto;
        }
        input[type="text"],
        input[type="email"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }
        input[type="text"]:focus,
        input[type="email"]:focus {
            border-color: #007BFF;
            outline: none;
        }
        .no-data {
            text-align: center;
            font-style: italic;
            color: #777;
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
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($clientes)): ?>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?= $cliente['id_cliente'] ?></td>
                        <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                        <td><?= htmlspecialchars($cliente['email']) ?></td>
                        <td><?= htmlspecialchars($cliente['telefono']) ?></td>
                        <td><?= htmlspecialchars($cliente['direccion']) ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                                <button type="submit" name="eliminar">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="no-data">No hay clientes registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h3>Agregar Cliente</h3>
    <div class="form-container">
        <form method="post">
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="telefono" placeholder="Teléfono" required>
            <input type="text" name="direccion" placeholder="Dirección" required>
            <button type="submit" name="agregar">Agregar Cliente</button>
        </form>
    </div>

    <div class="button-container">
        <form action="homee.php" method="POST"> <!-- Acción para regresar -->
            <button type="submit">Regresar</button> 
        </form>
    </div>

</body>
</html>
