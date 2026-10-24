<?php
class Nodo
{
    private string $valor;
    private ?Nodo $izquierdo = null;   // ?Nodo = puede ser un nodo o null (sin hijo)
    private ?Nodo $derecho = null;

    public function __construct(string $valor) { $this->valor = $valor; }

    public function getValor(): string        { return $this->valor; }
    public function getIzquierdo(): ?Nodo     { return $this->izquierdo; }
    public function getDerecho(): ?Nodo       { return $this->derecho; }
    public function setIzquierdo(?Nodo $n): void { $this->izquierdo = $n; }
    public function setDerecho(?Nodo $n): void   { $this->derecho = $n; }
}

// El ÁRBOL: sabe construirse y dibujarse.
class ArbolBinario
{
    private ?Nodo $raiz = null;

    // elegir la combinacion de recorridos segun lo que haya escrito el usuario. */
    public static function desdeRecorridos(array $pre, array $in, array $post): self
    {
        $arbol = new self();
        if ($pre && $in) {                       // incluye el caso de los tres
            self::validar($pre, $in);
            $arbol->raiz = self::preIn($pre, $in);
        } elseif ($post && $in) {
            self::validar($post, $in);
            $arbol->raiz = self::postIn($post, $in);
        } elseif ($pre && $post) {
            self::validar($pre, $post);
            $arbol->raiz = self::prePost($pre, $post);
        } else {
            throw new InvalidArgumentException('Ingresa al menos dos recorridos.');
        }
        return $arbol;
    }

    // revisa que ambos recorridos tengan los mismos nodos y sin repetidos.
    private static function validar(array $x, array $y): void
    {
        $a = $x; $b = $y;
        sort($a); sort($b);
        if ($a !== $b || count(array_unique($x)) !== count($x)) {
            throw new InvalidArgumentException('Los recorridos no coinciden: deben tener los mismos nodos, sin repetir.');
        }
    }

    private static function preIn(array $pre, array $in): ?Nodo
    {
        if (!$pre) return null;
        $nodo = new Nodo($pre[0]);
        $i = array_search($pre[0], $in, true);       // dónde está la raíz en inorden
        $nodo->setIzquierdo(self::preIn(array_slice($pre, 1, $i), array_slice($in, 0, $i)));
        $nodo->setDerecho(self::preIn(array_slice($pre, 1 + $i), array_slice($in, $i + 1)));
        return $nodo;
    }

    private static function postIn(array $post, array $in): ?Nodo
    {
        if (!$post) return null;
        $n = count($post);
        $nodo = new Nodo($post[$n - 1]);
        $i = array_search($post[$n - 1], $in, true);
        $nodo->setIzquierdo(self::postIn(array_slice($post, 0, $i), array_slice($in, 0, $i)));
        $nodo->setDerecho(self::postIn(array_slice($post, $i, $n - 1 - $i), array_slice($in, $i + 1)));
        return $nodo;
    }

    private static function prePost(array $pre, array $post): ?Nodo
    {
        if (!$pre) return null;
        $n = count($pre);
        $nodo = new Nodo($pre[0]);
        if ($n === 1) return $nodo;
        $tam = array_search($pre[1], $post, true) + 1;  // tamaño del subárbol izquierdo
        $nodo->setIzquierdo(self::prePost(array_slice($pre, 1, $tam), array_slice($post, 0, $tam)));
        $nodo->setDerecho(self::prePost(array_slice($pre, 1 + $tam), array_slice($post, $tam, $n - 1 - $tam)));
        return $nodo;
    }

    public function preorden(): array  { return $this->recorrer($this->raiz, 'pre'); }
    public function inorden(): array   { return $this->recorrer($this->raiz, 'in'); }
    public function postorden(): array { return $this->recorrer($this->raiz, 'post'); }

