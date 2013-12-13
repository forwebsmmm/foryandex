<?
require("../config.php");

$site="trainer/";
$type="content/courses";
if(!$act) $act="select";

require($site_path."cls/auth/session.php");

require($site_path."up.php");

if($act!="select") print "<br><br>
			<div align=right>
				<a href='$PHP_SELF?act=select'>вернуться к списку</a>
			</div>";
else print "<div style='margin:35px 0 15px'>
				<button type='submit' class='btn-green' onclick=\"location.href='$PHP_SELF?act=insert'\">Создать курс</button>
			</div>";


if(($act=="update" || $act == "insert_course") && $id)
{
	clearHandleCourses($id);

	$t=select("select *,
concat('<nobr><span class=status_',v.StatusID,' title=\"',st.Name_$lang;,'\" align=absmiddle></span> ',st.Name_$lang;,'</nobr>') Status
 from ut_materials v join ut_statuses st on st.statusid=v.statusid where v.ID='$id'");

	if($t[StatusID]==1 or $t[StatusID]==7) $table="";
	else $table="_approve";
	
	$q=select("select v.*
	from ut_materials$table v 
	where v.StatusID!=6 and v.UserID='$auth->user;' and v.ID='$id'");
	
	$q[Status]=$t[Status];
	$q[StatusID]=$t[StatusID];


	if(!$q[0]) 
	{
		print "Материал не найден";
	}
	else
	{
	
	?>
	<div id="catalog">
		<div id='materialMessage' style='display:none;'></div>
		<div class='materialBlock clearfix'>
			<div class='left'>
				<div class="image">
					<img src='<?php if($table=="_approve" && file_exists($site_path."images/ut_materials_approve/image/$id.jpg"))
											echo "/images/ut_materials".$table."/image/".$id.".jpg";
									else	echo "/images/course-default.png";
					?>' alt='' width='360'>
				</div>
			</div>
			<div class='right'>
	<?



	print "<div class='head'>".$q['Name']."</div>
			<div class='description'>".htmlspecialchars_decode($q['Description'])."</div>";

	print "<br><li><a href='?act=updateinfo&id=$id'>Изменить информацию</a>";
	$q3=select("select *,concat('<nobr><span class=status_',v.StatusID,' title=\"',st.Name_$lang;,'\" align=absmiddle></span> ',st.Name_$lang;,'</nobr>') Status
				from ut_materials_approve v
				join ut_statuses st on st.statusid=v.statusid
				where ID='$id'");
	if($q3[StatusID]==7 || $q3[StatusID]==1) print "<br>новая версия: $q3[Status]<br>
			<a href=/courses/$id/?preview=1 target=_blank>[предпросмотр]</a>
			<a href=?act=cancelupdate&id=$id&step=1 onclick=\"return confirm('Вы уверены? Изменения будут отменены')\">[отменить]</a><br>";


	print "<br><li><a href='?act=updateprice&id=$id'>Изменить цену</a>";
	
	print " (цена: <b>";
	if($q[Price]) print "$q[Price] руб.";
	else print "бесплатно";
	
	print "</b>)";
	
	$q2=select("select count(*) from ut_members where ID='$id'");
	print "<br><li><a href='/'>Участники:</a> $q2[0]";
	print "<br><li><a href='stats.php?act=selectbyday&material=$id' target='_blank'>Статистика</a><br>";
	
	?>
	<br>
	<button class='btn btn-orange' type='submit' onClick="window.open('/courses/<?=$id?>/?preview=1')">Предпросмотр</button>
	
	
	<br><br>
	
	<?
	print "<div class='description'>Статус: $q[Status]";
	
	$q3=select("select count(*) from ut_modules where MaterialID=$id and StatusID!=6");
	
	if($q[StatusID]==2 && $q[AdminComment]) print "<br>Комментарий: $q[AdminComment]";

	if($q[Active]==1)
	{
		if($q[StatusID]==4)
		{
			if($q[DateActive]>mktime()) print "<br>Появится в каталоге ".date("d.m.Y в H:i",$q[DateActive])." <a href=?act=publicate&id=$id>[изменить]</a>";
			else print "<br>Размещено в каталоге <a href=?act=publicate&id=$id>[отозвать]</a></div>";
		}
		else print " <a href=?act=publicate&id=$id>[отозвать]</a></div>";
		
	}
	elseif($q3[0]>0 || $q[StatusID]!=7)
	{
		print ", готов к публикации";
	?>
	</div>
	<button class='btn-green .matBut' style='margin-top:30px' type='submit' onClick="document.location.href='?publicate=1act=publicate&id=<?=$id?>'">Отправить на модерацию</button>
	<div class='description'>Ваш курс будет проверен в максимально короткие сроки</div>
	<?
	}
	elseif($q3[0]==0)
	{
		print "<br><a href='modules.php?act=insert&id=$id'>Добавьте</a> хотя бы 1 урок";
	}
	
	?>
						</div>
	</div>
	</div>
	<?
	
		print "<br><h1>Уроки</h1>";
		
		$f=new cls_form("content/modules","select");

		$f->draw();
		
		print "<button type=\"submit\" class=\"btn-green\" onclick=\"location.href='modules.php?act=insert&id=$id'\">Добавить урок</button>";
	
	
	
	}
}
else
{
$form->draw();

}



require($site_path."bottom.php");
?>
