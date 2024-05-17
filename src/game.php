<?php
declare(strict_types=1);
namespace Game;

include_once "tetromino.php";

use Tetromino\Tetromino;
use Tetromino\ShapeStorage;
use Tetromino\ShapeSet;

define("EMPTY_COLOR", "#ddd");

final class ColorStorage {
  private static array $colors = [
    "#eb6c63", "#c6eb63", "#e6dc5e",
    "#5ae089", "#5acae0", "#705ae0",
    "#ba5ae0", "#e05a77",
  ];

  public static function random(): string {
    $choice = array_rand(ColorStorage::$colors);
    return ColorStorage::$colors[$choice];
  }
}

final class Game {
  
  private Tetromino $tetromino;
  private Tetromino $nextTetromino;
  private array $blocks;

  public function __construct(
    private readonly int $rows,
    private readonly int $cols,
  ) {
    $this->tetromino = $this->createTetromino();
    $this->nextTetromino = $this->createTetromino();
    $this->blocks = [];
  }

  private function createTetromino(): Tetromino {
    $color = ColorStorage::random();
    $shape = ShapeStorage::random();
    $shapeSet = new ShapeSet($shape);
    return new Tetromino($color, $shapeSet, 0, 0);
  }

  public function nextTetrominoName(): string {
    return $this->nextTetromino->name();
  }

  public function coloredField(): array {
    $field = [];
    for ($i = 0; $i < $this->rows; $i++) {
      array_push($field, array_fill(0, $this->cols, EMPTY_COLOR));
    }
    $tiles = [...$this->blocks, ...$this->tetromino->getTiles()];
    foreach ($tiles as $tile) {
      $field[$tile->row][$tile->col] = $tile->color;
    }
    return $field;
  }
}
