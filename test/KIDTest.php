<?php
    include_once "../src/KID.php";

    $kid = new KID();
    
    $main = 1234;
    $person = 5678;
    
    $calculatedKid = $kid -> generateKIDmod10($main, 4, $person, 4);
    
    if($calculatedKid != "123456782") {
        die("Bad kid generated: $calculatedKid, expected 123456782");
    } else {
        echo "generateKIDmod10 ok<br>";
    }
    
    $nullkid = $kid->generateKIDmod10("12", 4, "123", 5);
    
    if(strpos(" ", $nullkid) >=0) {
        die("Bad kid generated: $nullkid");
    }
    
    echo "Nullkid: $nullkid";

?>