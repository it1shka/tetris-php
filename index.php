<?php declare(strict_types = 1);
include_once "src/state.php";

echo "<!DOCTYPE html>";
echo "<link rel=\"stylesheet\" href=\"/styles.css\" />";
echo State::fromQuery()->mutate()->render();
