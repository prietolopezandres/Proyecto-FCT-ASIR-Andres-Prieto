<?php
session_start();

if (!isset($_SESSION["nombre_usuario"])) {
    header("Location: login.php");
    exit();
}

// Conexion con la base de datos.
$host = "192.168.0.3";
$usuario = "root";
$contrasena = "Abcd1234.";
$base_de_datos = "stashmotor";
$conexion = mysqli_connect($host, $usuario, $contrasena, $base_de_datos);

// Verificar si la conexión fue exitosa.
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

    // Verificamos que el usuario pertenezca al departamento id_2, (Departamento Administrativo)
    if ($id_departamento_usuario != 3) {
        // Si no tiene el ID de departamento permitido, redirige nuevamente al login
        header("Location: login.php");
        exit();
    }
} else {
    // Manejo de error al obtener el ID del departamento del usuario
    die("Error al obtener el ID del departamento del usuario: " . mysqli_error($conexion));
}

// Obtener los modelos desde la base de datos
$modelos = array();
$consulta_modelos_sql = "SELECT ID_Modelo, Nombre FROM Modelo";
$resultado_modelos = mysqli_query($conexion, $consulta_modelos_sql);

if ($resultado_modelos) {
    while ($fila = mysqli_fetch_assoc($resultado_modelos)) {
        $modelos[$fila['ID_Modelo']] = $fila['Nombre'];
    }
}

// Mensajes de confirmación y error 
$mensaje = "";

// Procesa el formulario de ingreso de artículos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $descripcion = $_POST["descripcion"];
    $pvp = $_POST["pvp"];
    $modelo_id = $_POST["modelo_id"];

    // Inserta los datos del artículo en la tabla "Articulo"
    $insertar_articulo_sql = "INSERT INTO Articulo (Nombre, Descripcion, PVP, Modelo_ID_Modelo) VALUES ('$nombre', '$descripcion', '$pvp', $modelo_id)";

    if (mysqli_query($conexion, $insertar_articulo_sql)) {
        $mensaje = "Artículo agregado con éxito.";

        // Obtener el ID del artículo recién insertado
        $id_articulo_insertado = mysqli_insert_id($conexion);

        // Insertar relación en la tabla "articulo_modelo"
        $insertar_relacion_sql = "INSERT INTO articulo_modelo (Articulo_ID_Articulo, Modelo_ID_Modelo) VALUES ($id_articulo_insertado, $modelo_id)";
        mysqli_query($conexion, $insertar_relacion_sql);
    } else {
        $mensaje = "Error al agregar artículo: " . mysqli_error($conexion);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Administrativo | Registrar Artículo</title>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="stail.css">
</head>
<body>
    <header>
        <img src="https://i.imgur.com/xobMPda.png" alt="Logo de la Empresa" class="logo">
        <h1>Administrativo | Registrar Artículo</h1>
    </header>

    <!-- Menú -->
    <nav class="menu">
        <a href="articulo.php">Registrar Artículo</a>
        <a href="proveedor.php">Registrar Proveedor</a>
    </nav>

    <div id="container">

        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" required>

            <label for="descripcion">Descripción:</label>
            <input type="text" name="descripcion" id="descripcion">

            <label for="pvp">Precio:</label>
            <input type="text" name="pvp" id="pvp" required>

            <label for="modelo_id">Modelo:</label>
            <select name="modelo_id" id="modelo_id" required>
                <option value="">Selecciona un modelo</option>
                <?php
                foreach ($modelos as $modelo_id => $modelo_nombre) {
                    echo '<option value="' . $modelo_id . '">' . $modelo_nombre . '</option>';
                }
                ?>
            </select>

            <input type="submit" value="Agregar Artículo">
        </form>
        <br>

        <?php
        if (!empty($mensaje)) {
            echo "<p>$mensaje</p>";
        }
        ?>
    </div>
</body>
</html>
