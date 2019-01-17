<?php
class Validate extends ValidateCore {
            public static function isDniLite($dni)
	{
    if(strpos($dni,"-")==false){
        $RUT[0] = substr($dni, 0, -1);
        $RUT[1] = substr($dni, -1);
    }else{
        $RUT = explode("-", trim($dni));
    }
    $elRut = str_replace(".", "", trim($RUT[0]));
    $factor = 2;
    for($i = strlen($elRut)-1; $i >= 0; $i--):
        $factor = $factor > 7 ? 2 : $factor;
        $suma += $elRut{$i}*$factor++;
    endfor;
    $resto = $suma % 11;
    $dv = 11 - $resto;
    if($dv == 11){
        $dv=0;
    }else if($dv == 10){
        $dv="k";
    }else{
        $dv=$dv;
    }
   if($dv == trim(strtolower($RUT[1]))){
       return true;
   }else{
       return false;
   }
}     
    }
?>