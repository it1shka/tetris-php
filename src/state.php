<?php declare(strict_types = 1);

include_once "tetromino.php";
include_once "encoder.php";
include_once "randomizer.php";

define("FIELD_HEIGHT", 15);
define("FIELD_WIDTH", 10);

define("BLOCK_CELL_COLOR", "#ccc");
define("EMPTY_CELL_COLOR", "#eee");

define("ACTIONS", [
  "move_left",
  "rotate_left",
  "move_down",
  "rotate_right",
  "move_right",
]);

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

  public static function createTetrominoByName(string $name): Tetromino {
    $frames = Storage::getFramesByName($name);
    $position = [-1, intdiv(FIELD_WIDTH, 2)];
    $color = Storage::getRandomColor();
    return new Tetromino($name, $frames, $position, $color);
  }
}

function replaceSnakeCase(string $input): string {
  $parts = explode("_", $input);
  $capitalized = array_map('ucfirst', $parts);
  return implode(" ", $capitalized);
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
      ? (bool) $_GET["finished"]
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
      : Init::createEmptyField(false);
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
    $this->executeAction();
    $this->mountTetromino();
    $this->cleanCompletedRows();
    $this->checkFinish();
    return $this;
  }

  private function cleanCompletedRows(): void {
    // TODO:
  }

  private function checkFinish(): void {
    if ($this->collisionExists()) {
      $this->finished = true;
    }
  }

  private function mountTetromino(): void {
    $this->currentTetromino->move(Direction::Down);
    $collision = $this->collisionExists();
    $this->currentTetromino->move(Direction::Up);
    if (!$collision) return;
    foreach ($this->currentTetromino->intoTiles() as $tile) {
      $this->field[$tile->row][$tile->column] = true;
    }
    $this->currentTetromino = Init::createTetrominoByName($this->nextTetrominoName);
    $this->nextTetrominoName = Storage::getRandomTetrominoName();
  }

  private function executeAction(): void {
    if (!isset($_GET["action"])) return;
    $action = $_GET["action"];
    if (str_starts_with($action, "move")) {
      $this->executeMotion($action);
    } else if (str_starts_with($action, "rotate")) {
      $this->executeRotation($action);
    }
  }

  private function executeMotion(string $rawMotion): void {
    $motion = match ($rawMotion) {
      "move_right" => Direction::Right,
      "move_down" => Direction::Down,
      "move_left" => Direction::Left,
      default => null
    };
    if (is_null($motion)) return;
    $this->currentTetromino->move($motion);
    if ($this->collisionExists()) {
      $this->currentTetromino->move($motion->opposite());
    }
  }

  private function executeRotation(string $rawRotation): void {
    $rotation = match ($rawRotation) {
      "rotate_left" => Rotation::Left,
      "rotate_right" => Rotation::Right,
      default => null
    };
    if (is_null($rotation)) return;
    $this->currentTetromino->rotate($rotation);
    if ($this->collisionExists()) {
      $this->currentTetromino->rotate($rotation->opposite());
    }
  }

  private function collisionExists(): bool {
    $tetrominoTiles = $this->currentTetromino->intoTiles();
    $colliding = array_filter($tetrominoTiles, function (Tile $tetr): bool {
      [$row, $col] = [$tetr->row, $tetr->column];
      if ($row >= FIELD_HEIGHT || $col < 0 || $col >= FIELD_WIDTH) {
        return true;
      }
      if ($row < 0) return false;
      return $this->field[$row][$col];
    });
    return count($colliding) > 0;
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

  // TODO: complete
  private function renderControls(): string {
    if ($this->finished) {
      return "Game is finished. Your score is $this->score";
    }
    $savedState = $this->toQuery();
    $currentURL = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $output = "";
    foreach (ACTIONS as $action) {
      $queryString = $savedState . "&action=$action";
      $link = $currentURL . "?" . $queryString;
      $actionTitle = replaceSnakeCase($action);
      $output .= "<a href=\"$link\">$actionTitle</a>";
    }
    return $output;
  }

  private function fieldIntoTiles(): array {
    $tiles = [];
    foreach ($this->field as $i => $row) {
      foreach ($row as $j => $col) {
        if (!$col) continue;
        $tile = new Tile($i, $j, BLOCK_CELL_COLOR);
        array_push($tiles, $tile);
      }
    }
    return $tiles;
  }
}
