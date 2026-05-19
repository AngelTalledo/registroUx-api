<?php
$pdo = new PDO('mysql:host=localhost;dbname=registroux_db', 'root', '');
$row = $pdo->query('SHOW CREATE TABLE diagnostic_evaluations')->fetch();
echo $row['Create Table'];
