<?php

session_start();

// Verificar si hay una sesión activa
if (!isset($_SESSION["nombre_usuario"])) {
    // Si no hay una sesión activa, redirige al formulario de inicio de sesión
    header("Location: login.php");
    exit();
}

// Conexión a la base de datos
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
    if ($id_departamento_usuario != 4) {
        // Si no tiene el ID de departamento permitido, redirige a la página login.php
        header("Location: login.php");
        exit();
    }
} else {
    // Manejo de error al obtener el ID del departamento del usuario
    die("Error al obtener el ID del departamento del usuario: " . mysqli_error($conexion));
}

// Obtener los cargos desde la base de datos
$cargos = array();
$consulta_cargos_sql = "SELECT ID_Cargo, Nombre FROM Cargo";
$resultado_cargos = mysqli_query($conexion, $consulta_cargos_sql);

if ($resultado_cargos) {
    while ($fila = mysqli_fetch_assoc($resultado_cargos)) {
        $cargos[$fila['ID_Cargo']] = $fila['Nombre'];
    }
}

// Obtener los departamentos desde la base de datos
$departamentos = array();
$consulta_departamentos_sql = "SELECT ID_Departamento, Nombre FROM Departamento";
$resultado_departamentos = mysqli_query($conexion, $consulta_departamentos_sql);

if ($resultado_departamentos) {
    while ($fila = mysqli_fetch_assoc($resultado_departamentos)) {
        $departamentos[$fila['ID_Departamento']] = $fila['Nombre'];
    }
}

// Mensajes de confirmación y error
$mensaje = "";

// Procesa el formulario de ingreso de empleados
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $fecha_ingreso = $_POST["fecha_ingreso"];
    $correo_electronico = $_POST["correo_electronico"];
    $telefono = $_POST["telefono"];
    $cargo_id = $_POST["cargo_id"];
    $departamento_id = $_POST["departamento_id"];

    // Inserta los datos del empleado en la tabla "Empleado"
    $insertar_empleado_sql = "INSERT INTO Empleado (Nombre, Apellido, Fecha_Ingreso, Correo_Electronico, Telefono, Cargo_ID_Cargo, Departamento_ID_Departamento) VALUES ('$nombre', '$apellido', '$fecha_ingreso', '$correo_electronico', '$telefono', $cargo_id, $departamento_id)";

    if (mysqli_query($conexion, $insertar_empleado_sql)) {
        $mensaje = "Empleado agregado con éxito.";
    } else {
        $mensaje = "Error al agregar empleado: " . mysqli_error($conexion);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Formulario de Ingreso de Empleados</title>
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
        <label for="empleado_id">Ingrese los datos del empleado:</label>
        <br>
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" required pattern="[A-Za-z\s]+">

        <label for="apellido">Apellido:</label>
        <input type="text" name="apellido" id="apellido" required pattern="[A-Za-z\s]+">


            <label for="fecha_ingreso">Fecha de Ingreso:</label>
            <input type="date" name="fecha_ingreso" id="fecha_ingreso" required>
            <br>

            <label for="correo_electronico">Correo Electrónico:</label>
            <input type="email" name="correo_electronico" id="correo_electronico" required>


            <label for="telefono">Teléfono:</label>
            <input type="text" name="telefono" id="telefono">
            <br>

            <label for="departamento_id">Departamento:</label>
            <select name="departamento_id" id="departamento_id" required>
                <?php
                foreach ($departamentos as $departamento_id => $departamento_nombre) {
                    echo '<option value="' . $departamento_id . '">' . $departamento_nombre . '</option>';
                }
                ?>
            </select>
            <br>

            <label for="cargo_id">Cargo:</label>
            <select name="cargo_id" id="cargo_id" required>
                <?php
                foreach ($cargos as $cargo_id => $cargo_nombre) {
                    echo '<option value="' . $cargo_id . '">' . $cargo_nombre . '</option>';
                }
                ?>
            </select>
            <br>

        
            <input type="submit" value="Guardar Empleado">
        </form>
    </div>
</body>
</html>
