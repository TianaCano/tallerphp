<?php

class Acronimo
{
    private string $frase;

    public function __construct(string $frase)
    {
        $this->setFrase($frase);
    }

    public function setFrase(string $frase): void
    {
        $this->frase = trim($frase);
    }

    public function getFrase(): string
    {
        return $this->frase;
    }

    public function generar(): string
    {
        // los guiones se tratan como espacios
        $texto = str_replace('-', ' ', $this->frase);
        // se borra todo lo que no sea letra, número o espacio
        $texto = preg_replace('/[^\p{L}\p{N}\s]/u', '', $texto);
        //se parte en palabras (ignora espacios repetidos
        $palabras = preg_split('/\s+/u', $texto, -1, PREG_SPLIT_NO_EMPTY);
        // se toma la primera letra de cada palabra en mayúscula
        $sigla = '';
        foreach ($palabras as $palabra) {
            $sigla .= mb_strtoupper(mb_substr($palabra, 0, 1));
        }
        return $sigla;
    }
}
 //controlador
$frase = '';
$resultado = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {   // ¿se envió el formulario?
    $frase = $_POST['frase'] ?? '';             // ?? '' = si no existe, usa vacío
    $resultado = (new Acronimo($frase))->generar();
}

$titulo = 'Acrónimo';
include __DIR__ . '/includes/header.php';
?>
<h1>Frase a acrónimo</h1>
<p class="intro">Escribe un nombre largo y te devuelvo su sigla. Los guiones separan palabras.</p>

<section class="tarjeta">
  <form method="post">
    <label for="frase">Frase</label>
    <input id="frase" name="frase" value="<?= e($frase) ?>" placeholder="Estoy feliz ya que comi!" required>
    <button type="submit">Generar acrónimo</button>
  </form>

  <?php if ($resultado !== null): ?>
    <div class="resultado">Acrónimo: <strong><?= e($resultado) ?></strong></div>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
