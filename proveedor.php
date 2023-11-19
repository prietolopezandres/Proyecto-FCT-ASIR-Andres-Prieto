<?php

session_start();

// Verificar si hay una sesión activa
if (!isset($_SESSION["nombre_usuario"])) {
    // Si no hay una sesión activa, redirige al formulario de inicio de sesión
    header("Location: login.php");
    exit();
}

// Conexión a la base de datos (reemplaza con tus propios detalles)
$host = "192.168.0.3";
$usuario = "root";
$contrasena = "Abcd1234.";
$base_de_datos = "stashmotor";
$conexion = mysqli_connect($host, $usuario, $contrasena, $base_de_datos);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8");

// Obtener el ID del departamento del usuario actual
$nombre_usuario = $_SESSION["nombre_usuario"];
$consulta_usuario = "SELECT ID_Departamento FROM Usuario WHERE NombreUsuario = ?";
$stmt = mysqli_prepare($conexion, $consulta_usuario);
mysqli_stmt_bind_param($stmt, "s", $nombre_usuario);
mysqli_stmt_execute($stmt);
$resultado_usuario = mysqli_stmt_get_result($stmt);

if ($resultado_usuario) {
    $fila_usuario = mysqli_fetch_assoc($resultado_usuario);
    $id_departamento_usuario = $fila_usuario["ID_Departamento"];

    // Verificar si el usuario tiene el ID de departamento permitido
    if ($id_departamento_usuario != 3) {
        // Si no tiene el ID de departamento permitido, lo redigire a la pagina login.php
        header("Location: login.php");
        exit();
    }
} else {
    // Manejo de error al obtener el ID del departamento del usuario
    die("Error al obtener el ID del departamento del usuario: " . mysqli_error($conexion));
}

// Procesar el formulario si se ha enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre_proveedor = $_POST["nombre"];
    $direccion_proveedor = $_POST["direccion"];
    $telefono_proveedor = $_POST["telefono"];
    $pagina_web_proveedor = $_POST["pagina_web"];
    $correo_proveedor = $_POST["correo"];

    // Por ejemplo, insertar en la base de datos
    $consulta_insertar = "INSERT INTO proveedor (Nombre, Direccion, Telefono, Pagina_Web, Correo_Electronico) VALUES (?, ?, ?, ?, ?)";
    $stmt_insertar = mysqli_prepare($conexion, $consulta_insertar);
    mysqli_stmt_bind_param($stmt_insertar, "sssss", $nombre_proveedor, $direccion_proveedor, $telefono_proveedor, $pagina_web_proveedor, $correo_proveedor);
    $exito_insertar = mysqli_stmt_execute($stmt_insertar);
    

    if ($exito_insertar) {
        $mensaje = "Proveedor registrado exitosamente.";
    } else {
        $mensaje = "Error al registrar el proveedor: " . mysqli_error($conexion);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Formulario de Proveedor</title>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="stail.css">
</head>
<body>
    <header>
        <img src="https://i.imgur.com/xobMPda.png" alt="Logo de la Empresa" class="logo">
        <h1>Administrativo</h1>
    </header>
    <!-- Menú -->
    <nav class="menu">
        <a href="articulo.php">Registrar Artículo</a>
        <a href="proveedor.php">Registrar Proveedor</a>
    </nav>
    <div id="container">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="nombre">Nombre del Proveedor:</label>
            <input type="text" name="nombre" required>

            <label for="direccion">Dirección:</label>
            <input type="text" name="direccion">

            <label for="telefono">Teléfono:</label>
            <input type="text" name="telefono">

            <label for="pagina_web">Página Web:</label>
            <input type="text" name="pagina_web">

            <label for="correo">Correo Electrónico:</label>
            <input type="email" name="correo">

            <input type="submit" value="Registrar Proveedor">
        </form>
        <br>

        <?php
        if (isset($mensaje)) {
            echo "<p>$mensaje</p>";
        }
        ?>

        <!-- Botón para cerrar sesión -->
        <form action="cerrar_sesion.php" method="post">
            <button id="cerrar_sesion_btn" type="submit" name="cerrar_sesion">Cerrar Sesión</button>
        </form>
    </div>
</body>
</html>