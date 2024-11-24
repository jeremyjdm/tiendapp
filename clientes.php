<?php
session_start();

// Verificar si el usuario tiene acceso permitido (Root o Gerente)
if (!isset($_SESSION['perfil']) || !in_array($_SESSION['perfil'], ['Root', 'gerente'])) {
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

// Manejar edición de cliente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar'])) {
    $id_cliente = $_POST['id_cliente'];
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';

    // Validar los datos antes de actualizar
    if ($id_cliente && $nombre && $email && $telefono && $direccion) {
        $stmt = $conn->prepare("UPDATE clientes SET nombre = ?, email = ?, telefono = ?, direccion = ? WHERE id_cliente = ?");
        $stmt->bind_param("ssssi", $nombre, $email, $telefono, $direccion, $id_cliente);
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

$home_url = ($_SESSION['perfil'] === 'Root') ? "homer.php" : "homeg.php";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff; /* Fondo azul claro */
            margin: 20px;
        }
        h2 {
            color: #003366; /* Azul oscuro */
        }
        table, th, td {
            border: 1px solid #007bff; /* Bordes azules */
            border-collapse: collapse;
            padding: 8px;
        }
        table {
            width: 100%;
            margin-bottom: 16px;
        }
        th {
            background-color: #007bff; /* Fondo azul brillante para encabezados */
            color: white; /* Color de texto blanco */
        }
        tr:nth-child(even) {
            background-color: #f9f9f9; /* Filas alternas en gris claro */
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-width: 400px; /* Ampliado el ancho del formulario */
            margin-bottom: 16px;
        }
        button {
            background-color: #007bff; /* Color azul para botones */
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px; /* Bordes redondeados */
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3; /* Azul más oscuro al pasar el mouse */
        }
        /* Estilos del modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 20px;
            width: 400px; /* Ampliar el ancho del modal */
            border-radius: 8px; /* Bordes redondeados */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Sombra suave */
        }
        input[type="text"],
        input[type="email"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%; /* Hacer que los inputs ocupen el 100% */
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
                        <button onclick="openEditModal(<?= $cliente['id_cliente'] ?>, '<?= htmlspecialchars($cliente['nombre']) ?>', '<?= htmlspecialchars($cliente['email']) ?>', '<?= htmlspecialchars($cliente['telefono']) ?>', '<?= htmlspecialchars($cliente['direccion']) ?>')">Editar</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Agregar Cliente</h3>
    <form method="post">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="telefono" placeholder="Teléfono" required>
        <input type="text" name="direccion" placeholder="Dirección" required>
        <button type="submit" name="agregar">Agregar Cliente</button>
    </form>

    <form action="<?= $home_url ?>" method="POST"> <!-- Acción para regresar -->
        <button type="submit">Regresar</button> 
    </form>

    <!-- Modal de Edición -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Editar Cliente</h3>
            <form id="editForm" method="post">
                <input type="hidden" name="id_cliente" id="edit_id_cliente">
                <input type="text" name="nombre" id="edit_nombre" placeholder="Nombre" required>
                <input type="email" name="email" id="edit_email" placeholder="Email" required>
                <input type="text" name="telefono" id="edit_telefono" placeholder="Teléfono" required>
                <input type="text" name="direccion" id="edit_direccion" placeholder="Dirección" required>
                <button type="submit" name="editar">Actualizar Cliente</button>
                <button type="button" onclick="closeEditModal()">Cerrar</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, nombre, email, telefono, direccion) {
            document.getElementById('edit_id_cliente').value = id;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_telefono').value = telefono;
            document.getElementById('edit_direccion').value = direccion;

            document.getElementById('editModal').style.display = 'flex'; // Mostrar el modal
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none'; // Ocultar el modal
        }
    </script>

</body>
</html>
