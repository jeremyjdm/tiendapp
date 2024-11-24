
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Tienda de Pesca</title>
	<style>
		body {
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			background-image: url('arte.jpeg');
			background-size: cover;
			background: linear-gradient(25deg, #015a95, #f2f2f2);
			margin: 0;
			padding: 1.5rem;
		}
		header {
			background: linear-gradient(25deg, #43CAD9, #015a95);
			color: black;
			text-align: center;
			padding: 1.5rem;
		}
		header img {
			width: 100px;
            height: auto;
            margin-bottom: 50px;
			border-radius: 100px;
		}
		h1 {
			color: white;
			margin: 0;
			font-size: 2.5rem;
			size: 20px
		}
		.container {
			display: flex;
			justify-content: space-around;
			flex-wrap: wrap;
			padding: 30px;
			gap: 50px;
		}
		.product {
			background: #43CAD9;
			border-radius: 100px;
			box-shadow: 2px 4px 8px rgba(0, 0, 0, 0.1);
			padding: 15px;
			text-align: center;
			width: 300px;
		    height: 250px;
		}
		.product-categorias {
			background: #43CAD9;
			border-radius: 100px;
			box-shadow: 2px 4px 8px rgba(0, 0, 0, 0.1);
			padding: 15px;
			text-align: center;
			width: 300px;
		    height: 250px;
		}
		.product:hover {
			transform: scale(1.05);
		}
		.product-categorias:hover {
			transform: scale(1.05);
		}
		.product img {
			max-width: 45px;
			height: auto;
			border-radius: 10px;
			margin-bottom: 15px;
		}
		.product-categorias img {
			max-width: 45px;
			height: auto;
			border-radius: 10px;
			margin-bottom: 15px;
		}
		.product h2 {
			font-size: 1.8rem;
			color: black;
		}
		.product-categorias h2 {
			font-size: 1.8rem;
			color: black;
			margin-top:-15px;
		}
		.features {
			text-align: left;
			margin-bottom: 15px;
		}
		.features ul {
			list-style-type: disc;
			padding-left: 20px;
			color: #555;
		}
		.buy-button {
			background-color: #038587;
			color: white;
			padding: 12px 30px;
			border: none;
			border-radius: 20px;
			cursor: pointer;
			font-size: 1rem;
			text-transform: uppercase;
		}
		.buy-button:hover {
			background-color: #4a88b2;
		}
		footer {
			background-color: #04325b;
			color: white;
			text-align: center;
			padding: 10px;
			margin-top: 30px;
		}
		
        /* Estilos del menú */
        nav {
            width: 200px;
            height: 100vh; /* Ajusta la altura al 100% de la pantalla */
            background-color: #333; /* Fija el menú en el lado izquierdo */
            top: 0;
            left: 0; /* Alinea los elementos en columna */
        }
        nav ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

		button {
            width: 100%;
            padding: 10px;
            background-color: #d9534f; /* Color rojo para el botón de cerrar sesión */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }

        button:hover {
            background-color: #c9302c; /* Color más oscuro al pasar el ratón */
        }
	</style>
</head>
<body>
    <header>
<div class="container">
	<img src="img/logo.png">
    <h1>Bienvenido,  Empleado. </h1> <!-- Reemplaza [Nombre del Usuario] con la variable adecuada -->
    <form action="login.php" method="POST"> <!-- Acción para cerrar sesión -->
		<button class="submit">Cerrar Sesión</button> 
    </form>
</div>
    </header>

    <div class="container">
    <!-- Producto 1 -->
    <div class="product">
        <img src="img/venta.png" alt="">
        <h2>Registros de Ventas</h2>
        <div class="features"></div>
        <form action="ventase.php" method="POST">
            <button class="buy-button">Entrar</button>
        </form>
    </div>

    <div class="product">
        <img src="img/compra.png" alt="Caña de Pescar Profesional">
        <h2>Gestión de Productos</h2>
        <div class="features"></div>
        <form action="productose.php" method="POST">
            <button class="buy-button">Entrar</button>
        </form>
    </div>
</div>

<div class="container">
    <!-- Producto 4 -->
    <div class="product">
        <img src="img/cliente.png" alt="Caña de Pescar Profesional">
        <h2>Clientes</h2>
        <form action="clientese.php" method="POST"> <!-- Asegúrate de que 'clientes.php' existe -->
            <button class="buy-button">Entrar</button>
        </form>
    </div>
</div>


    <footer>
        <p>&copy; 2024 Tienda de Pesca. Todos los derechos reservados.</p>
    </footer>
	
</body>
</html>

