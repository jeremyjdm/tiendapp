<?php
session_start();

// Habilitar la visualización de errores en PHP para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
$host = "localhost";
$user = "root"; // Cambiar si es necesario
$pass = ""; // Cambiar si es necesario
$dbname = "bdtienda";

$conn = new mysqli($host, $user, $pass, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$error_message = ""; // Variable para almacenar mensajes de error

// Procesar el formulario de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si los campos están definidos
    $nombre_usuario = isset($_POST['nombre_usuario']) ? $_POST['nombre_usuario'] : '';
    $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';
    $perfil = isset($_POST['perfil']) ? $_POST['perfil'] : '';

    // Validar que el perfil no esté vacío
    if ($perfil === "") {
        $error_message = "Por favor, selecciona un perfil.";
    } else {
        // Consulta para verificar las credenciales del usuario
        $sql = "SELECT * FROM usuarios WHERE nombre_usuario = ? AND perfil = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $nombre_usuario, $perfil);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();

            // Verificar la contraseña encriptada
            if (password_verify($contrasena, $usuario['contrasena'])) {
                // Usuario autenticado correctamente
                $_SESSION['nombre_usuario'] = $nombre_usuario;
                $_SESSION['perfil'] = $perfil;

                // Redirigir a la página correspondiente
                switch ($perfil) {
                    case 'Root':
                        header("Location: homer.php");
                        break;
                    case 'secretaria':
                        header("Location: homes.php");
                        break;
                    case 'gerente':
                        header("Location: homeg.php");
                        break;
                    case 'Empleado':
                        header("Location: homee.php");
                        break;
                    default:
                        $error_message = "Perfil no reconocido.";
                }
                exit(); // Asegúrate de llamar a exit después de header
            } else {
                // Contraseña incorrecta
                $error_message = "Contraseña incorrecta.";
            }
        } else {
            // Usuario no encontrado
            $error_message = "Usuario no encontrado o perfil incorrecto.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - basetienda</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #e9ecef;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 350px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #495057;
        }

        label {
            display: block;
            margin-bottom: 5px;
            text-align: left;
            color: #495057;
        }

        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        select:focus {
            border-color: #80bdff;
            outline: none;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        .back-button {
            background-color: #6c757d;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-align: center;
            margin-top: 10px;
        }

        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Inicio de Sesión</h2>
        <form method="POST" action="">
            <label for="nombre">Usuario:</label>
            <input type="text" id="nombre" name="nombre_usuario" required>

            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required>

            <label for="perfil">Perfil:</label>
            <select id="perfil" name="perfil" required>
                <option value="">Selecciona perfil</option>
                <option value="Root">Root</option>
                <option value="secretaria">Secretaria</option>
                <option value="gerente">Gerente</option>
                <option value="Empleado">Empleado</option>
            </select>

            <input type="submit" value="Iniciar Sesión">
            <?php if (!empty($error_message)) : ?>
                <div class="error"><?php echo $error_message; ?></div>
            <?php endif; ?>
        </form>

        <!-- Botón de regreso -->
        <form method="post" action="index.html"> <!-- Puedes cambiar 'index.php' por la URL a la que deseas regresar -->
            <button type="submit" class="back-button">Regresar</button>
        </form>
    </div>
</body>
</html>
