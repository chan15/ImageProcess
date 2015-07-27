<?php

include '../class/ImageProcess.php';

$base = 'base.jpg';
$ip = new ImageProcess;
$ip->load($base);
$ip->resize(300);
$ip->outputType = 'png';
$ip->fileName = 'convert';
$output = $ip->output('uploads');

echo '<h3>Resize</h3>';
echo '<img src="base.jpg">';
echo '<img src="'.$output['fullPath'].'">';

$ip = new ImageProcess;
$ip->load($base);
$ip->crop(array(100, 100, 100, 100));
$ip->outputType = 'gif';
$ip->fileName = 'crop';
$output = $ip->output('uploads');

echo '<h3>Crop</h3>';
echo '<img src="base.jpg">';
echo '<img src="'.$output['fullPath'].'">';
