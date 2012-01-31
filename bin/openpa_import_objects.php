<?php

// lancia in sequenza il comando:
// php bin/php/ezcsvimport.php --class=argomento --storage-dir=./extension/openpa/data/classificazioni/ 154 ./extension/openpa/data/classificazioni/argomento.csv

$nodes = array(
"maggioranza_minoranza"=> 161,
"testata"=> 152,
"legislatura"=> 159,
"fase"=> 151,
"tipo_risposta"=> 166,
"posti_disponibili"=> 165,
"servizio"=> 173,
"tipo_telefono"=> 174,
"tipo_struttura"=> 172,
"macroeventodellavita"=> 156,
"macroargomento"=> 154,
"argomento_stampa"=> 153,
"tipo_servizio_sul_territorio"=> 148,
//"folder"=> 147,
"tipo_commissione"=> 163,
"tipo_bando"=> 150,
"lista_elettorale"=> 160,
"argomento"=> 154,
"tipo_servizio_ristoranti"=> 168,
//"tipo_ristorante"=> ,
"posizione"=> 164,
"area_regolamento"=> 158,
"eventodellavita"=>156,
"io_sono"=>157
);

$path = "./extension/openpa/data/classificazioni";

if ($handle = opendir($path)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
	    $classes_array = explode(".", $entry);
            $class = $classes_array[0];
	    system("php bin/php/ezcsvimport.php --class=".$class." --storage-dir=".$path." ".$nodes[$class]." ".$path."/".$class.".csv");
        }
    }
    closedir($handle);
}

?>
