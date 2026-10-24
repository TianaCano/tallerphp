<?php
class Conjuntos
{
    private array $a;
    private array $b;

    public function __construct(array $a, array $b)
    {
        // conjunto no repite elementos: array_unique los quita; sort los ordena
        $this->a = $this->limpiar($a);
        $this->b = $this->limpiar($b);
    }

    // private: es un ayudante interno, nadie de afuera lo necesita
    private function limpiar(array $lista): array
    {
        $lista = array_unique($lista);
        sort($lista);
        return array_values($lista);   // reinicia los índices 0,1,2
    }

    // convierte "1, 2, 3" en [1,2,3] validando que sean enteros
    public static function parsear(string $texto): array
    {
        $partes = preg_split('/[\s,;]+/', trim($texto), -1, PREG_SPLIT_NO_EMPTY);
        $numeros = [];
        foreach ($partes as $p) {
            if (filter_var($p, FILTER_VALIDATE_INT) === false) {
                throw new InvalidArgumentException("\"$p\" no es un entero.");
            }
            $numeros[] = (int)$p;
        }
        return $numeros;
    }

    public function union(): array         { return $this->limpiar(array_merge($this->a, $this->b)); }
    public function interseccion(): array  { return $this->limpiar(array_intersect($this->a, $this->b)); }
    public function diferenciaAB(): array  { return $this->limpiar(array_diff($this->a, $this->b)); }
    public function diferenciaBA(): array  { return $this->limpiar(array_diff($this->b, $this->a)); }
}

// muestra un conjunto como { 1, 2, 3 } o 0 si esta vacio
function mostrar(array $c): string
{
    return $c ? '{ ' . implode(', ', $c) . ' }' : '∅ (vacío)';
}

$ta = $tb = '';
$conj = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ta = $_POST['a'] ?? '';
    $tb = $_POST['b'] ?? '';
    try {
        $conj = new Conjuntos(Conjuntos::parsear($ta), Conjuntos::parsear($tb));
    } catch (InvalidArgumentException $ex) {
        $error = $ex->getMessage();
    }
}

$titulo = 'Conjuntos';
include __DIR__ . '/includes/header.php';
?>
<h1>Operaciones con conjuntos</h1>
<p class="intro">Ingresa dos conjuntos de números enteros separados por comas.</p>

<section class="tarjeta">
  <form method="post">
    <label for="a">Conjunto A</label>
    <input id="a" name="a" value="<?= e($ta) ?>" placeholder="1, 2, 3, 4" required>
    <label for="b">Conjunto B</label>
    <input id="b" name="b" value="<?= e($tb) ?>" placeholder="3, 4, 5, 6" required>
    <button type="submit">Calcular</button>
  </form>

  <?php if ($error): ?>
    <div class="error"><?= e($error) ?></div>
  <?php elseif ($conj): ?>
    <div class="resultado">
      Unión (A ∪ B): <strong><?= e(mostrar($conj->union())) ?></strong><br>
      Intersección (A ∩ B): <strong><?= e(mostrar($conj->interseccion())) ?></strong><br>
      Diferencia A − B: <strong><?= e(mostrar($conj->diferenciaAB())) ?></strong><br>
      Diferencia B − A: <strong><?= e(mostrar($conj->diferenciaBA())) ?></strong>
    </div>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
