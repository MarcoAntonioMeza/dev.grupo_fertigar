<?php
use yii\helpers\Html;
use yii\helpers\Url;
use Da\QrCode\QrCode;
?>
<?php
$qrCode = (new QrCode( $model->id ))
            ->setSize(250)
            ->setErrorCorrectionLevel('H');

$code = [];
$code['qrBase64'] =  $qrCode->writeDataUri();
?>
<table  width="100%"  style="font-family: “Helvetica Neue”, Arial, sans-serif; font-size: 14px; margin-left: 25px;margin-right:  25px; ">
	<tr>
		<td align="center" style="font-weight: bold;" colspan="3">
			<img  src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" />
		</td>
	</tr>
	<tr>
		<td align="center">
			<strong >FOLIO: #<?= str_pad($model->id,6,"0",STR_PAD_LEFT)  ?> / ENTRADA: <?= date("Y-m-d",$model->created_at) ?></strong>
		</td>
	</tr>
</table>