<?php

for ($i=1; $i <= 100; $i++) {
  if ($i % 15 === 0) {
    echo "foobar";
  }
  elseif ($i % 5 === 0) {
    echo "bar";
  }
  elseif ($i % 3 === 0) {
    echo "foo";
  }
  else echo $i;

  echo ($i === 100) ? "\n" :", ";
}

?>
