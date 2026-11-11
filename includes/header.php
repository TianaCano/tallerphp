<?php
if (!function_exists('e')) {
    function e($texto): string {
        return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($titulo ?? 'Taller PHP') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Livvic:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/tallerphp/css/estilos.css">
</head>
<body class="inicio">

  <div class="fondo" aria-hidden="true">
    <span class="orbe o1"></span>
    <span class="orbe o2"></span>
    <span class="orbe o3"></span>
    <span class="orbe o4"></span>
    <span class="orbe o5"></span>
  </div>

  <main class="contenedor contenedor-ejercicio">
    <header class="cabecera-ejercicio">
      <a href="index.html" class="btn-volver">← Volver al menú</a>
    </header>