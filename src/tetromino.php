<?php
declare(strict_types=1);
namespace Tetromino;

final class Shape {
  public function __construct (
    public readonly string $name,
    public readonly array $body,
    public readonly int $rows,
    public readonly int $cols,
  ) {}

  public static function fromString(string $name, string $source): Shape {
    $rows = array_map('trim', explode("\n", $source));
    $rows = array_filter($rows, fn (string $row) => !empty($row));
    $body = [];
    foreach ($rows as $i => $row) {
      foreach (str_split($row) as $j => $col) {
        if ($col !== "X") continue;
        array_push($body, [$i, $j]);
      }
    }
    $rowsCount = count($rows);
    $colsCount = strlen($rows[0]);
    return new Shape($name, $body, $rowsCount, $colsCount);
  }

  public function rotate(): Shape {
    $rotatedBody = array_map(function (array $pos) {
      [$row, $col] = $pos;
      return [$col, $this->cols - $row - 1];
    }, $this->body);
    return new Shape($this->name, $rotatedBody, $this->cols, $this->rows);
  }
}

final class ShapeStorage {
  private static array $shapes;

  public static function init(): void {
    ShapeStorage::$shapes  = [
      Shape::fromString("Straight", "XXXX"),
      Shape::fromString("Square", "XX\nXX"),
      Shape::fromString("T", "XXX\nOXO"),
      Shape::fromString("L", "XO\nXO\nXX"),
      Shape::fromString("J", "OX\nOX\nXX"),
      Shape::fromString("S", "OXX\nXXO"),
      Shape::fromString("Z", "XXO\nOXX"),
    ];
  }

  public static function shapes(): array {
    return [...ShapeStorage::$shapes];
  }

  public static function byName(string $shapeName): ?Shape {
    foreach (ShapeStorage::$shapes as $shape) {
      if ($shape->name === $shapeName)  {
        return $shape;
      }
    }
    return null;
  }

  public static function random(): Shape {
    $choice = array_rand(ShapeStorage::$shapes);
    return ShapeStorage::$shapes[$choice];
  }
}

ShapeStorage::init();

final class ShapeSet {
  private readonly array $frames;
  private int $pointer;

  private static function createFrames(Shape $initial): array {
    $frames = [$initial];
    for ($i = 0; $i < 3; $i++) {
      array_push($frames, end($frames)->rotate());
    }
    return $frames;
  }

  public function __construct (Shape $initial) {
    $this->frames = ShapeSet::createFrames($initial);
    $this->pointer = 0;
  }

  public function current(): Shape {
    return $this->frames[$this->pointer];
  }

  public function previous(): void {
    $this->pointer--;
    if ($this->pointer < 0) {
      $this->pointer = count($this->frames) - 1;
    }
  }

  public function next(): void {
    $this->pointer++;
    if ($this->pointer >= count($this->frames)) {
      $this->pointer = 0;
    }
  }
}

final class Tile {
  public function __construct (
    public readonly int $row,
    public readonly int $col,
    public readonly string $color,
  ) {}
}

final class Tetromino {
  public function __construct (
    public readonly string $color,
    private readonly ShapeSet $shapeSet,
    private int $row,
    private int $col,
  ) {}

  public function name(): string {
    return $this->shapeSet->current()->name;
  }

  public function rotateLeft(): void {
    $this->shapeSet->previous();
  }

  public function rotateRight(): void {
    $this->shapeSet->next();
  }

  public function moveLeft(): void {
    $this->col--;
  }

  public function moveRight(): void {
    $this->col++;
  }

  public function moveDown(): void {
    $this->row++;
  }

  public function getTiles(): array {
    return array_map(function ($pos) {
      [$row, $col] = $pos;
      $row += $this->row;
      $col += $this->col;
      return new Tile($row, $col, $this->color);
    }, $this->shapeSet->current()->body);
  }
}
