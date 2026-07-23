<?php
require_once __DIR__ . '/../../bootstrap.php';

$auth->requireAuth();
?>

<form action="/logout" method="POST">
    <button type="submit">Logout</button>
</form>