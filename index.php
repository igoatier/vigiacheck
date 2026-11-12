<?php
declare(strict_types=1);

$query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '?page=login';
header('Location: php-version/index.php' . $query);
exit;

