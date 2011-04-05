<?php

class KID {
    
    function generateKIDmod10($pre, $prelen, $post, $postlen) {
        $calc = sprintf("%0".$prelen."d%0".$postlen."d", $pre, $post);
        
        $sum = 0;
        $weight = 2;
        for($pos = strlen($calc) - 1; $pos >= 0; $pos--) {
            $product = $calc[$pos] * $weight;
            
            for($i = 0; $i < strlen($product); $i++) {
                $sum += substr($product, $i, 1);
            }
            
            if($weight == 2) {
                $weight = 1;
            } else {
                $weight = 2;
            }
            
        }

        $check = 10 - substr($sum, -1, 1);
        
        return $calc.$check;
    }
    
    
}

?>