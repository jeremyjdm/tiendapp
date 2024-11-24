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

// Manejar adición de método de pago
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $tipo_pago = $_POST['tipo_pago'] ?? '';

    // Validar los datos antes de insertarlos
    if ($tipo_pago) {
        $stmt = $conn->prepare("INSERT INTO metodos_pago (tipo_pago) VALUES (?)");
        $stmt->bind_param("s", $tipo_pago);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Manejar eliminación de método de pago
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar'])) {
    $id_metodo_pago = $_POST['id_metodo_pago'] ?? '';
    
    // Validar el ID del método de pago
    if ($id_metodo_pago) {
        $stmt = $conn->prepare("DELETE FROM metodos_pago WHERE id_metodo_pago = ?");
        $stmt->bind_param("i", $id_metodo_pago);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Obtener métodos de pago
$result = $conn->query("SELECT * FROM metodos_pago");
$metodos_pago = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Métodos de Pago</title>
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
        }
        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
            display: flex;
            justify-content: center;
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
            align-items: center;
            margin-top: 20px;
        }
        input[type="text"] {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 300px;
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

    <h2>Gestión de Métodos de Pago</h2>

    <div class="form-container">
        <form method="POST">
            <input type="text" name="tipo_pago" placeholder="Agregar nuevo método de pago" required>
            <input type="submit" name="agregar" value="Agregar">
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Método</th>
                <th>Tipo de Pago</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($metodos_pago)): ?>
                <tr>
                    <td colspan="3" style="text-align: center;">No hay métodos de pago disponibles.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($metodos_pago as $metodo): ?>
                    <tr>
                        <td><?= $metodo['id_metodo_pago'] ?></td>
                        <td><?= htmlspecialchars($metodo['tipo_pago']) ?></td>
                        <td>
                            <form style="display:inline;" method="POST">
                                <input type="hidden" name="id_metodo_pago" value="<?= $metodo['id_metodo_pago'] ?>">
                                <input type="submit" name="eliminar" value="Eliminar" style="background-color: #dc3545;">
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
