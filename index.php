<?
require("../config.php");

require($site_path."cls/auth/session.php");

$type = "main/trainer";
$site = "trainer/";


if($auth->user) $isTrainer = isTrainer($auth->user);
else $isTrainer =0;

$menuHide = true;

if($isTrainer && $auth->user)
	System::redirect ("/trainer/courses.php?hello=1");

require ($site_path."up.php");


	list($content) = select("select Message_rus from ut_content where ContentID=4");
?>

	<div class="about-trainers">
		<h1><?=message('aboutTrainer','О тренерах');?></h1>
		<?=htmlspecialchars_decode($content);?>
		<form action="/trainer/index.php?type=main/trainer&act=show_offer" method="POST">
			<input type="hidden" value="1" name="step">
			<input type="hidden" value="main/trainer" name="type">
			<input type="hidden" value="show_offer" name="act">
			<div>
				<input class="checkbox" name="Oferta" type="checkbox" value="1" required> Я соглашаюсь с договором
				<a href="javascript:void(0)" onclick='window.open("/xml/main/content.php?dir=trainer_agreement","_blank","scrollbars=yes,toolbar=no,status=no,resizable=no,width=650,height=600")'>оферты</a>
			</div>
			<button type="submit" class="btn-green"><?=message('doTrainer','Стать тренером');?></button>
		</form>
	</div>

<?php


require($site_path."bottom.php");
?>
