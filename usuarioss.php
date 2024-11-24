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

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Manejar adición de usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $nombre_usuario = $_POST['nombre_usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $perfil = $_POST['perfil'] ?? '';

    // Validar los datos antes de insertarlos
    if ($nombre_usuario && $contrasena && $perfil) {
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, contrasena, perfil) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre_usuario, $contrasena, $perfil);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Manejar eliminación de usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar'])) {
    $id_usuario = $_POST['id_usuario'] ?? '';
    
    // Validar el ID del usuario
    if ($id_usuario) {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Obtener usuarios
$result = $conn->query("SELECT * FROM usuarios");
$usuarios = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
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
    </style>
</head>
<body>

    <h2>Gestión de Usuarios</h2>

    <table>
        <thead>
            <tr>
                <th>ID Usuario</th>
                <th>Nombre Usuario</th>
                <th>Contraseña</th>
                <th>Perfil</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($usuarios)): ?>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= $usuario['id_usuario'] ?></td>
                        <td><?= htmlspecialchars($usuario['nombre_usuario']) ?></td>
                        <td><?= htmlspecialchars($usuario['contrasena']) ?></td>
                        <td><?= htmlspecialchars($usuario['perfil']) ?></td>
                            <form style="display:inline;" method="POST">
                                <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No hay usuarios registrados.</td>
                </tr>
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
