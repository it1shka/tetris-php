<?php

final class Storage {
  private static array $shapes = [
    "I" => "XXXX|X.X.X.X",
    "O" => "XX.XX",
    "T" => "XXX.OXO|OX.XX.OX|OXO.XXX|XO.XX.XO",
    "L" => "XXX.XOO|XX.OX.OX|OOX.XXX|XO.XO.XX",
    "J" => "XXX.OOX|OX.OX.XX|XOO.XXX|XX.XO.XO",
    "Z" => "XXO.OXX|OX.XX.XO",
    "S" => "OXX.XXO|XO.XX.OX",
  ];

  private static array $colors = [
    "#172547", "#9F8170",
    "#4B5320", "#458B74",
    "#C62D42", "#E5C6CE",
    "#D193A7", "#824644",
  ];

  private static function intoFrames(string $shape): array {
    return array_map(function (string $rawFrame): array {
      $frames = [];
      foreach (explode(".", $rawFrame) as $i => $row) {
        foreach (str_split($row) as $j => $col) {
          if ($col !== "X") continue;
          array_push($frames, [$i, $j]);
        }
      }
      return $frames;
    }, explode("|", $shape));
  }

  public static function getRandomShape(): array {
    $name = array_rand(Storage::$shapes);
    $frames = Storage::intoFrames(Storage::$shapes[$name]);
    return [$name, $frames];
  }

  public static function getRandomColor(): string {
    $choice = array_rand(Storage::$colors);
    return Storage::$colors[$choice];
  }
}
