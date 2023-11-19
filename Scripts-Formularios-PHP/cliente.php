<?php
session_start();

// Verificar si hay una sesión activa
if (!isset($_SESSION["nombre_usuario"])) {
    // Si no hay una sesión activa, redirige al formulario de inicio de sesión
    header("Location: login.php");
    exit();
}

// Establecer la conexión a la base de datos
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
    if ($id_departamento_usuario != 1) {
        // Si no tiene el ID de departamento permitido, redirige a la pagina de login.
        header("Location: login.php");
        exit();
    }
} else {
    // Manejo de error al obtener el ID del departamento del usuario
    die("Error al obtener el ID del departamento del usuario: " . mysqli_error($conexion));
}

// Mensajes de confirmación y error
$mensaje = "";

// Procesa el formulario de ingreso de clientes
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dni = $_POST["dni"];
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $telefono = $_POST["telefono"];
    $direccion = $_POST["direccion"];

    // Inserta los datos del cliente en la tabla "cliente"
    $insertar_cliente_sql = "INSERT INTO Cliente (DNI, Nombre, Apellido, Telefono, Direccion) VALUES ('$dni', '$nombre', '$apellido', '$telefono', '$direccion')";

    if (mysqli_query($conexion, $insertar_cliente_sql)) {
        $mensaje = "Cliente agregado con éxito.";
    } else {
        $mensaje = "Error al agregar cliente: " . mysqli_error($conexion);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Registro de Cliente</title>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="stail.css">
</head>
<body>
    <header>
        <img src="https://i.imgur.com/xobMPda.png" alt="Logo de la Empresa" class="logo">
        <h1>Registro de Cliente</h1>
    </header>

    <div class="menu">
        <a href="cliente.php">Cliente</a>
    </div>

    <div id="container">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="dni">DNI / NIF:</label>
            <input type="text" name="dni" id="dni" required>

            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" required>

            <label for="apellido">Apellido:</label>
            <input type="text" name="apellido" id="apellido" required>

            <label for="telefono">Teléfono:</label>
            <input type="tel" name="telefono" id="telefono">

            <label for="direccion">Dirección:</label>
            <input type="text" name="direccion" id="direccion">

            <input type="submit" value="Registrar Cliente">
        </form>

        <?php
        // Muestra el mensaje de confirmación
        if (!empty($mensaje)) {
            echo "<p>$mensaje</p>";
        }
        ?>

        <!-- Botón para cerrar sesión -->
        <form action="login.php" method="post">
            <button id="cerrar_sesion_btn" type="submit" name="cerrar_sesion">Cerrar Sesión</button>
        </form>
    </div>
</body>
</html>
