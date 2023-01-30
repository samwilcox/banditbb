<?php
class ColorPalette{
	public $color;
	
	public function __construct($color){
		$this->color = $color;
	}
	public function color_mod($hex, $diff) {
		$rgb = str_split(trim($hex, '# '), 2);
		 
		foreach ($rgb as &$hex) {
		$dec = hexdec($hex);
		if ($diff >= 0) {
		$dec += $diff;
		}
		else {
		$dec -= abs($diff);	
		}
		$dec = max(0, min(255, $dec));
		$hex = str_pad(dechex($dec), 2, '0', STR_PAD_LEFT);
	}
	return '#'.implode($rgb);
	}
	public function createPalette($colorCount=6){
		$colorPalette = array();
		for($i=1; $i<=$colorCount; $i++){
			if($i == 1){
				$color = $this->color;
				$colorVariation = -(($i*4) * 9);
			}
			if($i == 2){
				$color = $newColor;
				$colorVariation = -($i * 9);
			}
			if($i == 3){
				$color = $newColor;
				$colorVariation = -($i * 9);
			}
			if($i == 4){
				$color = $newColor;
				$colorVariation = -($i * 9);
			}
            if($i == 5){
				$color = $newColor;
				$colorVariation = -($i * 9);
			}
            if($i == 6){
				$color = $newColor;
				$colorVariation = -($i * 9);
			}
            if($i == 7){
				$color = $newColor;
				$colorVariation = -($i * 9);
			}
			
			$newColor = $this->color_mod($color, $colorVariation);

			array_push($colorPalette, $newColor);
		}
		return $colorPalette;
	}
}

$colorsList = [ '#FF0000', '#FFC0C0', '#FFFF00', '#00FF00', '#00FFFF', '#0000FF', '#FF00FF', '#FFFFFF' ];

// foreach ( $colorsList as $myColor ) {
//     $pallete = new ColorPalette( $myColor );
//     $cPallete = $pallete->createPalette();

//     foreach ( $cPallete as $c ) {
//         echo '<div style="height: 16px; width: 16px; background-color: ' . $c . '; display: inline-block;"></div>';
//     }

//     echo '<br>';
// }

echo '<input style="width: 30px; height: 30px;" type="color">';