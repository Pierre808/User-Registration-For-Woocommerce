<?php

ob_start();
?>

<h2> Settings </h2>

<?php
$content = ob_get_clean();

echo $content;