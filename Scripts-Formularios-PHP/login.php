<?php
session_start();

$host = "192.168.0.3";
$usuario = "root";
$contrasena = "Abcd1234.";
$base_de_datos = "stashmotor";
$conexion = mysqli_connect($host, $usuario, $contrasena, $base_de_datos);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8");

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = $_POST["nombre_usuario"];
    $contrasena = $_POST["contrasena"];

    $buscar_usuario_sql = "SELECT * FROM Usuario WHERE NombreUsuario = ?";
    $stmt = mysqli_prepare($conexion, $buscar_usuario_sql);
    mysqli_stmt_bind_param($stmt, "s", $nombre_usuario);
    mysqli_stmt_execute($stmt);
    $resultado_buscar_usuario = mysqli_stmt_get_result($stmt);

    if ($resultado_buscar_usuario && mysqli_num_rows($resultado_buscar_usuario) > 0) {
        $fila_usuario = mysqli_fetch_assoc($resultado_buscar_usuario);
        $hash_contrasena = $fila_usuario["ContrasenaHash"];
        $id_departamento_usuario = $fila_usuario["ID_Departamento"];

        if (password_verify($contrasena, $hash_contrasena)) {
            $_SESSION["nombre_usuario"] = $nombre_usuario;

            switch ($id_departamento_usuario) {
                case 1:
                    header("Location: cliente.php");
                    exit();
                case 3:
                    header("Location: administracion.php");
                    exit();
                case 4:
                    header("Location: departamento.php");
                    exit();
                default:
                    $mensaje = "Nombre de usuario o contraseña incorrecta. Por favor, introduce nuevamente los campos.";
            }
        } else {
            $mensaje = "Nombre de usuario o contraseña incorrecta. Por favor, introduce nuevamente los campos.";
        }
    } else {
        $mensaje = "Nombre de usuario o contraseña incorrecta. Por favor, introduce nuevamente los campos.";
    }
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Inicio de Sesión</title>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="stail.css">
    <style>
       
        #container {
            text-align: center;
        }

        form {
            margin: 0 auto; /* Centra el formulario */
            max-width: 300px; /* Limita el ancho del formulario */
        }

        label {
            display: block; 
            margin-bottom: 5px; 
        }

        input[type="text"],
        input[type="password"] {
            width: 100%; 
            padding: 8px; 
            margin-bottom: 10px; 

        input[type="submit"] {
            width: 100%; 
            padding: 10px; 
            background-color: #B5B2B2; 
            color: black;
            border: none;
            cursor: pointer; 
        }

        input[type="submit"]:hover {
            background-color: #808080; 
        }

        p {
            color: #ff0000;
        }
    </style>
</head>
<body>
    <header>
        <img src="https://i.imgur.com/xobMPda.png" alt="Logo de la Empresa" class="logo">
        <h1>Inicio de Sesión</h1>
    </header>

    <div id="container">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="nombre_usuario">Nombre de Usuario:</label>
            <input type="text" name="nombre_usuario" id="nombre_usuario" required>

            <label for="contrasena">Contraseña:</label>
            <input type="password" name="contrasena" id="contrasena" required>

            <input type="submit" value="Iniciar Sesión">
        </form>
        <br>

        <?php
        // Muestra mensajes de error o confirmación
        if (!empty($mensaje)) {
            echo "<p>$mensaje</p>";
        }
        ?>
    </div>
</body>
</html>
