<?php
declare(strict_types=1);
namespace Tetromino;

final class Shape {
  public function __construct (
    public readonly string $name,
    public readonly array $body,
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
    return new Shape($name, $body);
  }
}

final class ShapeStorage {
  private static array $shapes = [
    Shape::fromString("Straight", "XXXX"),
    Shape::fromString("Square", "XX\nXX"),
    Shape::fromString("T", "XXX\nOXO"),
    Shape::fromString("L", "XO\nXO\nXX"),
    Shape::fromString("J", "OX\nOX\nXX"),
    Shape::fromString("S", "OXX\nXXO"),
    Shape::fromString("Z", "XXO\nOXX"),
  ];

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
}

// TODO: 
final class ShapeSet {
  private readonly array $frames;
  private int $pointer;

  private static function createFrames(Shape $initial): array {
    // TODO: 
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

// TODO: complete class
final class Tetromino {
  public function __construct (
    // TODO: define fields
    // TODO: such as ShapeSet, position, etc.
  ) {}
}
