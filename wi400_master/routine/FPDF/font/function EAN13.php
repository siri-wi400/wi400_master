<?php 
   function codestring($ean13) {   // generate a string from the 13 digits to be used by the ttf
        $string = substr($ean13,0,1).chr(65 + substr($ean13,1,1));
        $first = substr($ean13,0,1);
        for ($i=3; $i<=7; $i++) {
            $in_a = false;
            switch ($i) {
                case 3:
                    $in_a = in_array($first, array(0, 1, 2, 3)) ? true : false;
                    break;
                case 4:
                    $in_a = in_array($first, array(0, 4, 7, 8)) ? true : false;
                    break;
                case 5:
                    $in_a = in_array($first, array(0, 1, 4, 5, 9)) ? true : false;
                    break;
                case 6:
                    $in_a = in_array($first, array(0, 2, 5, 6, 7)) ? true : false;
                    break;
                case 7:
                    $in_a = in_array($first, array(0, 3, 6, 8, 9)) ? true : false;
                    break;
            }
            if ($in_a) {
                $string = $string.chr(65 + substr($ean13, ($i-1), 1));
            } else {
                $string = $string.chr(75 + substr($ean13, ($i-1), 1));
            }
        }
        $string = $string.'*';
        for ($i=8; $i<=13; $i++) {
            $string = $string.chr(97 + substr($ean13, ($i-1), 1));
        }
        $string = $string.'+';
        return $string;
    }
?>