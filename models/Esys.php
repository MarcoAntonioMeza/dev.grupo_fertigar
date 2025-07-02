<?php
 namespace app\models;

//use yii\base\Model;
//use Yii;

class Esys// extends Model
{
    public static $dias_semana = ["Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado"];
    public static $meses       = [1 => "Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];

    public static function stringTimeToUnix($fecha, $format = "Y-m-d"){
        if(!is_numeric($fecha)){
            $fecha = \DateTime::createFromFormat($format, $fecha);
            $fecha = $fecha->format('U');
        }

        return $fecha;
    }

    public static function stringToTimeUnix($string, $format = "Y-m-d")
    {
        if(!$string)
            return null;

        if(is_numeric($string))
            return $string;

        $fecha = \DateTime::createFromFormat($format, $string);

        return $fecha->format('U');
    }

    public static function fecha_en_texto($fecha, $time = false)
    {
        $ano = date('Y', $fecha);
        $mes = date('n', $fecha);
        $dia = date('d', $fecha);
        $diasemana = date('w', $fecha);

        $time = $time? '. ' . date('h:i a', $fecha): '';

        $dias_semana = ["Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado"];
        $meses       = [1 => "Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];

        return $dias_semana[$diasemana] . ", $dia de " . $meses[$mes] . " de $ano" . $time;
    }

    public static function hace_tiempo_en_texto($fecha)
    {
        $unixTime = date('U', $fecha);

        $segundos = date('U') - $unixTime;
        $minutos  = $segundos / 60;
        $horas    = $minutos / 60;
        $dias     = $horas / 24;
        $meses    = $dias / 30;
        $años     = $meses / 12;


        if($años >= 1){
            $años  = floor($años);
            $meses = floor($meses - ($años * 12));

            $string = $años . ($años >= 2? ' años': ' año');

            if($meses >= 1)
                $string .= ' ' . $meses . ($meses >= 2? ' meses': ' mes'); 

        }elseif($meses >= 1){
            $meses = floor($meses);
            $dias  = floor($dias - ($meses * 30));

            $string = $meses . ($meses >= 2? ' meses': ' mes');

            if($dias >= 1)
                $string .= ' ' . $dias . ($dias >= 2? ' días': ' día'); 

        }elseif($dias >= 1){
            $dias  = floor($dias);
            $horas = floor($horas - ($dias * 24));

            $string = $dias . ($dias >= 2? ' días': ' día');

            if($horas >= 1)
                $string .= ' ' . $horas . ($horas >= 2? ' horas': ' hora'); 

        }elseif($horas >= 1){
            $horas   = floor($horas);
            $minutos = floor($minutos - ($horas * 60));

            $string = $horas . ($horas >= 2? ' horas': ' hora');

            if($minutos >= 1)
                $string .= ' ' . $minutos . ($minutos >= 2? ' minutos': ' minuto'); 

        }elseif($minutos >= 1){
            $minutos = floor($minutos);

            $string = $minutos . ($minutos >= 2? ' minutos': ' minutos');

        }else{
            $string = 'unos segundos'; 
        }


        return $string;
    }

    public static function unixTimeToString($unixTime, $format = "Y-m-d"){
        return is_numeric($unixTime)? date($format, $unixTime): $unixTime;
    }

    public static function human_filesize($bytes, $decimals = 2)
    {
        $sz = ['bytes', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . $sz[$factor];
    }

    public static function asElapsedTime($value)
    {
        if(!$value)
            return '';

        $horas = 0;
        $minutos = 0;
        $string_time = 0;

        if($value >= 3600){
            $horas = floor($value / 3600);
            $value = $value - ($horas * 3600);

            $string_time = $horas . "h";
        }

        if($value >= 60){
            $minutos = floor($value / 60);
            $value   = $value - ($minutos * 60);

            $string_time = ($string_time? $string_time . ' ': '') . $minutos . "m";
        }

        if(!$horas && !$minutos){
            $string_time = $value . "s";
        }

        return $string_time;
    }

    public static function code128($chaine){
        $code128 = "";

        if(strlen($chaine) > 0){
            $z = 0;
            $i = 1;

            while(($i <= strlen($chaine)) and ($z == 0)){
                if(((ord(substr($chaine, $i -1, 1))) >= 32 and (ord(substr($chaine, $i -1, 1))) <= 126) or ((ord(substr($chaine, $i -1, 1))) == 198)){
                    $i++;

                }else{
                    $i = 0;
                    $z = 1;
                }
            }

            $code128 = "";
            $tableB  = true;

            if($i > 0){
                $i = 1;

                while($i <= strlen($chaine)){
                    if($tableB){
                        $mini = ($i == 1) or (($i +3) == strlen($chaine))? 4: 6;
                        $mini = self::code128TestNum($mini, $chaine, $i);

                        if($mini < 0){
                            $code128 = $i == 1? chr(205): $code128 . chr(204);
                            $tableB  = false;

                        }elseif($i == 1){
                            $code128 = chr(209);
                        }
                    }

                    if(!$tableB){
                        $mini = 2;
                        $mini = self::code128TestNum($mini, $chaine, $i);

                        if($mini < 0){
                            $dummy = self::code128MyVal(substr($chaine, $i -1, 2));
                            $dummy = $dummy < 95? $dummy +32: $dummy +100;

                            $code128 = $code128 . chr($dummy);
                            $i = $i +2;

                        }else{
                            $code128 = $code128 . chr(205);
                            $tableB  = true;
                        }
                    }

                    if($tableB){
                        $code128 = $code128 . substr($chaine, $i -1, 1);
                        $i++;
                    }
                }

                for($i = 1; $i <= strlen($code128); $i++){
                    $dummy = ord(substr($code128, $i -1, 1));
                    $dummy = $dummy < 127? $dummy -32: $dummy -100;

                    if($i == 1)
                        $checksum = $dummy;

                    $checksum = ($checksum + ($i -1) * $dummy) % 103;
                }

                $checksum = $checksum < 95? $checksum +32: $checksum +100;
                $code128  = $code128 . chr($checksum) . chr(206);
            }
        }

        return utf8_encode($code128);
    }

    private static function code128TestNum($mini, $chaine, $i){
        $mini = $mini -1;

        if(($i + $mini) <= strlen($chaine)){
            $y = 0;

            while(($mini >= 0) and ($y == 0)){
                if((ord(substr($chaine, ($i + $mini -1), 1)) < 48) or (ord(substr($chaine, ($i + $mini -1), 1)) > 57)){
                    $y    = 1;
                    $mini = $mini +1;
                }

                $mini = $mini -1;
            }
        }

        return $mini;
    }

    private static function code128MyVal($chaine){
        $j = 1;
        $chaine2 = "";

        while($j <= strlen($chaine)){
            if(is_numeric(substr($chaine, $j -1, 1))){
                $chaine2 .= substr($chaine, $j -1, 1);
                $j++;

            }else{
                break;
            }
        }

        return $chaine2;
    }
}
