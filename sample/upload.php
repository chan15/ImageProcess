<?php

include '../class/ImageProcess.php';

$ip = new ImageProcess;
$ip->outputType = 'gif';
$upload = $ip->upload($_FILES['file'], 'uploads');
echo '<pre>'; var_dump($upload); echo '</pre>';
echo '<img src="'.$upload['fullPath'].'">';
