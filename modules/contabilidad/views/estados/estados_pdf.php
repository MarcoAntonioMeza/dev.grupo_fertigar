<?php
use yii\helpers\Html;
?>
<body>
    <table style="font-size: 12px;" width="100%">
        <tr>
            <td>
                <?= Html::img('@web/img/logo.png', ["height"=>"150px"]) ?>
            </td>
            <td>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <h2 style="font-weight:bold; font-size:24px; color: #000;">COOPERATIVA TLAHUICA, S.C.L:</h2>
                <p style="color: #000;font-weight:bold; font-size: 21px;">R.F.C TSC-970508-M22.</p>
            </td>
        </tr>
    </table>

    <table style="font-size: 12px; margin-top:20px;" width="100%">

        <tr>
            <td width="100%" align="center">
                <p style="font-size:18px; text-transform:uppercase;" ><strong style="color: #000;">ESTADO DE RESULTADOS LAPSUAL Y ACUMULADO DEL <?=date('d F Y',$balanza_general[0]['balanza'][0]['fecha_min'])?> AL <?=date('d F Y',$balanza_general[0]['balanza'][0]['fecha_max'])?> COMPARADO CON CIFRAS PRESUPUESTALES</strong></p>
            </td>
        </tr>
        <tr>
            <td  width="100%" align="left">
                <p style="font-size:10px;"><strong style="color: #000;">DESCRIPCION:</strong></p>
            </td>
        </tr>
    </table>

    <table width="100%">
        <tr>
            <td >
                <hr  style="font-weight: bold; height: 3px; background-color: black; color: black">
            </td>
        </tr>
    </table>


    <table class="table" width="100%" style=" margin: 0px 10px 0px 0px; border:#000 solid 2px;">
   
        <thead>
                <tr >
                    <th style="text-align: center;"></th>
                    <th style="text-align: center;" colspan = "3" ><?=date('d-F',$balanza_general[0]['balanza'][0]['fecha_min'])?> AL <?=date('d-F',$balanza_general[0]['balanza'][0]['fecha_max'])?></th>
                    <th style="text-align: center;" colspan = "3" >ACUMULADO</th>
                </tr> 
           
        </thead>
        <tbody>                
            <tr >
                <td style="font-weight:bold; text-transform:uppercase; font-size:10px;">-- Cuenta --</td>
                <td style="font-weight:bold; text-transform:uppercase; font-size:10px;">Presupuesto</td>
                <td style="font-weight:bold; text-transform:uppercase; font-size:10px;">Real</td>
                <td style="font-weight:bold; text-transform:uppercase; font-size:10px;">Variacion</td>
                <td style="font-weight:bold; text-transform:uppercase; font-size:10px;">Presupuesto</td>
                <td style="font-weight:bold; text-transform:uppercase; font-size:10px;">Real</td>
                <td style="font-weight:bold; text-transform:uppercase; font-size:10px;">Variacion</td>
            </tr>  

        <?php foreach($balanza_general as $key => $balanza) :?>           

            <tr>
                <td style="font-weight:bold; text-transform:uppercase; font-size:8px;"><?=$balanza['cuenta']->code . ' '.$balanza['cuenta']->nombre?></td>

                <td style="font-weight:bold; text-transform:uppercase; font-size:8px;"><?=round(($balanza['cantidad']),2) ?></td>
                <td style="font-weight:bold; text-transform:uppercase; font-size:8px;"><?=$balanza['balanza_total'] ?></td>
                <td style="font-weight:bold; text-transform:uppercase; font-size:8px;"><?=round(($balanza['cantidad']-$balanza['balanza_total']),2) ?></td>

                <td style="font-weight:bold; text-transform:uppercase; font-size:8px;"><?=$balanza['balanza_total'] ?></td>        
                <td style="font-weight:bold; text-transform:uppercase; font-size:8px;"><?=$balanza['acumulado'] ?></td>
                <td style="font-weight:bold; text-transform:uppercase; font-size:8px;"><?=round(($balanza['cantidad']-$balanza['balanza_total']),2) ?></td>
            </tr>
    <?php endforeach;?>
        </tbody>

    </table>

    <br><br>

</body>


