<?php
session_start();

// Verificar si hay una sesión activa
if (!isset($_SESSION["nombre_usuario"])) {
    // Si no hay una sesión activa, redirige a la página de inicio de sesión
    header("Location: login.php?mensaje=Debes iniciar sesión primero");
    exit();
}

// Cerrar la sesión
session_destroy();

// Redirigir a la página de inicio de sesión con un mensaje de éxito
header("Location: login.php?mensaje=Has cerrado sesión exitosamente");
exit();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Cerrar Sesión</title>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="stail.css">
</head>
<body>
    <header>
        <img src="https://i.imgur.com/xobMPda.png" alt="Logo de la Empresa" class="logo">
        <h1>Cerrar Sesión</h1>
    </header>

    <div class="menu">
        <!-- Puedes incluir el mismo menú que en los otros formularios -->
        <a href="departamento.php">Departamento</a>
        <a href="cliente.php">Cliente</a>
        <!-- Agrega otros enlaces del menú si es necesario -->
    </div>

    <div id="container">
        <h2>Cerrar Sesión</h2>
        <!-- Puedes incluir el contenido del formulario si es necesario -->
        <p>¡Has cerrado sesión exitosamente!</p>
        <!-- Puedes agregar más contenido según tus necesidades -->
    </div>
</body>
</html>