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

// Manejar adición de producto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $id_categoria = $_POST['id_categoria'];
    $id_proveedor = $_POST['id_proveedor'];

    $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, id_categoria, id_proveedor) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiii", $nombre, $descripcion, $precio, $stock, $id_categoria, $id_proveedor);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Manejar eliminación de producto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar'])) {
    $id_producto = $_POST['id_producto'];
    
    $stmt = $conn->prepare("DELETE FROM productos WHERE id_producto = ?");
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Manejar edición de producto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar'])) {
    $id_producto = $_POST['id_producto'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $id_categoria = $_POST['id_categoria'];
    $id_proveedor = $_POST['id_proveedor'];

    $stmt = $conn->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, id_categoria = ?, id_proveedor = ? WHERE id_producto = ?");
    $stmt->bind_param("ssdiiii", $nombre, $descripcion, $precio, $stock, $id_categoria, $id_proveedor, $id_producto);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Obtener productos con nombres de categoría y proveedor
$query = "
    SELECT p.*, c.nombre_categoria, pr.nombre AS nombre_proveedor
    FROM productos p
    JOIN categorias c ON p.id_categoria = c.id_categoria
    JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
";
$result = $conn->query($query);
$productos = $result->fetch_all(MYSQLI_ASSOC);

// Obtener todas las categorías y proveedores para el formulario
$categorias = $conn->query("SELECT * FROM categorias")->fetch_all(MYSQLI_ASSOC);
$proveedores = $conn->query("SELECT * FROM proveedores")->fetch_all(MYSQLI_ASSOC);
$conn->close();

$home_url = ($_SESSION['perfil'] === 'Root') ? "homer.php" : "homeg.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f0f8ff; /* Color de fondo suave */
        }
        h2, h3 {
            color: #003366; /* Color azul oscuro para títulos */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            background-color: #ffffff; /* Color de fondo blanco para la tabla */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #007bff; /* Color azul para encabezados */
            color: white;
        }
        tr:hover {
            background-color: #e6f0ff; /* Color de fondo claro al pasar el mouse */
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-width: 400px;
            margin-bottom: 16px;
            background-color: #ffffff; /* Color de fondo blanco para formularios */
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        input, select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px;
            background-color: #007bff; /* Color azul para botones */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3; /* Color azul más oscuro al pasar el mouse */
        }
        .button-container {
            margin-bottom: 16px;
        }
        #editModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        #editModal div {
            background: white;
            padding: 20px;
            margin: 50px auto;
            width: 300px;
            position: relative;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>

    <h2>Gestión de Productos</h2>
    <table>
        <thead>
            <tr>
                <th>ID Producto</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Categoría</th>
                <th>Proveedor</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $producto): ?>
                <tr>
                    <td><?= $producto['id_producto'] ?></td>
                    <td><?= htmlspecialchars($producto['nombre']) ?></td>
                    <td><?= htmlspecialchars($producto['descripcion']) ?></td>
                    <td>$<?= number_format($producto['precio'], 2) ?></td>
                    <td><?= $producto['stock'] ?></td>
                    <td><?= htmlspecialchars($producto['nombre_categoria']) ?></td>
                    <td><?= htmlspecialchars($producto['nombre_proveedor']) ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
                            <button type="submit" name="eliminar">Eliminar</button>
                        </form>
                        <button onclick="openEditModal(<?= $producto['id_producto'] ?>, '<?= htmlspecialchars($producto['nombre']) ?>', '<?= htmlspecialchars($producto['descripcion']) ?>', <?= $producto['precio'] ?>, <?= $producto['stock'] ?>, <?= $producto['id_categoria'] ?>, <?= $producto['id_proveedor'] ?>)">Editar</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Agregar Producto</h3>
    <form method="post">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="text" name="descripcion" placeholder="Descripción" required>
        <input type="number" name="precio" placeholder="Precio" required min="0" step="0.01">
        <input type="number" name="stock" placeholder="Stock" required min="0" step="1">

        <label for="id_categoria">Categoría (ID - Nombre):</label>
        <select name="id_categoria" id="id_categoria" required>
            <?php foreach ($categorias as $categoria): ?>
                <option value="<?= $categoria['id_categoria'] ?>"><?= $categoria['id_categoria'] . ' - ' . htmlspecialchars($categoria['nombre_categoria']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="id_proveedor">Proveedor (ID - Nombre):</label>
        <select name="id_proveedor" id="id_proveedor" required>
            <?php foreach ($proveedores as $proveedor): ?>
                <option value="<?= $proveedor['id_proveedor'] ?>"><?= $proveedor['id_proveedor'] . ' - ' . htmlspecialchars($proveedor['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="agregar">Agregar Producto</button>
    </form>

    <div id="editModal">
        <div>
            <h3>Editar Producto</h3>
            <form id="editForm" method="post">
                <input type="hidden" name="id_producto" id="editId" required>
                <input type="text" name="nombre" id="editNombre" placeholder="Nombre" required>
                <input type="text" name="descripcion" id="editDescripcion" placeholder="Descripción" required>
                <input type="number" name="precio" id="editPrecio" placeholder="Precio" required min="0" step="0.01">
                <input type="number" name="stock" id="editStock" placeholder="Stock" required min="0" step="1">

                <label for="editCategoria">Categoría (ID - Nombre):</label>
                <select name="id_categoria" id="editCategoria" required></select>

                <label for="editProveedor">Proveedor (ID - Nombre):</label>
                <select name="id_proveedor" id="editProveedor" required></select>

                <button type="submit" name="editar">Actualizar Producto</button>
                <button type="button" onclick="closeEditModal()">Cerrar</button>
            </form>
        </div>
    </div>

    <script>
        const categorias = <?= json_encode($categorias) ?>;
        const proveedores = <?= json_encode($proveedores) ?>;

        function openEditModal(id, nombre, descripcion, precio, stock, id_categoria, id_proveedor) {
            document.getElementById('editId').value = id;
            document.getElementById('editNombre').value = nombre;
            document.getElementById('editDescripcion').value = descripcion;
            document.getElementById('editPrecio').value = precio;
            document.getElementById('editStock').value = stock;
            
            const categoriaSelect = document.getElementById('editCategoria');
            const proveedorSelect = document.getElementById('editProveedor');

            categoriaSelect.innerHTML = '';
            proveedorSelect.innerHTML = '';

            categorias.forEach(categoria => {
                const option = document.createElement('option');
                option.value = categoria.id_categoria;
                option.textContent = `${categoria.id_categoria} - ${categoria.nombre_categoria}`;
                categoriaSelect.appendChild(option);
            });

            proveedores.forEach(proveedor => {
                const option = document.createElement('option');
                option.value = proveedor.id_proveedor;
                option.textContent = `${proveedor.id_proveedor} - ${proveedor.nombre}`;
                proveedorSelect.appendChild(option);
            });

            categoriaSelect.value = id_categoria;
            proveedorSelect.value = id_proveedor;

            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>

    <div class="button-container">
        <a href="<?= $home_url ?>" style="text-decoration:none;">
            <button>Regresar</button>
        </a>
    </div>
</body>
</html>
