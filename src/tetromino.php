<?php declare(strict_types = 1);

enum Direction {
  case Up;
  case Right;
  case Down;
  case Left;

  public function opposite(): Direction {
    return match ($this) {
      Direction::Up => Direction::Down,
      Direction::Down => Direction::Up,
      Direction::Right => Direction::Left,
      Direction::Left => Direction::Right,
    };
  }
}

enum Rotation {
  case Left;
  case Right;

  public function opposite(): Rotation {
    return match ($this) {
      Rotation::Left => Rotation::Right,
      Rotation::Right => Rotation::Left,
    };
  }
}

final class Tile {
  public function __construct (
    public readonly int $row,
    public readonly int $column,
    public readonly string $color,
  ) {}
}

final class Tetromino {
  private int $framePointer = 0;

  public function __construct (
    public readonly string $name,
    private readonly array $frames,
    public array $position,
    public readonly string $color,
  ) {}

  public function move(Direction $direction): void {
    [$row, $col] = $this->position;
    $this->position = match ($direction) {
      Direction::Up => [$row - 1, $col],
      Direction::Right => [$row, $col + 1],
      Direction::Down => [$row + 1, $col],
      Direction::Left => [$row, $col - 1]
    };
  }

  public function rotate(Rotation $rotation): void {
    $this->framePointer = match ($rotation) {
      Rotation::Left => $this->framePointer <= 0 
        ? count($this->frames) - 1
        : $this->framePointer - 1,
      Rotation::Right => $this->framePointer >= count($this->frames) - 1
        ? 0
        : $this->framePointer + 1,
    };
  }

  public function intoTiles(): array {
    $frame = $this->frames[$this->framePointer];
    return array_map(function (array $pos) { 
      $row = $pos[0] + $this->position[0];
      $col = $pos[1] + $this->position[1];
      return new Tile($row, $col, $this->color);
    }, $frame);
  }

  // needed for encoding / decoding
  public function setRotation(int $framePointer): void {
    $this->framePointer = $framePointer;
  }

  public function getRotation(): int {
    return $this->framePointer;
  }
}
