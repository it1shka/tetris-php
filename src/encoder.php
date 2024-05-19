<?php declare(strict_types = 1);

include_once "./tetromino.php";
include_once "./randomizer.php";

final class Encoder {
  public static function encodeBooleanMatrix(array $matrix): string {
    $parts = array_map(function (array $row): int {
      $zeroesAndOnes = array_map(fn (bool $e) => $e ? "1" : "0", $row);
      $binaryString = "1" . implode($zeroesAndOnes);
      return bindec($binaryString);
    }, $matrix);
    return implode("-", $parts);
  }

  public static function decodeBooleanMatrix(string $rawMatrix): array {
    return array_map(function (string $row): array {
      $binaryString = decbin((int) $row);
      return array_map(function (string $symbol): bool {
        return $symbol === "1";
      }, str_split(substr($binaryString, 1)));
    }, explode("-", $rawMatrix));
  }

  public static function encodeTetromino(Tetromino $tetromino): string {
    // TODO: also encode it as 1-2-3..
  }

  public static function decodeTetromino(string $rawTetromino): Tetromino {
    // TODO: 
  }
}
