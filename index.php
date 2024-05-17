<?php

declare(strict_types=1);
include_once "./src/game.php";

use \Game\Game;

$game = new Game(15, 10);
$field = $game->coloredField();

?>

<head>
  <style>
    .field {
      display: grid;
      grid-template-rows: repeat(15, 1fr);
      grid-template-columns: repeat(10, 1fr);
      gap: 1px;
      height: 600px;
      width: 400px;
    }
    
    .next-tetromino {
      font-size: 16px;
    }
  </style>
</head>

<body>
  <h1 class="next-tetromino">
    Next tetromino: 
    <?php echo $game->nextTetrominoName() ?>
  </h1>
  <div class="field">
<?php

foreach ($field as $row) {
  foreach ($row as $col) {
    echo "<div class=\"cell\" style=\"background-color: $col\"></div>";
  }
}

?>
  </div>
</body>
