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

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Manejar adición de venta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $id_cliente = $_POST['id_cliente'];
    $id_metodo_pago = $_POST['id_metodo_pago'];
    $total = $_POST['total'];
    $fecha = date("Y-m-d H:i:s"); // Obtener la fecha y hora actual

    $stmt = $conn->prepare("INSERT INTO ventas (id_cliente, fecha, id_metodo_pago, total) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issd", $id_cliente, $fecha, $id_metodo_pago, $total);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Manejar eliminación de venta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar'])) {
    if (isset($_POST['id_venta'])) {
        $id_venta = $_POST['id_venta'];

        // Primero, eliminar los detalles relacionados
        $stmt_detalle = $conn->prepare("DELETE FROM detalle_ventas WHERE id_venta = ?");
        $stmt_detalle->bind_param("i", $id_venta);
        $stmt_detalle->execute();
        $stmt_detalle->close();

        // Ahora, eliminar la venta
        $stmt = $conn->prepare("DELETE FROM ventas WHERE id_venta = ?");
        $stmt->bind_param("i", $id_venta);
        if ($stmt->execute()) {
            // Eliminación exitosa
            $stmt->close();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error al eliminar la venta: " . $stmt->error; // Manejar el error si ocurre
        }
    }
}

// Manejar edición de venta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar'])) {
    $id_venta = $_POST['id_venta'];
    $id_cliente = $_POST['id_cliente'];
    $id_metodo_pago = $_POST['id_metodo_pago'];
    $total = $_POST['total'];

    $stmt = $conn->prepare("UPDATE ventas SET id_cliente = ?, id_metodo_pago = ?, total = ? WHERE id_venta = ?");
    $stmt->bind_param("isdi", $id_cliente, $id_metodo_pago, $total, $id_venta);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Obtener ventas con detalles de clientes y métodos de pago
$query = "
    SELECT v.*, c.nombre AS nombre_cliente, mp.tipo_pago AS metodo_pago
    FROM ventas v
    JOIN clientes c ON v.id_cliente = c.id_cliente
    JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
";
$result = $conn->query($query);
$ventas = $result->fetch_all(MYSQLI_ASSOC);

// Obtener todos los clientes y métodos de pago para el formulario
$clientes = $conn->query("SELECT * FROM clientes")->fetch_all(MYSQLI_ASSOC);
$metodos_pago = $conn->query("SELECT * FROM metodos_pago")->fetch_all(MYSQLI_ASSOC);
$conn->close();

$home_url = ($_SESSION['perfil'] === 'Root') ? "homer.php" : "homeg.php";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ventas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e9f0f5; /* Color de fondo suave */
            color: #333;
            margin: 20px;
        }
        h2 {
            color: #007BFF; /* Título en azul */
        }
        table, th, td {
            border: 1px solid #ddd;
            border-collapse: collapse;
            padding: 8px;
            text-align: left;
        }
        table {
            width: 100%;
            margin-bottom: 16px;
            background-color: #fff; /* Color de fondo de la tabla */
        }
        th {
            background-color: #007BFF; /* Cabecera en azul */
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1; /* Color de hover */
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-width: 400px;
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
        }
        input[type="number"], select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #007BFF; /* Botones en azul */
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px;
            border-radius: 4px;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3; /* Color de hover en botones */
        }
        #editForm {
            display: none;
            background-color: #fff; /* Color de fondo del formulario de edición */
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); /* Sombra para el formulario */
        }
    </style>
</head>
<body>

    <h2>Gestión de Ventas</h2>
    <table>
        <thead>
            <tr>
                <th>ID Venta</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Método de Pago</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ventas as $venta): ?>
                <tr>
                    <td><?= $venta['id_venta'] ?></td>
                    <td><?= htmlspecialchars($venta['nombre_cliente']) ?></td>
                    <td><?= $venta['fecha'] ?></td>
                    <td><?= htmlspecialchars($venta['metodo_pago']) ?></td>
                    <td>$<?= number_format($venta['total'], 2) ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id_venta" value="<?= $venta['id_venta'] ?>">
                            <button type="submit" name="eliminar">Eliminar</button>
                        </form>
                        <button onclick="editSale(<?= $venta['id_venta'] ?>, '<?= htmlspecialchars($venta['nombre_cliente']) ?>', '<?= $venta['fecha'] ?>', '<?= htmlspecialchars($venta['metodo_pago']) ?>', <?= $venta['total'] ?>)">Editar</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Agregar Venta</h3>
    <form method="post">
        <label for="id_cliente">Cliente:</label>
        <select name="id_cliente" id="id_cliente" required>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?= $cliente['id_cliente'] ?>"><?= htmlspecialchars($cliente['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="id_metodo_pago">Método de Pago:</label>
        <select name="id_metodo_pago" id="id_metodo_pago" required>
            <?php foreach ($metodos_pago as $metodo): ?>
                <option value="<?= $metodo['id_metodo_pago'] ?>"><?= htmlspecialchars($metodo['tipo_pago']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="total">Total:</label>
        <input type="number" name="total" placeholder="Total" required min="0" step="0.01">
        <button type="submit" name="agregar">Agregar Venta</button>
    </form>

    <div id="editForm">
        <h3>Editar Venta</h3>
        <form method="post" id="updateForm">
            <input type="hidden" name="id_venta" id="edit_id_venta">
            <label for="edit_id_cliente">Cliente:</label>
            <select name="id_cliente" id="edit_id_cliente" required>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= $cliente['id_cliente'] ?>"><?= htmlspecialchars($cliente['nombre']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="edit_id_metodo_pago">Método de Pago:</label>
            <select name="id_metodo_pago" id="edit_id_metodo_pago" required>
                <?php foreach ($metodos_pago as $metodo): ?>
                    <option value="<?= $metodo['id_metodo_pago'] ?>"><?= htmlspecialchars($metodo['tipo_pago']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="edit_total">Total:</label>
            <input type="number" name="total" id="edit_total" placeholder="Total" required min="0" step="0.01">
            <button type="submit" name="editar">Actualizar Venta</button>
            <button type="button" onclick="document.getElementById('editForm').style.display='none';">Cancelar</button>
        </form>
    </div>

    <form action="<?= $home_url ?>" method="POST"> <!-- Acción para regresar -->
        <button type="submit">Regresar</button> 
    </form>

    <script>
        function editSale(id, cliente, fecha, metodo_pago, total) {
            document.getElementById('edit_id_venta').value = id;
            document.getElementById('edit_id_cliente').value = cliente;
            document.getElementById('edit_id_metodo_pago').value = metodo_pago;
            document.getElementById('edit_total').value = total;
            document.getElementById('editForm').style.display = 'block';
        }
    </script>

</body>
</html>
