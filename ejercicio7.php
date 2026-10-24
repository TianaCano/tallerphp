<?php

session_start(); 

class Calculadora
{
    private const SIMBOLOS = [
        'suma' => '+', 'resta' => '−', 'multiplicacion' => '×',
        'division' => '÷', 'porcentaje' => '% de',
    ];

    public function calcular(float $a, string $operacion, float $b): float
    {
        return match ($operacion) {         
            'suma'           => $a + $b,
            'resta'          => $a - $b,
            'multiplicacion' => $a * $b,
            'division'       => $b == 0
                ? throw new InvalidArgumentException('No se puede dividir entre cero.')
                : $a / $b,
            'porcentaje'     => $a * $b / 100,  
            default          => throw new InvalidArgumentException('Operación no válida.'),
        };
    }

    public function simbolo(string $operacion): string
    {
        return self::SIMBOLOS[$operacion] ?? '?';
    }
}

class Historial
{
    private const CLAVE = 'historial'; 

    public function agregar(string $linea): void
    {
        $_SESSION[self::CLAVE][] = $linea;
    }

    public function obtener(): array
    {
        return $_SESSION[self::CLAVE] ?? [];
    }

    public function borrar(): void
    {
        unset($_SESSION[self::CLAVE]);
    }
}

$calc = new Calculadora();
$historial = new Historial();
$error = null;
$ultimo = null;
$valores = ['a' => '', 'b' => '', 'op' => 'suma'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['accion'] ?? '') === 'borrar') {
        $historial->borrar();                     
    } else {
        $valores = [
            'a' => $_POST['a'] ?? '', 'b' => $_POST['b'] ?? '', 'op' => $_POST['op'] ?? 'suma',
        ];
        if (!is_numeric($valores['a']) || !is_numeric($valores['b'])) {
            $error = 'Escribe dos números válidos (enteros o decimales).';
        } else {
            try {
                $a = (float)$valores['a'];
                $b = (float)$valores['b'];
                $r = round($calc->calcular($a, $valores['op'], $b), 10);
                $ultimo = "$a {$calc->simbolo($valores['op'])} $b = $r";
                $historial->agregar($ultimo);
            } catch (InvalidArgumentException $ex) {
                $error = $ex->getMessage();
            }
        }
    }
}

$titulo = 'Calculadora';
include __DIR__ . '/includes/header.php';
$ops = ['suma' => 'Suma (+)', 'resta' => 'Resta (−)', 'multiplicacion' => 'Multiplicación (×)',
        'division' => 'División (÷)', 'porcentaje' => 'Porcentaje (A % de B)'];
?>
<h1>Calculadora</h1>
<p class="intro">Elige los números y la operación. Cada cuenta se guarda en el historial.</p>

<section class="tarjeta">
  <form method="post">
    <div class="dos-col">
      <div><label for="a">Número A</label>
        <!-- step="any" permite decimales -->
        <input id="a" name="a" type="number" step="any" value="<?= e($valores['a']) ?>" required></div>
      <div><label for="b">Número B</label>
        <input id="b" name="b" type="number" step="any" value="<?= e($valores['b']) ?>" required></div>
    </div>
    <label for="op">Operación</label>
    <select id="op" name="op">
      <?php foreach ($ops as $clave => $texto): ?>
        <option value="<?= $clave ?>" <?= $valores['op'] === $clave ? 'selected' : '' ?>><?= $texto ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" name="accion" value="calcular">Calcular</button>
  </form>

  <?php if ($error): ?>
    <div class="error"><?= e($error) ?></div>
  <?php elseif ($ultimo): ?>
    <div class="resultado"><strong><?= e($ultimo) ?></strong></div>
  <?php endif; ?>
</section>

<section class="tarjeta">
  <h2>Historial</h2>
  <?php if ($historial->obtener()): ?>
    <table>
      <?php foreach (array_reverse($historial->obtener()) as $linea): ?>
        <tr><td><?= e($linea) ?></td></tr>
      <?php endforeach; ?>
    </table>
    <form method="post">
      <button class="secundario" type="submit" name="accion" value="borrar">Borrar historial</button>
    </form>
  <?php else: ?>
    <p>Aún no hay operaciones. Haz tu primera cuenta ✧</p>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
