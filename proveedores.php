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

// Manejar edición de proveedor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar'])) {
    $id_proveedor = $_POST['id_proveedor'];
    $nombre = $_POST['nombre'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $direccion = $_POST['direccion'] ?? '';

    // Validar los datos antes de actualizar
    if ($id_proveedor && $nombre && $telefono && $email && $direccion) {
        $stmt = $conn->prepare("UPDATE proveedores SET nombre = ?, telefono = ?, email = ?, direccion = ? WHERE id_proveedor = ?");
        $stmt->bind_param("ssssi", $nombre, $telefono, $email, $direccion, $id_proveedor);
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

$home_url = ($_SESSION['perfil'] === 'Root') ? "homer.php" : "homeg.php";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proveedores</title>
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
            text-align: left;
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
            max-width: 400px; /* Aumentado el ancho del formulario */
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
        .button-container {
            margin-bottom: 16px;
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

    <h2>Gestión de Proveedores</h2>
    <table>
        <thead>
            <tr>
                <th>ID Proveedor</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($proveedores as $proveedor): ?>
                <tr>
                    <td><?= $proveedor['id_proveedor'] ?></td>
                    <td><?= htmlspecialchars($proveedor['nombre']) ?></td>
                    <td><?= htmlspecialchars($proveedor['telefono']) ?></td>
                    <td><?= htmlspecialchars($proveedor['email']) ?></td>
                    <td><?= htmlspecialchars($proveedor['direccion']) ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id_proveedor" value="<?= $proveedor['id_proveedor'] ?>">
                            <button type="submit" name="eliminar">Eliminar</button>
                        </form>
                        <button onclick="openEditModal(<?= $proveedor['id_proveedor'] ?>, '<?= htmlspecialchars($proveedor['nombre']) ?>', '<?= htmlspecialchars($proveedor['telefono']) ?>', '<?= htmlspecialchars($proveedor['email']) ?>', '<?= htmlspecialchars($proveedor['direccion']) ?>')">Editar</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Agregar Proveedor</h3>
    <form method="post">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="text" name="telefono" placeholder="Teléfono" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="direccion" placeholder="Dirección" required>
        <button type="submit" name="agregar">Agregar Proveedor</button>
    </form>

    <form action="<?= $home_url ?>" method="POST"> <!-- Acción para regresar -->
        <button type="submit">Regresar</button> 
    </form>

    <!-- Modal de Edición -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Editar Proveedor</h3>
            <form id="editForm" method="post">
                <input type="hidden" name="id_proveedor" id="edit_id_proveedor">
                <input type="text" name="nombre" id="edit_nombre" placeholder="Nombre" required>
                <input type="text" name="telefono" id="edit_telefono" placeholder="Teléfono" required>
                <input type="email" name="email" id="edit_email" placeholder="Email" required>
                <input type="text" name="direccion" id="edit_direccion" placeholder="Dirección" required>
                <button type="submit" name="editar">Actualizar Proveedor</button>
                <button type="button" onclick="closeEditModal()">Cerrar</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, nombre, telefono, email, direccion) {
            document.getElementById('edit_id_proveedor').value = id;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_telefono').value = telefono;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_direccion').value = direccion;

            document.getElementById('editModal').style.display = 'flex'; // Cambiado para centrar el modal
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>

</body>
</html>
