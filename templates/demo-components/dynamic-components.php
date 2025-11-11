<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Dynamic Components</title>
</head>
<body>
	
<?php

use Tutor\Components\Button;

?>
<pre><code>
&lt;?php
	echo Button::make()->label( 'I am a button' )->size( 'large' )->variant( 'primary' )->render(); // phpcs:ignore
	echo Button::make()->label( 'I am a button' )->size( 'medium' )->variant( 'primary' )->render(); // phpcs:ignore
	echo Button::make()->label( 'I am a button' )->size( 'small' )->variant( 'primary' )->render(); // phpcs:ignore
	echo Button::make()->label( 'I am a button' )->variant( 'primary-soft' )->render(); // phpcs:ignore 
	echo Button::make()->label( 'I am a button' )->variant( 'destructive' )->render(); // phpcs:ignore 
	echo Button::make()->label( 'I am a button' )->variant( 'destructive-soft' )->render(); // phpcs:ignore 
?&gt;
</code></pre>

<div class="tutor-dynamic-btn-wrapper tutor-flex tutor-gap-3 tutor-items-center tutor-flex-wrap">
<?php
echo Button::make()->label( 'I am a button' )->size( 'large' )->variant( 'primary' )->render(); // phpcs:ignore 
echo Button::make()->label( 'I am a button' )->size( 'medium' )->variant( 'primary' )->render(); // phpcs:ignore 
echo Button::make()->label( 'I am a button' )->size( 'small' )->variant( 'primary' )->render(); // phpcs:ignore 
echo Button::make()->label( 'I am a button' )->variant( 'primary-soft' )->render(); // phpcs:ignore 
echo Button::make()->label( 'I am a button' )->variant( 'primary-soft' )->attr( 'class', 'tutor-btn-loading' )->render(); // phpcs:ignore 
echo Button::make()->label( 'I am a button' )->variant( 'destructive' )->render(); // phpcs:ignore 
echo Button::make()->label( 'I am a button' )->variant( 'destructive-soft' )->render(); // phpcs:ignore 
echo Button::make()->size( 'large' )->icon(
	'<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M11.617 5.28089C12.9779 6.05395 14.0472 6.66146 14.809 7.21795C15.576 7.77827 16.1434 8.36389 16.3465 9.13605C16.4955 9.70222 16.4955 10.2979 16.3465 10.8641C16.1434 11.6362 15.576 12.2218 14.809 12.7821C14.0472 13.3386 12.9779 13.9461 11.6171 14.7192C10.3026 15.466 9.19413 16.0957 8.35263 16.4537C7.50438 16.8145 6.73103 16.9974 5.97943 16.7844C5.42706 16.6278 4.92447 16.3307 4.51959 15.9222C3.97012 15.3679 3.74955 14.6016 3.6452 13.6796C3.54161 12.7641 3.54162 11.5659 3.54163 10.0418V9.9583C3.54162 8.43422 3.54161 7.23596 3.6452 6.32059C3.74955 5.39847 3.97012 4.63223 4.51959 4.07784C4.92447 3.66936 5.42706 3.37227 5.97943 3.21574C6.73103 3.00276 7.50438 3.18563 8.35263 3.54643C9.19413 3.90435 10.3026 4.53409 11.617 5.28089Z" fill="currentColor"></path>
					</svg>' )->variant( 'primary-soft' )->render(); // phpcs:ignore
  
echo Button::make()->attr( 'class', 'tutor-btn-block' )->label( 'I am a block button' )->variant( 'primary-soft' )->render(); // phpcs:ignore
?>

</div>
</body>
</html>
