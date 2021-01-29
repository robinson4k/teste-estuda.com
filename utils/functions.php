<?php
function formataData($data, $format = 'd/m/Y H:i') {
    return !empty($data) ? date($format, strtotime(str_replace('/', '-', $data))) : null;
}

function mask($mask,$str) {
    $str = str_replace(" ","",$str);
    for($i=0;$i<strlen($str);$i++){
        $mask[strpos($mask,"#")] = $str[$i];
    }
    return $mask;
}

function limpar($val)
{
    return str_replace(['-','_', '/', '.', '(', ')', ' '], '', $val); // Removes special chars.
}