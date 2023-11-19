<?php
session_start();

// Verificar si hay una sesión activa
if (!isset($_SESSION["nombre_usuario"])) {
    // Si no hay una sesión activa, redirige a la página de inicio de sesión
    header("Location: login.php");
    exit();
}

// Establecer la conexión a la base de datos
$host = "192.168.0.3";
$usuario = "root";
$contrasena = "Abcd1234.";
$base_de_datos = "stashmotor";
$conexion = mysqli_connect($host, $usuario, $contrasena, $base_de_datos);

// Verificar si la conexión fue exitosa
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Establecer la codificación de caracteres para la conexión
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
    if ($id_departamento_usuario != 4) { // Cambiar el ID del departamento a Recursos Humanos
        // Si no tiene el ID de departamento permitido, redirige a una página de acceso denegado
        header("Location: login.php");
        exit();
    }
} else {
    // Manejo de error al obtener el ID del departamento del usuario
    die("Error al obtener el ID del departamento del usuario: " . mysqli_error($conexion));
}

// Mensajes de confirmación y error
$mensaje = "";

// Procesa el formulario de registro de usuarios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = $_POST["nombre_usuario"];
    $contrasena = $_POST["contrasena"];
    $id_departamento = $_POST["id_departamento"];

    // Verificar que el nombre de usuario no esté en uso
    $consulta_existencia_usuario = "SELECT * FROM Usuario WHERE NombreUsuario = ?";
    $stmt_existencia_usuario = mysqli_prepare($conexion, $consulta_existencia_usuario);
    mysqli_stmt_bind_param($stmt_existencia_usuario, "s", $nombre_usuario);
    mysqli_stmt_execute($stmt_existencia_usuario);
    $resultado_existencia_usuario = mysqli_stmt_get_result($stmt_existencia_usuario);

    if (mysqli_num_rows($resultado_existencia_usuario) == 0) {
        // El nombre de usuario no está en uso, procede con el registro
        $hash_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
        $insertar_usuario_sql = "INSERT INTO Usuario (NombreUsuario, ContrasenaHash, ID_Departamento) VALUES ('$nombre_usuario', '$hash_contrasena', $id_departamento)";

        if (mysqli_query($conexion, $insertar_usuario_sql)) {
            $mensaje = "Usuario registrado con éxito.";
        } else {
            $mensaje = "Error al registrar usuario: " . mysqli_error($conexion);
        }
    } else {
        // El nombre de usuario ya está en uso, muestra un mensaje de error
        $mensaje = "El nombre de usuario ya está en uso. Por favor, elige otro.";
    }
}

// Obtener la lista de departamentos
$departamentos = array();
$consulta_departamentos = "SELECT ID_Departamento, Nombre FROM Departamento";
$resultado_departamentos = mysqli_query($conexion, $consulta_departamentos);

if ($resultado_departamentos) {
    while ($fila = mysqli_fetch_assoc($resultado_departamentos)) {
        $departamentos[$fila['ID_Departamento']] = $fila['Nombre'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Administrativo | Registro de Usuario</title>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="stail.css">
</head>
<body>
    <header>
        <img src="https://i.imgur.com/xobMPda.png" alt="Logo de la Empresa" class="logo">
        <h1>Recursos Humanos</h1>
    </header>

    <!-- Menú -->
    <nav class="menu">
        <a href="departamento.php">Consultar Empleado</a>
        <a href="empleado.php">Registrar Empleado</a>
        <a href="registrarusuario.php">Registrar Usuario</a>
    </nav>

    <div id="container">
        <?php
        if (!empty($mensaje)) {
            echo "<p>$mensaje</p>";
        }
        ?>

        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="nombre_usuario">Nombre de Usuario:</label>
            <input type="text" name="nombre_usuario" id="nombre_usuario" required>

            <label for="contrasena">Contraseña:</label>
            <input type="password" name="contrasena" id="contrasena" required>

            <label for="id_departamento">Departamento:</label>
            <select name="id_departamento" id="id_departamento" required>
                <option value="">Selecciona un departamento</option>
                <?php
                foreach ($departamentos as $departamento_id => $departamento_nombre) {
                    echo '<option value="' . $departamento_id . '">' . $departamento_nombre . '</option>';
                }
                ?>
            </select>

            <input type="submit" value="Registrar Usuario">
        </form>

         <!-- Botón para cerrar sesión -->
         <form action="cerrar_sesion.php" method="post">
            <button id="cerrar_sesion_btn" type="submit" name="cerrar_sesion">Cerrar Sesión</button>
        </form>

        <?php
        if (!empty($mensaje)) {
            echo "<p>$mensaje</p>";
        }
        ?>
    </div>
</body>
</html>
