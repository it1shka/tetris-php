<?php declare(strict_types = 1);

include_once "./tetromino.php";
include_once "./randomizer.php";

define("SEPARATOR", "-");

final class Encoder {
  public static function encodeBooleanMatrix(array $matrix): string {
    $parts = array_map(function (array $row): int {
      $zeroesAndOnes = array_map(fn (bool $e) => $e ? "1" : "0", $row);
      $binaryString = "1" . implode($zeroesAndOnes);
      return bindec($binaryString);
    }, $matrix);
    return implode(SEPARATOR, $parts);
  }

  public static function decodeBooleanMatrix(string $rawMatrix): array {
    return array_map(function (string $row): array {
      $binaryString = decbin((int) $row);
      return array_map(function (string $symbol): bool {
        return $symbol === "1";
      }, str_split(substr($binaryString, 1)));
    }, explode(SEPARATOR, $rawMatrix));
  }

  public static function encodeTetromino(Tetromino $tetromino): string {
    return implode(SEPARATOR, [
      array_search($tetromino->name, array_keys(Storage::$shapes)),
      ...$tetromino->position,
      array_search($tetromino->color, Storage::$colors),
      $tetromino->getRotation(),
    ]);
  }

  public static function decodeTetromino(string $rawTetromino): Tetromino {
    $parts = explode(SEPARATOR, $rawTetromino);
    [$nameIndex, $row, $col, $colorIndex, $rotation] = $parts;
    $name = array_keys(Storage::$shapes)[$nameIndex];
    $frames = Storage::$shapes[$name];
    $position = [(int)$row, (int)$col];
    $color = Storage::$colors[$colorIndex];
    $tetromino = new Tetromino($name, $frames, $position, $color);
    $tetromino->setRotation((int) $rotation);
    return $tetromino;
  }
}
