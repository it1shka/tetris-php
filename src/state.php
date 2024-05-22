<?php declare(strict_types = 1);

include_once "tetromino.php";
include_once "encoder.php";
include_once "randomizer.php";

define("FIELD_HEIGHT", 15);
define("FIELD_WIDTH", 10);

define("BLOCK_CELL_COLOR", "#ccc");
define("EMPTY_CELL_COLOR", "#eee");

final class Init {
  public static function createEmptyField(mixed $value = null): array {
    $field = [];
    for ($row = 0; $row < FIELD_HEIGHT; $row++) {
      $values = array_fill(0, FIELD_WIDTH, $value);
      array_push($field, $values);
    }
    return $field;
  }

  public static function createInitialTetromino(): Tetromino {
    [$name, $frames] = Storage::getRandomShape();
    $position = [-1, intdiv(FIELD_WIDTH, 2)];
    $color = Storage::getRandomColor();
    return new Tetromino($name, $frames, $position, $color);
  }
}

final class State {
  public function __construct (
    private bool $finished,
    private int $score,
    private Tetromino $currentTetromino,
    private string $nextTetrominoName,
    private array $field,
  ) {}

  public static function fromQuery(): State {
    $finished = isset($_GET["finished"])
      ? $_GET["finished"]
      : false;
    $score = isset($_GET["score"]) 
      ? (int) $_GET["score"] 
      : 0;
    $currentTetromino = isset($_GET["current_tetromino"]) 
      ? Encoder::decodeTetromino($_GET["current_tetromino"])
      : Init::createInitialTetromino();
    $nextTetrominoName = isset($_GET["next_tetromino_name"]) 
      ? $_GET["next_tetromino_name"]
      : Storage::getRandomTetrominoName();
    $field = isset($_GET["field"]) 
      ? Encoder::decodeBooleanMatrix($_GET["field"])
      : Init::createEmptyField();
    return new State($finished, $score, $currentTetromino, $nextTetrominoName, $field);
  }

  public function toQuery(): string {
    return http_build_query([
      "finished" => $this->finished,
      "score" => $this->score,
      "current_tetromino" => Encoder::encodeTetromino($this->currentTetromino),
      "next_tetromino_name" => $this->nextTetrominoName,
      "field" => Encoder::encodeBooleanMatrix($this->field),
    ]);
  }

  public function mutate(): State {
    if ($this->finished) return $this;
    // TODO: pull action from $_GET["action"]
    // TODO: and mutate the state
    return $this;
  }

  public function render(): string {
    $field = $this->renderField();
    $controls = $this->renderControls();
    return <<<END
      <main class="game-container">
        <div class="game-info">
          <h2>Score: $this->score</h2>
          <h2>Next: $this->nextTetrominoName</h2>
        </div>
        <div class="game-field">$field</div>
        <div class="game-controls">$controls</div>
      </main>
    END;
  }

  private function renderField(): string {
    $tiles = [
      ...$this->currentTetromino->intoTiles(),
      ...$this->fieldIntoTiles(),
    ];
    $coloredField = Init::createEmptyField(EMPTY_CELL_COLOR);
    foreach ($tiles as $tile) {
      $coloredField[$tile->row][$tile->column] = $tile->color;
    }
    $output = "";
    foreach ($coloredField as $row) {
      foreach ($row as $color) {
        $output .= "<div style=\"background-color: $color\"></div>";
      }
    }
    return $output;
  }

  private function renderControls(): string {
    // TODO: 
    return "";
  }

  private function fieldIntoTiles(): array {
    $tiles = [];
    foreach ($this->field as $i => $row) {
      foreach ($row as $j => $col) {
        if (!$col) continue;
        $tile = new Tile($i, $j, EMPTY_CELL_COLOR);
        array_push($tiles, $tile);
      }
    }
    return $tiles;
  }
}
