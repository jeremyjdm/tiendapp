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
    $id_venta = $_POST['id_venta'];
    
    $stmt = $conn->prepare("DELETE FROM ventas WHERE id_venta = ?");
    $stmt->bind_param("i", $id_venta);
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ventas</title>
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
        label {
            margin-top: 10px;
        }
        select, input[type="number"], button {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            width: calc(100% - 22px);
        }
        button {
            background-color: #007BFF;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .button-container {
            text-align: center;
            margin-top: 20px;
        }
        .no-data {
            text-align: center;
            font-style: italic;
            color: #777;
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
            <?php if (!empty($ventas)): ?>
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
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="no-data">No hay ventas registradas.</td>
                </tr>
            <?php endif; ?>
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

    <div class="button-container">
        <form action="homee.php" method="POST"> <!-- Acción para regresar -->
            <button type="submit">Regresar</button> 
        </form>
    </div>

</body>
</html>
