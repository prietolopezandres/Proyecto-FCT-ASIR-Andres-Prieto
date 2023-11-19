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

// Obtener la lista de departamentos
$departamentos = array();
$consulta_departamentos = "SELECT ID_Departamento, Nombre FROM Departamento";
$resultado_departamentos = mysqli_query($conexion, $consulta_departamentos);

if ($resultado_departamentos) {
    while ($fila = mysqli_fetch_assoc($resultado_departamentos)) {
        $departamentos[$fila['ID_Departamento']] = $fila['Nombre'];
    }
}

// Mensajes de confirmación y error
$mensaje = "";
$resultado_html = "";

// Procesa el formulario de selección de departamento
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $departamento_id = $_POST["departamento_id"];

    // Consulta para obtener los empleados del departamento seleccionado
    $consulta_empleados = "SELECT e.Nombre, e.Apellido, c.Nombre AS Cargo, e.Fecha_Ingreso
                           FROM empleado e
                           JOIN cargo c ON e.Cargo_ID_Cargo = c.ID_Cargo
                           WHERE e.Departamento_ID_Departamento = $departamento_id";

    $resultado_empleados = mysqli_query($conexion, $consulta_empleados);

    if ($resultado_empleados) {
        // Generar la tabla HTML con los resultados
        $resultado_html = '<table class="result-table">';
        $resultado_html .= '<thead>';
        $resultado_html .= '<tr>';
        $resultado_html .= '<th>Nombre</th>';
        $resultado_html .= '<th>Apellido</th>';
        $resultado_html .= '<th>Cargo</th>';
        $resultado_html .= '<th>Fecha de Ingreso</th>';
        $resultado_html .= '</tr>';
        $resultado_html .= '</thead>';
        $resultado_html .= '<tbody>';

        while ($fila = mysqli_fetch_assoc($resultado_empleados)) {
            $resultado_html .= '<tr>';
            $resultado_html .= '<td>' . $fila['Nombre'] . '</td>';
            $resultado_html .= '<td>' . $fila['Apellido'] . '</td>';
            $resultado_html .= '<td>' . $fila['Cargo'] . '</td>';
            $resultado_html .= '<td>' . $fila['Fecha_Ingreso'] . '</td>';
            $resultado_html .= '</tr>';
        }

        $resultado_html .= '</tbody>';
        $resultado_html .= '</table>';
    } else {
        $mensaje = "Error al obtener los empleados: " . mysqli_error($conexion);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Lista de Empleados por Departamento</title>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="stail.css">
</head>
<body>
    <header>
        <img src="https://i.imgur.com/xobMPda.png" alt="Logo de la Empresa" class="logo">
        <h1>Recursos Humanos</h1>
    </header>

    <nav class="menu">
        <a href="departamento.php">Consultar Empleado</a>
        <a href="empleado.php">Registrar Empleado</a>
        <a href="registrarusuario.php">Registrar Usuario</a>
    </nav>

    <div id="container">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="departamento_id">Selecciona un departamento:</label>
            <select name="departamento_id" id="departamento_id" required>
                <option value="">Selecciona un departamento</option>
                <?php
                foreach ($departamentos as $departamento_id => $departamento_nombre) {
                    echo '<option value="' . $departamento_id . '">' . $departamento_nombre . '</option>';
                }
                ?>
            </select>

            <input type="submit" value="Mostrar Empleados">
        </form>

        <?php
        if (!empty($mensaje)) {
            echo "<p>$mensaje</p>";
        }

        if (!empty($resultado_html)) {
            echo $resultado_html;
        }
        ?>
        <br>

        <form action="cerrar_sesion.php" method="post">
            <button id="cerrar_sesion_btn" type="submit" name="cerrar_sesion">Cerrar Sesión</button>
        </form>
    </div>
</body>
</html>
