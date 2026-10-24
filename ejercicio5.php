<?php


class ConversorBinario
{
    private int $numero;
    private array $pasos = [];   // guarda cada división para mostrarla

    public function __construct(int $numero)
    {
        $this->numero = $numero;
    }

    public function getPasos(): array
    {
        return $this->pasos;
    }

    public function convertir(): string
    {
        $this->pasos = [];
        if ($this->numero === 0) {
            return '0';
        }
        $n = abs($this->numero);        // trabajamos con el valor positivo
        $binario = '';
        while ($n > 0) {
            $resto = $n % 2;            // % = resto de la división
            $this->pasos[] = "$n ÷ 2 = " . intdiv($n, 2) . ", resto $resto";
            $binario = $resto . $binario;  // el resto nuevo se pone AL INICIO
            $n = intdiv($n, 2);
        }
        return ($this->numero < 0 ? '-' : '') . $binario;
    }
}

$numero = '';
$binario = null;
$pasos = [];
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = $_POST['numero'] ?? '';
    if (filter_var($numero, FILTER_VALIDATE_INT) === false) {
        $error = 'Escribe un número entero.';
    } else {
        $conv = new ConversorBinario((int)$numero);
        $binario = $conv->convertir();
        $pasos = $conv->getPasos();
    }
}

$titulo = 'Binario';
include __DIR__ . '/includes/header.php';
?>
<h1>Entero a binario</h1>
<p class="intro">Escribe un número entero y te muestro su versión en binario.</p>

<section class="tarjeta">
  <form method="post">
    <label for="numero">Número entero</label>
    <input id="numero" name="numero" type="number" value="<?= e($numero) ?>" required>
    <button type="submit">Convertir</button>
  </form>

  <?php if ($error): ?>
    <div class="error"><?= e($error) ?></div>
  <?php elseif ($binario !== null): ?>
    <div class="resultado">
      Binario: <strong><?= e($binario) ?></strong>
      <?php if ($pasos): ?>
        <p class="ayuda">Divisiones (lee los restos de abajo hacia arriba):</p>
        <table><?php foreach ($pasos as $p): ?><tr><td><?= e($p) ?></td></tr><?php endforeach; ?></table>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
