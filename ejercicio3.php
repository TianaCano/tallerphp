<?php

class Estadistica
{
    private array $numeros;   // lista de decimales

    public function __construct(array $numeros)
    {
        if (count($numeros) === 0) {
            throw new InvalidArgumentException('Ingresa al menos un número.');
        }
        $this->numeros = $numeros;
    }

    // metodo "static": se llama sin crear objeto: Estadistica::desdeTexto(...)
    // convierte "1, 2.5  3" en [1.0, 2.5, 3.0] y valida que todo sea numero
    public static function desdeTexto(string $texto): self
    {
        $partes = preg_split('/[\s,;]+/', trim($texto), -1, PREG_SPLIT_NO_EMPTY);
        $numeros = [];
        foreach ($partes as $p) {
            if (!is_numeric($p)) {
                throw new InvalidArgumentException("\"$p\" no es un número válido.");
            }
            $numeros[] = (float)$p;
        }
        return new self($numeros);
    }

    public function promedio(): float
    {
        return array_sum($this->numeros) / count($this->numeros);
    }

    public function mediana(): float
    {
        $orden = $this->numeros;
        sort($orden);                          // ordena de menor a mayor
        $n = count($orden);
        $medio = intdiv($n, 2);
        // si son impares, el del centro; si son pares, el promedio de los dos centrales.
        return $n % 2 ? $orden[$medio] : ($orden[$medio - 1] + $orden[$medio]) / 2;
    }

    public function moda(): array
    {
        $cuentas = array_count_values(array_map('strval', $this->numeros));
        $max = max($cuentas);
        if ($max === 1) {
            return [];                        
        }
        return array_keys(array_filter($cuentas, fn($veces) => $veces === $max));
    }
}

$texto = '';
$stats = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texto = $_POST['numeros'] ?? '';
    try {
        $stats = Estadistica::desdeTexto($texto);
    } catch (InvalidArgumentException $ex) {
        $error = $ex->getMessage();
    }
}

$titulo = 'Estadística';
include __DIR__ . '/includes/header.php';
?>
<h1>Promedio, mediana y moda</h1>
<p class="intro">Escribe los números que quieras, separados por comas o espacios. Aceptan decimales.</p>

<section class="tarjeta">
  <form method="post">
    <label for="numeros">Números</label>
    <input id="numeros" name="numeros" value="<?= e($texto) ?>" placeholder="4, 8.5, 8.5, 10, 2" required>
    <button type="submit">Calcular</button>
  </form>

  <?php if ($error): ?>
    <div class="error"><?= e($error) ?></div>
  <?php elseif ($stats): ?>
    <div class="resultado">
      Promedio: <strong><?= e(round($stats->promedio(), 4)) ?></strong><br>
      Mediana: <strong><?= e(round($stats->mediana(), 4)) ?></strong><br>
      Moda: <strong><?= $stats->moda() ? e(implode(', ', $stats->moda())) : 'No hay moda' ?></strong>
    </div>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
