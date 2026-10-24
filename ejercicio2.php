<?php

class Secuencia
{
    private int $n;

    public function __construct(int $n)
    {
        if ($n < 1) {
            throw new InvalidArgumentException('El número debe ser mayor o igual a 1.');
        }
        $this->n = $n;
    }

    public function getN(): int
    {
        return $this->n;
    }

    public function fibonacci(): array
    {
        if ($this->n > 90) {  // mas de 90 se pasa del limite de enteros de php
            throw new InvalidArgumentException('Para Fibonacci usa un numero hasta 90.');
        }
        $serie = [];
        $a = 0;
        $b = 1;
        for ($i = 0; $i < $this->n; $i++) {
            $serie[] = $a;
            [$a, $b] = [$b, $a + $b]; 
        }
        return $serie;
    }

    // Devuelve 1!, 2!, ..., n!
    public function factorial(): array
    {
        if ($this->n > 20) {
            throw new InvalidArgumentException('Para factorial usa un número hasta 20.');
        }
        $serie = [];
        $acum = 1;
        for ($i = 1; $i <= $this->n; $i++) {
            $acum *= $i;
            $serie[] = $acum;
        }
        return $serie;
    }
}

$numero = '';
$operacion = 'fibonacci';
$serie = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = $_POST['numero'] ?? '';
    $operacion = $_POST['operacion'] ?? 'fibonacci';
    try {
        if (filter_var($numero, FILTER_VALIDATE_INT) === false) {
            throw new InvalidArgumentException('Escribe un número entero.');
        }
        $obj = new Secuencia((int)$numero);
        $serie = ($operacion === 'factorial') ? $obj->factorial() : $obj->fibonacci();
    } catch (InvalidArgumentException $ex) {
        $error = $ex->getMessage(); 
    }
}

$titulo = 'Fibonacci y factorial';
include __DIR__ . '/includes/header.php';
?>
<h1>Fibonacci o factorial</h1>
<p class="intro">Ingresa cuántos términos quieres y elige la operación.</p>

<section class="tarjeta">
  <form method="post">
    <label for="numero">Número</label>
    <input id="numero" name="numero" type="number" value="<?= e($numero) ?>" required>
    <label for="operacion">Operación</label>
    <select id="operacion" name="operacion">
      <option value="fibonacci" <?= $operacion === 'fibonacci' ? 'selected' : '' ?>>Sucesión de Fibonacci</option>
      <option value="factorial" <?= $operacion === 'factorial' ? 'selected' : '' ?>>Factorial</option>
    </select>
    <button type="submit">Calcular</button>
  </form>

  <?php if ($error): ?>
    <div class="error"><?= e($error) ?></div>
  <?php elseif ($serie !== null): ?>
    <div class="resultado"><?= e(implode(' , ', $serie)) ?></div>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
