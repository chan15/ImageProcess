<style>
code {
    color: #000066;
    display: block;
    padding: .8em;
    line-height: 1.45em;
    background: #E6E6F5;
    width: 500px;
    border-radius: 6px;
    border: 3px dotted #C299FF;
}
</style>
<?php

include '../class/ImageProcess.php';

$base = 'base.jpg';
$ip = new ImageProcess;
$ip->load($base);
$ip->resize(200);
$ip->outputType = 'png';
$ip->fileName = 'convert';
$output = $ip->output('uploads');

echo '<h3>Resize</h3>';
echo '<pre><code>
$base = \'base.jpg\';
$ip = new ImageProcess;
$ip->load($base);
$ip->resize(200);
$ip->outputType = \'png\';
$ip->fileName = \'convert\';
$output = $ip->output(\'uploads\');
</code></pre>';
echo '<img src="base.jpg">';
echo '<img src="'.$output['fullPath'].'">';

echo '<hr>';

$ip = new ImageProcess;
$ip->load($base);
$ip->crop(array(50, 50, 50, 50));
$ip->outputType = 'gif';
$ip->fileName = 'crop';
$output = $ip->output('uploads');

echo '<h3>Crop</h3>';
echo '<pre><code>
$ip = new ImageProcess;
$ip->load($base);
$ip->crop(array(50, 50, 50, 50));
$ip->outputType = \'gif\';
$ip->fileName = \'crop\';
$output = $ip->output(\'uploads\');
</code></pre>';
echo '<img src="base.jpg">';
echo '<img src="'.$output['fullPath'].'">';

echo '<hr>';

$ip = new ImageProcess;
$ip->fileName = 'enlarge';
$ip->resizable = true;
$ip->load($base);
$ip->resize(400);
$output = $ip->output('uploads');

echo '<h3>Enlarge Resize</h3>';
echo '<pre><code>
$ip = new ImageProcess;
$ip->fileName = \'resizable\';
$ip->resizable = true;
$ip->load($base);
$ip->resize(400);
$output = $ip->output(\'uploads\');
</code></pre>';
echo '<img src="base.jpg">';
echo '<img src="'.$output['fullPath'].'">';