    private function recorrer(?Nodo $n, string $tipo): array
    {
        if (!$n) return [];
        $izq = $this->recorrer($n->getIzquierdo(), $tipo);
        $der = $this->recorrer($n->getDerecho(), $tipo);
        $yo = [$n->getValor()];
        return match ($tipo) {
            'pre'  => array_merge($yo, $izq, $der),
            'in'   => array_merge($izq, $yo, $der),
            default => array_merge($izq, $der, $yo),
        };
    }

    public function aSvg(): string
    {
        $g = ['lineas' => '', 'nodos' => '', 'prof' => 0];
        $col = 0;
        $this->posicionar($this->raiz, 0, $col, $g);
        $ancho = $col * 60 + 20;
        $alto = ($g['prof'] + 1) * 70 + 10;
        return "<svg viewBox=\"0 0 $ancho $alto\" width=\"$ancho\" role=\"img\" aria-label=\"Árbol binario\">"
             . $g['lineas'] . $g['nodos'] . '</svg>';
    }

    private function posicionar(?Nodo $n, int $prof, int &$col, array &$g): ?array
    {
        if (!$n) return null;
        $g['prof'] = max($g['prof'], $prof);
        $izq = $this->posicionar($n->getIzquierdo(), $prof + 1, $col, $g);
        $x = 40 + $col * 60;          // columna propia
        $y = 35 + $prof * 70;
        $col++;
        $der = $this->posicionar($n->getDerecho(), $prof + 1, $col, $g);
        foreach ([$izq, $der] as $hijo) {          // línea hacia cada hijo
            if ($hijo) {
                $g['lineas'] .= "<line x1=\"$x\" y1=\"$y\" x2=\"{$hijo[0]}\" y2=\"{$hijo[1]}\"/>";
            }
        }
        $g['nodos'] .= "<circle cx=\"$x\" cy=\"$y\" r=\"21\"/><text x=\"$x\" y=\"$y\">"
                     . e($n->getValor()) . '</text>';
        return [$x, $y];
    }
}

if (!function_exists('e')) { function e($texto): string { return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8'); } }

// Convierte "A → B → D" o "A,B,D" o "A B D" en ['A','B','D'].
function leerRecorrido(string $t): array
{
    return preg_split('/[\s,;>→\-]+/u', trim($t), -1, PREG_SPLIT_NO_EMPTY);
}

$campos = ['pre' => '', 'in' => '', 'post' => ''];
$arbol = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($campos as $k => $v) {
        $campos[$k] = $_POST[$k] ?? '';
    }
    try {
        $arbol = ArbolBinario::desdeRecorridos(
            leerRecorrido($campos['pre']), leerRecorrido($campos['in']), leerRecorrido($campos['post'])
        );
    } catch (InvalidArgumentException $ex) {
        $error = $ex->getMessage();
    }
}

$titulo = 'Árbol binario';
include __DIR__ . '/includes/header.php';
?>
<h1>Árbol binario</h1>
<p class="intro">Llena mínimo dos recorridos (puedes usar flechas, comas o espacios). Si llenas los tres, uso preorden + inorden.</p>

<section class="tarjeta">
  <form method="post">
    <label for="pre">Preorden</label>
    <input id="pre" name="pre" value="<?= e($campos['pre']) ?>" placeholder="A → B → D → E → C">
    <label for="in">Inorden</label>
    <input id="in" name="in" value="<?= e($campos['in']) ?>" placeholder="D → B → E → A → C">
    <label for="post">Postorden</label>
    <input id="post" name="post" value="<?= e($campos['post']) ?>" placeholder="D → E → B → C → A">
    <button type="submit">Construir árbol</button>
  </form>

  <?php if ($error): ?>
    <div class="error"><?= e($error) ?></div>
  <?php elseif ($arbol): ?>
    <div class="resultado arbol"><?= $arbol->aSvg() ?></div>
    <div class="resultado">
      Preorden: <?= e(implode(' → ', $arbol->preorden())) ?><br>
      Inorden: <?= e(implode(' → ', $arbol->inorden())) ?><br>
      Postorden: <?= e(implode(' → ', $arbol->postorden())) ?>
    </div>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
