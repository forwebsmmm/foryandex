<?php
require_once $site_path.'cls/flash/Flash.php';

function listlabelRender($sender)
{
	global $_GLOBALS;
	$vars = ($sender->vars) ? explode(";",$sender->vars) : $_GLOBALS[$sender->default] ;
	if (!$sender->vals)
		echo $vars[$sender->val];
	else
	{
		$vals = explode(";",$sender->vals);
		foreach ($vals as $key => $value)
			if ($value == $sender->val)
				echo $vars[$key];
	}
}

function iconRender($sender,$format)
{
	global $site_path,$img_path;

	$src = set_params($sender->src);
	$value = set_params($sender->text);
	$target = ($sender->target) ? " target='".$sender->target."'" : '' ;

	if($value) {
		echo "<a href='$value' $target>";
	}

	if(file_exists($site_path."images/".$src) || file_exists($src)) {
		echo "<img src='$img_path"."$src' $format class=thumbnail>";
	}
	else if(remote_file_exists($src)) {
		echo "<img  src='$src' alt='' $format class=thumbnail>";
	}
	if($value) echo "</a>";
}

class cls_form extends cls_root
{
	var $mode;
	var $type;
	var $sqlres;
	var $attributes;
	var $templateattributes;
	var $serialize;
	var $template;

	var $name;
	var $title;
	var $banner;
	var $act;

	var $table;
	var $php;
	var $image;

	var $items;

	var $sql;
	var $acterror;

	var $action;
	var $select;
	var $display;

	var $success;
	var $error;
	var $wrong;

	var $privacy;

	var $empty;
	var $style;
	var $showlogo;
	var $formwidth;
	var $short;

	var $redirect;
	var $hint;
	var $vip;

	var $noheader;

	var $pageid;

	var $maxwidth;
	var $maxheight;

	/***********************************************************************
	Инициализация класса
	 * ***********************************************************************/


	function cls_form($type,$tag)
	{
		global $img_path,$message,$searchform,$test,$site,$lang,$gl_file,$site_path,$site,$idt,$step,$auth;
		parent::__construct();
		$this->type=$type;
		$this->name=$tag;

		if(!$type) return false;

		if(!$tag) $tag="select";


		$xml=select("select PageID,Xml,Date from en_pages where Type='$type' and Site='$site'");

		$fname=$site_path."xml/".$site.substr($type,0,strrpos($type,"/"))."/lk".substr($type,strrpos($type,"/")).".xml.$lang";
		if(file_exists($fname)) include_once($fname);

		if((!$xml[0]||$xml[Date]<filemtime($site_path."xml/".$site.$type.".xml"))&&file_exists($site_path."xml/".$site.$type.".xml"))
		{

			$item=$this->xml_contents($site_path."xml/".$site.$type.".xml");

			$q=select("select PageID from en_pages where Type='$type' and Site='$site'");

			runsql("delete from en_pages where Type='$type' and Site='$site'");
			runsql("delete from en_tags where PageID='$q[0]'");

			foreach($item->ownerDocument->nodes as $k)
			{
				if($k->level==2)  $item->Xml2File($type,$k->tagname);
			}

			$xml=select("select PageID,Xml,Date from en_pages where Type='$type' and Site='$site'");
		}

		$this->pageid=$xml[0];

//$this->mode=1;

		if($this->mode==1) $this->template=$this->xml_contents($site_path."xml/templates/main.xml");

		if(!$this->mode)
		{


			$str=$xml[Xml];
//print $str;
			$ar2=explode("|?;",$str);
			$t=$ar2[0];

			for($i=1;$i<count($ar2);$i++)
			{
				if($i%2==0)
				{

					$this->attributes[$t][$k]=$ar2[$i];

				}
				else $k=$ar2[$i];
			}

			$j++;

			if(!$this->name&&$this->attributes['form']['name']) $this->name=$this->attributes['form']['name'];
			if(!$this->name) $this->name="select";

			$xmlpage=$xml[PageID];


			$xml=select("select TagID,Xml from en_tags where Act='$this->name' and PageID='$xmlpage'");

			if(!$xml[0]&&file_exists($site_path."xml/".$site.$type.".xml"))
			{
				$item=$this->xml_contents($site_path."xml/".$site.$type.".xml");
				if ($item->Xml2File($type,$this->name))
					$xml=select("select TagID,Xml from en_tags where Act='$this->name' and PageID='$xmlpage'");
			}

			$str=$xml[Xml];

			$ar=explode("#?;",$str);
			$j=0;


			foreach($ar as $v2)
			{
				$ar2=explode("|?;",$v2);
				$t=$ar2[0];


				for($i=1;$i<count($ar2);$i++)
				{
					if($i%2==0)
					{

						if($t=="field"||$t=="item"||$t=="button") $this->attributes[$t][$j][$k]=$ar2[$i];
						else $this->attributes[$t][$k]=$ar2[$i];

					}
					else $k=$ar2[$i];
				}

				$j++;

			}

			$this->mode=2;


		}
		else
		{

			$item=$this->xml_contents($site_path."xml/".$site.$type.".xml");
			if(!$item) {print "$site_path"."xml/".$site.$type.".xml not found"; return false;}
			$this->mode=1;
		}

		if($this->mode==1)
		{
			$this->table=$item->getAttribute('table','');
			$this->act=$item->getAttribute('act','');

			if ($this->act=='select')
				;//$this->setupDesign('table');

			$this->image=$item->getAttribute('image','');
			$this->php=$item->getAttribute('php','');

			$this->title=set_params($item->getAttribute('title',''));
			$this->privacy=set_params($item->getAttribute('privacy',''));
			$this->showlogo=set_params($item->getAttribute('showlogo',''));
			$this->formwidth=set_params($item->getAttribute('formwidth',''));
			$this->short=set_params($item->getAttribute('short',''));
			$this->redirect=set_params($item->getAttribute('redirect',''));
			$this->hint=set_params($item->getAttribute('hint',''));
			$this->success=set_params($item->getAttribute('success',''));
			$this->vip=set_params($item->getAttribute('vip',''));
//??
			if(!$tag) $this->name=set_params($item->getAttribute('name',''));


			$this->empty=set_params($item->getAttribute('empty',''));


			$this->banner=$item->getAttribute('banner','');


		}
		else
		{

			$ar=$this->attributes['form'];

			$designName=$this->name;
			$designSQL=$this->attributes[$designName]['sql'];
			$designAct= $ar['act'] ? $ar['act'] : $this->attributes[$designName]['act'] ;
			if(substr($designSQL,0,6)=="select"&&!strstr($designName,"search")&&$designAct!="search")
				$designAct="select";
			elseif(!$designAct)
				$designAct=$designName;
			if ( $designAct=='select' )
				;//$this->setupDesign('table');
			$designStyle=$this->attributes[$this->name]['style'];
			if ( $designStyle  )
				$this->setupDesign($designStyle);

			if(is_array($ar)) {
				foreach($ar as $k=>$v)
				{
					if(strstr($v,"\$")) $v=set_params($v);
					if(substr($k,strlen($k)-4,1)=="_")
					{
						if(substr($k,strlen($k)-3)==$lang)
						{
							$k=substr($k,0,strlen($k)-4);
							$this->$k=$v;
						}
					}
					elseif($k!="name"||!$this->name)  $this->$k=$v;
				}
			}
		}
		if(!$this->name) $this->name="select";

//цикл можно убрать-------------------------------

		if($this->mode==1)
		{
			$table = $item->getElementsByTagName($this->name);
			$this->document=$table[0];
			if(!$table[0]) { return false;}

			foreach(get_class_vars(get_class($this)) as $name=>$val)
			{
				if(strlen($v=$table[0]->getAttribute($name,''))) $this->$name=$v;
			}
		}
		else
		{
			$ar=$this->attributes[$this->name];
			if(is_array($ar))
			{
				foreach($ar as $k=>$v)
				{
					//if(strstr($v,"\$")) $v=set_params($v);
					if(substr($k,strlen($k)-4,1)=="_")
					{
						if(substr($k,strlen($k)-3)==$lang)
						{
							$k=substr($k,0,strlen($k)-4);
							$this->$k=$v;
						}
					}
					else $this->$k=$v;
				}
			}
		}

		if(substr($this->sql,0,6)=="select"&&!strstr($this->name,"search")&&$this->act!="search") $this->act="select";
		elseif(!$this->act) $this->act=$this->name;

		$this->hint=nl2br($this->hint);


		if($this->act=="select"&&$this->sql &&!$searchform)
		{
			$this->getRows();
		}


		if($site=="gazeta/") $this->privacy=2;
		if($site=="moderate/") $this->privacy=2;
		if($site=="admin/") $this->privacy=2;
		if(strstr($site,"admin")) $this->privacy=2;

		$this->rowdesign=trim($this->attributes[$this->name]['text']);
	}


	function getTemplates()
	{
		global $site_path;


		$xml=select("select PageID,Xml,Date from en_pages where Type='templates'");
		if(!$xml[0]||$xml[Date]<filemtime($site_path."xml/templates/main.xml"))
		{
			$item=$this->xml_contents($site_path."xml/templates/main.xml");
			$item->Xml2File('templates','');
			$xml=select("select PageID,Xml from en_pages where Type='templates'");
		}

		if($xml[0])
		{

			$str=$xml[1];
//print $str;
//exit;
			$ar=explode("#?;",$str);
			$j=0;

			foreach($ar as $v2)
			{
				$ar2=explode("|?;",$v2);
				$t=$ar2[0];


				for($i=1;$i<count($ar2);$i++)
				{
					if($i%2==0)
					{

						if($t=="field"||$t=="item"||$t=="button") $this->templateattributes[$t][$j][$k]=$ar2[$i];
						else $this->templateattributes[$t][$k]=$ar2[$i];

					}
					else $k=$ar2[$i];
				}

				$j++;

			}
		}
	}

	function getRows()
	{
		global $auth,$step,$serialized;

		$str1 = substr($this->sql,0,strrpos($this->sql," "));

		if (substr($str1,strlen($str1)-5)=="limit")
			$nocalc = 1;

		$this->sql = $this->limitsql($this->sql);
		if (!$nocalc && !$this->nocalc)
			$sql = "select SQL_CALC_FOUND_ROWS ".substr($this->sql,6);
		else
			$sql = $this->sql;

		if (strstr($this->sql,"@n:="))
			runquery("set @n:=$num");

		if ($this->serialize)
		{
			eval($this->set_form_params(substr($this->serialize,1),$i));
			$this->serialized = $serialized;
			$this->numrows = count($this->serialized);
		}
		else
		{
			$this->sqlres = runsql($sql,$this->name);

			$res = mysql_query("select FOUND_ROWS()");
			$r = mysql_fetch_array($res);

			$this->numrows = $r[0];

			if (!$this->numrows)
				$this->numrows=mysql_num_rows($this->sqlres);
			if ($this->numrows==1 && !mysql_num_rows($this->sqlres))
				$this->numrows = 0;
		}
	}

	function getTemplateControl($item)
	{

		$item=new cls_controls($item,$this);
		$item->export=$this->export;


		if($item->type=="template"&&!$this->templateattributes) $this->getTemplates();

		if($item->type=="template"&&($this->template&&$this->mode==1 || $this->templateattributes&&$this->mode==2))
		{


			if($this->mode==1&& !$table = $this->template->getElementsByTagName($item->name)) {print icon("error","Element $item->name not found in the template");exit;}
			elseif($this->mode==2&& !$table[0] = $this->templateattributes[strtolower($item->name)]) {print icon("error","Element $item->name not found in the template");exit;}
			else
			{

				$newitem=new cls_controls($table[0],$this);
				$newitem->export=$this->export;

				if(strlen($item->needed)) $newitem->needed=$item->needed;

				return $newitem;


			}
		}
		else return $item;
	}

	function getTemplateHeader($item)
	{

		$item=new cls_header($item,$this);
		$item->export=$this->export;

		if($item->type=="template"&&!$this->templateattributes) $this->getTemplates();

		if($item->type=="template"&&($this->template&&$this->mode==1 || $this->templateattributes&&$this->mode==2))
		{


			if($this->mode==1&& !$table = $this->template->getElementsByTagName($item->name)) {print icon("error","Element $item->name not found in the template");exit;}
			elseif($this->mode==2&& !$table[0] = $this->templateattributes[strtolower($item->name)]) {print icon("error","Element $item->name not found in the template");exit;}
			else
			{


				$newitem=new cls_header($table[0],$this);
				$newitem->export=$this->export;


				if(strlen($item->needed)) $newitem->needed=$item->needed;
				if(strlen($item->order)&&($item->order!=$item->name || !$newitem->order)) $newitem->order=$item->order;
				if(strlen($item->format)) $newitem->format=$item->format;
				if(strlen($item->colspan)) $newitem->colspan=$item->colspan;

				if(strlen($item->count)) $newitem->count=$item->count;

				return $newitem;
			}
		}
		else return $item;
	}


	function getfields()
	{
		if($this->mode==1)
		{
			$inner = $this->document->getElementsByTagName("fields");

			$fields = $inner[0]->getElementsByTagName("field");
		}
		else
			$fields= $this->attributes['field'];

		return $fields;
	}

	function set_form_params($str,$i)
	{
		global $img_path,$db_params,$id,$site_path,$search,$im_array,$r,$REMOTE_ADDR,$_FILES,$_POST,$_GET,$id,$secpass,$lang,$auth,$er,$cfg;

		$ndate=0;
		$st=$str;

		if(is_Array($_POST)&&is_Array($_GET)) $_POST=array_merge($_POST,$_GET);
		elseif(is_Array($_GET)) $_POST=$_GET;

		$im_count=0;

		if($_POST['numrows']) $mult=1;
		if(!$_POST['Time']) $Time=time();
		if(!$_POST['Day']) $Date=time();
		if(!$_POST['IP']) $IP=$REMOTE_ADDR;

		if($this->mode==1)
		{
			if($this->act=="select")
			{
				$inner = $this->document->getElementsByTagName("header");
				$fields = $inner[0]->getElementsByTagName("item");
			}else{
				$inner = $this->document->getElementsByTagName("fields");
				$fields = $inner[0]->getElementsByTagName("field");
			}
		}else{
			if($this->act=="select")
			{
				$fields =$this->attributes['item'];
			}else{
				$fields =$this->attributes['field'];
			}
		}

		foreach($fields as $field)
		{
			$item=$this->getTemplateControl($field);
			//$name=$field->getAttribute("name","no");
			$name=$item->name;
			if($name=="IP") {$$name=$REMOTE_ADDR;}
			$items[$name]=$item;

			if($item->default&&!$_POST[$name]) $_POST[$name]=$item->default;
			elseif($item->type=="stringlike") $_POST[$name]="%$_POST[$name]%";

			if($_POST[$name]=="%%") $_POST[$name]="%";

			if($mult)
			{
				$f['name']=$_FILES[$name]['name'][$i];
				$f['tmp_name']=$_FILES[$name]['tmp_name'][$i];
				$f['size']=$_FILES[$name]['size'][$i];
				$f['type']=$_FILES[$name]['type'][$i];
			}
			else $f=$_FILES[$name];
			
			$type=$item->type;
			
			if($type == "image_popup" && !$f[name] && $_POST['ImageURL'] ) {
				
				$f['name'] = "img".rand(1,1000).".jpg";
				$f['tmp_name'] = $site_path."tmp/origin_".$f[name];
				$f['uploadedFile'] = true;
				if(!copy($_POST['ImageURL'], $f[tmp_name]))
					$er = "Ошибка загрузки с URL";
			}			

			
			if(($type=="file"||$type=="image" || $type=="image_popup" ||$type=="imageeditor"||$type=="flag")&&$f[name])
			{
				
				$file=fopen($f['tmp_name'],"r");
				if(!$file) $er .= message(filedoesntloaded,"Файл не загружен")."<br>";
				$fname=$f['tmp_name'];
				if($this->mode==1)
				{
					$maxsize=$field->getAttribute("maxsize",'');
					$format=$field->getAttribute("format",'');
				}
				else
				{
					$maxsize=$field['maxsize'];
					$format=$field['format'];
				}

				if(!strstr($format,strtolower(substr($f['name'],strpos($f['name'],".")+1)))&&$format) $er=message(loadfileswithextension,"Загружайте файлы с расширением")." .$format!<br>";

				//${$name}=fread($file,filesize($fname));
				//if($type=="file") ${$name}=addslashes(${$name});

				if(filesize($fname)>0) ${$name}=$fname;
			}

			if(($type=="flag"||$type=="image"||$type=="image_popup"||$type=="imageeditor")&&($f[name]||$_POST['UploadedImage'])
				&&($id||strstr($str,"insert ")))
			{
				$image= new cls_image($f);
				
				$image->maxsize=$field['maxsize'];
				$image->maxwidth=$field['maxwidth'];
				$image->maxheight=$field['maxheight'];
				$image->minwidth=$field['minwidth'];
				$image->minheight=$field['minheight'];

				//[!!!] deprecated
				$image->fixwidth = $field['fixwidth'];
				$image->fixheight = $field['fixheight'];
				$image->fixsizes = $field['fixsizes'];

				$image->mix = $field['mix'];
				$image->mix2 = $field['mix2'];
				$image->quality = ($field['quality']) ? $field['quality'] : $image->quality ;

				if($image->mix2)
				{
					$image2 =new cls_image($image->imageMix($image->mix2));
					$image2->quality = ($field['quality']) ? $field['quality'] : $image2->quality ;
					$image->contents=$image2->contents;
				}

				if($position=$field['position'])
				{
					$image->position=$position;
				}

				if($width=$field['width'])
					$image->newWidth=$width;

				if($height=$field['height'])
					$image->newHeight=$height;

				if($_POST['UploadedImage']==1)
				{
					$file_name=$site_path."images/".$this->table."/".strtolower($name)."/0.jpg";
					$file=fopen($file_name,"r");
					$image->contents=fread($file,filesize($file_name));
					fclose($file);
					$image->type="jpeg";
				}
				else $image->check();
				
				$tmp_contents = $image->contents;
				if($image->contents) ${$name} ="$name";

				$im_array[$im_count]['name']=$item->name;
				$im_array[$im_count]['image']=$image->contents;

				if(($width||$height)&&$image->type=="gif")
				{
					if($this->mode==1) if(!$bgcolor=$field->getAttribute("bgcolor",'')) $bgcolor="ffffff";
					elseif(!$bgcolor=$field['bgcolor']) $bgcolor="ffffff";

					if($width&&($image->width>$width)||($height&&($image->height>$height))) $image->gif2jpeg($bgcolor);
				}

				$Type=$image->type;
				$ph[$name]=1;
				if (!$field['nosmall'])
				{
					$ph['Small'] = 1;
					if ($image->type != "gif")
					{

						$Small = new cls_image($image->imageResize());
						$Small->quality = ($field['quality']) ? $field['quality'] : $Small->quality ;
						$file_name = $site_path."tmp/".$image->name;
						unlink($file_name);
						$Small = $Small->contents;
					}
					else {
						$Small = $tmp_contents;
					}
				}

				if ($_POST['UploadedImage']==1)
				{
					if (!$field['nosmall'])
					{
						$file_name = $site_path."images/".$this->table."/"."small"."/0.jpg";
						$file = fopen($file_name,"r");
						$Small = fread($file,filesize($file_name));
						fclose($file);
					}

					$file_name = $site_path."images/".$this->table."/".strtolower($name)."/0.jpg";
					$file = fopen($file_name,"r");
					$image->contents = fread($file,filesize($file_name));
					fclose($file);
				}
				
				if($type=="image_popup" && $f['uploadedFile'] )
				{
					unlink($f['tmp_name']);
				}

				if (!$field['nosmall'])
					$im_array[$im_count]['small'] = $Small;

				$im_array[$im_count]['type'] = $image->imtype;
				$im_count++;

				$ImageFormat = $image->imtype;
			}
			elseif($type=="date"||$type=="currentdate"||$type=="datetime"||$type=="currentdatetime") {
				if(!$_POST['minute'][$ndate]) $_POST['minute'][$ndate]=0;
				if(!$_POST['hour'][$ndate]) $_POST['hour'][$ndate]=0;

				if ($item->timezone)
				{
					$lasttimezone=date_default_timezone_get();
					date_default_timezone_set($item->timezone);
				}
				$$name=mktime($_POST['hour'][$ndate],$_POST['minute'][$ndate],$_POST['seconds'][$ndate],$_POST['month'][$ndate],$_POST['day'][$ndate],$_POST['year'][$ndate]);
				if(($$name==-1)|| !$_POST['month'][$ndate] ||!$_POST['day'][$ndate] ||!$_POST['year'][$ndate]) unset($$name); $ndate++;
				if ($item->timezone)
					date_default_timezone_set($lasttimezone);
			}
			elseif($type=="sqldate") {
				$$name=($_POST['year'][$ndate]."-".$_POST['month'][$ndate]."-".$_POST['day'][$ndate]);
				if(($$name==-1)|| !$_POST['month'][$ndate] ||!$_POST['day'][$ndate] ||!$_POST['year'][$ndate]) unset($$name);
				$ndate++;
			}
			elseif($type=="multidate")
			{
				$$name=implode(';',$_POST[$name]);
			}
			elseif ($type=="multilist")
			{
				$$name = implode('|',$_POST[$name]);
			}
			elseif ($type=="taglist")
			{
				$$name = implode('|',$_POST[$name]);
			}
			elseif ($type=="sqlmultilist")
			{
				$$name = implode('|',$_POST[$name]);
			}
			elseif ($type=="sqltaglist")
			{
				$$name = implode('|',$_POST[$name]);
			}
			if(($type=="url"||$name=="url")&&$$name&&!strstr($$name,"http://")) $$name="http://".$$name;
		}

		$st=$str;
		while($q=strpos($str,"@"))
		{
			//отсекаем до ;
			$pos=strpos($str,";",1+$q);
			if($pos&&(!($pos2=strpos($str,"=",1+$q))||$pos<$pos2)&&(!($pos2=strpos($str,",",1+$q))||$pos<$pos2)&&(!($pos2=strpos($str," ",1+$q))||$pos<$pos2))
			{
				$par_name=substr($str,1+$q,$pos-$q-1);
				$str=substr($str,1+$q);

				$item=$items[$par_name];
				if($item->unique&&$mult)
				{
					foreach($_POST[$par_name] as $val)
					{
						if($ar[$val]) $er.=message(valueof,"Значение")." $item->caption=$val ".message(notunique,"не уникально")."!<br>";
						else $ar[$val]=1;
					}
				}
				//elseif(is_Array($_POST[$par_name])) $$par_name=$_POST[$par_name][$i];

				if(!$$par_name&&is_Array($_POST[$par_name])){
					$par_val=$_POST[$par_name][$i];
				}
				elseif(!$$par_name){
					$par_val=$_POST[$par_name];
				}
				else {
					$par_val=$$par_name;
				}

				//уже закаченные картинки не требуем повторно
				if($item->type=='image'&&!$par_val&&$item->needed && file_exists($site_path."images/".$this->table."/small/".$id.".jpg")) $item->needed=0;

				$error=$er;

				if($item->type=="email") $item->preg="/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,4})$/u";

				//для нерусскоязычных пользователей убераем из реглярки русские символы
				if ((isset($item->preg))&&($cfg['GLOBAL']['default_language'] != "rus")){
					$srch="/(([а-яА-ЯёЁ]+)\-?[а-яА-ЯёЁ]+?)/u";
					$item->preg = preg_replace($srch,"",$item->preg);
				}
				if($item->preg&&$par_val&&!preg_match( $item->preg, $par_val )) $er.=message(wrongformat,"Неправильный формат")." $item->caption<br>";
				if(strlen($par_val)==0&&$item->needed) $er.=message(umastenter,"Вы должны ввести")." $item->caption<br>";

				if($item->maxlines && count(explode("\n",$par_val))>$item->maxlines) $er.=message(linescount,"Число строк поля")." $item->caption ".message(shouldbyless,"не должно превышать")." $item->maxlines<br>";
				if($item->maxwordlength && !$item->maxlength) $item->maxlength=6000;

				if($par_val&&$item->maxlength&&(mb_strlen($par_val,'UTF-8')>$item->maxlength)) $er.=message(lengthofffield,"Длина поля")." $item->caption ".message(lessthen,"не должна превышать")." $item->maxlength<br>";
				if($par_val&&$item->minlength&&(mb_strlen($par_val,'UTF-8')<$item->minlength)) $er.=message(lengthofffield,"Длина поля")." $item->caption ".message(notlessthen,"не должна быть меньше")." $item->minlength<br>";

				if(strlen($par_val)&&strlen($item->max)&&($par_val>$item->max)) $er= message(valueof,"Значение")." $item->caption ".message(notmorethen,"не должно превышать")." $item->max<br>";
				if(strlen($par_val)&&strlen($item->min)&&($par_val<$item->min)) $er= message(valueof,"Значение")." $item->caption ".message(notless,"не должно быть ниже")." $item->min<br>";

				if(!$er && $par_val&&$item->maxwordlength && GetWordMaxLength(PrettyURL($par_val,$item->maxwordlength))>$item->maxwordlength) $er.=message(maxlengthofword,"Максимальная длина слова в поле")." $item->caption ".message(lessthen,"не должна превышать")." $item->maxwordlength<br>";

				if($er!=$error) {$this->wrong[$w]=$par_name; $w++;}

				if($par_name)
				{
					if($item->type!="flag"&&$item->type!="image"&&$item->type!="imageeditor"&&$par_name!="Small"&&$item->type!="file")
					{
						$par_val=str_replace("<", "&lt;", $par_val);
						$par_val=str_replace(">", "&gt;", $par_val);

						//if($item->type=="numeric") $par_val=intval($par_val);
						if($item->type=="text"||$item->type=="editor"||$item->type=="string") $search.=strip_tags($par_val)." ";
						if($item->type=="text"||$item->type=="editor")
						{
							if($item->type=="text")
							{
								$par_val=str_replace("\r\n","<br />",$par_val);
								if($item->type=="text") $par_val=mysql_real_escape_string($par_val);
							}
							$par_val=stripslashes($par_val);
						}else{
							$par_val=mysql_real_escape_string($par_val);
							$par_val=stripslashes($par_val);
						}
						$par_val=stripslashes($par_val);
					}
					$par_val=addslashes($par_val);

					$sql="SET @"."$par_name='$par_val'";
					$db_params[]=$par_name;
					runquery($sql);
				}
				$par_val=stripslashes($par_val);
				if($item->unique&&$par_val&&!$mult)
				{
					$par_val=addslashes($par_val);
					$sql="select * from $this->table where $par_name='$par_val' and $item->unique";

					if($this->select)
					{
						$sq.=" and ".str_replace("=","<>",substr($this->select,strpos($this->select,"where ")+6));

						while(strstr($sq,"."))
						{
							$sq=substr($sq,0,strpos($sq,".")-1).substr($sq,1+strpos($sq,"."));
						}
						$sql.=$sq;
					}
					$res=runsql($sql,$this->name);
					if(mysql_num_rows($res)) $er.=message(existintable,"В таблице уже есть ")." $item->caption=$par_val<br>";
				}
			}
			else $str=substr($str,1+$q);
		}

		$str=$st;

		while($q=strpos($str,"$"))
		{
			$par_name=substr($str,1+$q,strpos($str,";",1+$q)-$q-1);

			if(strstr($par_name,"->"))
			{
				$ob=substr($par_name,0,strpos($par_name,"->"));
				$var=substr($par_name,2+strpos($par_name,"->"));

				if(strstr($var,"[")) $vname=$ob.substr($var,0,strpos($var,"[")).substr($var,1+strpos($var,"["),strpos($var,"]")-strpos($var,"[")-1);
				else $vname=$ob.$var;

				$st=str_replace($par_name,$vname,$st);
				$str=str_replace($par_name,$vname,$str);
				$par_name=$vname;

				if(!$$par_name)
				{
					if(!strstr($var,"[")) $$par_name=${$ob}->$var;
					else
					{
						$var1=substr($var,0,strpos($var,"["));
						$var=substr($var,1+strpos($var,"["));
						$var=substr($var,0,strlen($var)-1);
						$v=${$ob}->$var1;
						$$par_name=$v[$var];
					}
				}
			}
			elseif(strstr($par_name,"["))
			{
				$ob=substr($par_name,0,strpos($par_name,"["));
				$var=substr($par_name,1+strpos($par_name,"["));
				$var=substr($var,0,strlen($var)-1);

				$st=str_replace($par_name,$var,$st);
				$str=str_replace($par_name,$var,$str);

				$par_name=$var;
				if(!$$par_name) $$par_name=${$ob}[$var];
				//локализация
				if($ob=="message"&&!$$par_name){
					//сколько параметров у фукции?
					if (($fid = strpos($var,","))!==false){             //1
						$hint = substr($var,0,$fid);
						$text_message = substr($var,$fid+1);
						$$par_name = message($hint,$text_message);
					}else{                                              //2
						$$par_name = message($var);
					}
					$$par_name =addslashes($$par_name);
				}
			}else{
				if($r[$par_name]) $$par_name=$r[$par_name];
				//elseif(is_Array($_POST[$par_name])) $$par_name=$_POST[$par_name][$i];
				elseif($_POST[$par_name]) $$par_name=$_POST[$par_name];

				if(!$$par_name&&!strstr($par_name,">"))
				{
					$p=select("select @$par_name");
					if($p[0]) $$par_name=$p[0];
				}

				//print "$i:".$par_name.is_Array($$par_name).$parval[$i].")";
				if(is_Array($$par_name))
				{
					$parval=$$par_name;
					$$par_name=$parval[$i];
				}

				$str=substr($str,1+$q);
				$$par_name=str_replace(";","#dot",$$par_name);

				$st=  str_replace("\$".$par_name.";",$$par_name,$st);
				$str= str_replace("\$".$par_name.";",$$par_name,$str);
			}
		}

		$str=$st;
		$w=0;
		while(strlen($q=strpos($str,"^")))
		{
			$par_name=substr($str,1+$q,strpos($str,";",1+$q)-$q-1);
			if(strstr($par_name,"("))
			{
				$result= myeval($par_name.";");
				if($result) $st= str_replace("^".$par_name.";",$result,$st);
			}
			$str=substr($str,1+$q);
		}

		return $st;
	}


	function runsql($noprint=false)
	{
		global $img_path,$search,$auth,$idn,$id,$site_path,$im_array,$test,$i,$er,$lang,$_POST,$insertid,$r;

		if (!$this->checkPermission(1))
			return 0;

		//[!!!]
		if ($auth && $auth->user && $auth->getBanned())
			return 0;

		if ($this->act!="select")
			$sql = $this->sql;
		else
			$sql = $this->action;

		if ($sql)
		{
			if (!$numrows=$_POST['numrows'])
				$numrows = 1;

			$tmpsql = $sql;

			for ($i=0;$i<$numrows;$i++)
			{
				$sql = $tmpsql;

				$sql = str_replace("&lt;","<",$sql);
				$sql = str_replace("&gt;",">",$sql);
				$sqlar = explode("#",$sql);
				foreach ($sqlar as $sql)
				{
					$j++;

					$l = substr($sql,0,1);
					if ($l=="^")
					{
						eval($this->set_form_params(substr($sql,1),$i));
						if (!$er)
							$str = set_params($this->success);
					}else{
						if(((($this->mode==2)&&($this->attributes['field']||$this->attributes['item']))||
							(($this->mode==1)&&($this->document->getElementsByTagName("fields")||$this->document->getElementsByTagName("header")))
						)&&$this->name!="delete"&&$this->name!="create"&&$this->name!="drop"){
							$sql=$this->set_form_params($sql,$i);
						}else{
							$sql=set_params($sql);
						}
						if($er)
						{
							break;
						}else{
							if (strstr($sql,"insert"))
								$c=1;

							$sqlstr = str_replace(";","",$sql);
							$sqlstr = str_replace("#dot",";",$sqlstr);
							if ($res=runquery($sqlstr))
								$str = set_params($this->success);
							if (substr($sql,0,6)=="select")
								$r = mysql_fetch_array($res);


							if(!$er)
							{
								if($im_array)
								{
									$name=$this->table;
									if(!$name) $name="unfiled";

									$q=select("select @insertid");

									if($q[0]) $idn=$q[0];
									else $idn=$id;

									foreach($im_array as $v)
									{
										if($name && $v['image'] && $idn)
										{
											$path=$site_path."images/".$name."/".strtolower($v['name'])."/";
											System::forceDirectory($path);
											$file=fopen($path.$idn.".jpg","w");
											fputs($file,$v['image']);
											fclose($file);
										}

										if($v['small'] && $name && $idn)
										{
											$path=$site_path."images/".$name."/small/";
											System::forceDirectory($path);
											$file=fopen($path.$idn.".jpg","w");
											fputs($file,$v['small']);
											fclose($file);
										}
									}
									//иначе файл не записывается - не получает $idn при insert после других операций
									if($idn) unset($im_array);
								}
							}
						}
					}

					$r1=select("select @error");
					if($r1[0])
					{
						$er.=$r1[0];
						break;
					}
				}
				if($er) break;

			}

			if ($er)
				$retstr=icon('error',"<font color=red>$er</font>");
			elseif ($str)
				$retstr=icon('ok',"$str");
			else
				$retstr="";

			if (!$noprint)
				print $retstr;
			else
				return $retstr;
		}
	}

	function drawHeaderHeader($numrows)
	{
		switch ($this->export)
		{
			case 'csv':
				$this->drawHeaderHeaderCSV($numrows);
				break;
			case 'html':
			case '1':
			case '':
				$this->drawHeaderHeaderHTML($numrows);
				break;
		}
	}

	function drawHeaderHeaderCSV($numrows)
	{

	}

	function drawHeaderHeaderHTML($numrows)
	{
		print "<tr   ";
		if($this->hcolor)
			print "bgcolor=".$this->hcolor."  ";
		print "  align=\"center\" ";
		if($this->headerclass)
			print "class=\"".$this->headerclass."\"";

		print " >\n";
		print "<input type=\"hidden\" name=\"numrows\" id=\"numrows\"  value=\"$numrows\">";
	}

	function drawHeaderFooter()
	{
		switch ($this->export)
		{
			case 'csv':
				$this->drawHeaderFooterCSV();
				break;
			case 'html':
			case '1':
			case '':
				$this->drawHeaderFooterHTML();
				break;
		}
	}

	function drawHeaderFooterCSV()
	{
		print "\n";
	}

	function drawHeaderFooterHTML()
	{
		print "</tr>\n";
	}

	function DrawHeader()
	{
		global $id,$PHP_SELF,$where,$firstpage,$QUERY_STRING;
		if($this->act=="select"&&$this->numrows)
		{
			$cnum=0;
			if($this->numrows>$this->pagecount||!strlen($this->numrows))
				$numrows=$this->pagecount;
			else
				$numrows=$this->numrows;

			$this->drawHeaderHeader($numrows);

			$items=$this->attributes['item'];

			foreach($items as $item)
			{
				$this->items[$cnum]=$this->getTemplateHeader($item);
				$visible = set_params($this->items[$cnum]->visibility) != 'hidden';
				if($this->items[$cnum]->type!="hidden"&&!($colspan>1)&&$visible) $this->items[$cnum]->Draw();
				if($colspan) $colspan--;
				if($this->items[$cnum]->colspan>1) $colspan=$this->items[$cnum]->colspan;
				$cnum++;
			}
			$this->drawHeaderFooter();
		}
		return $cnum;
	}

	function limitsql($sql)
	{
		global $page,$sort;

		$vsort = $sort;

		foreach ($this->attributes['item'] as $item)
		{
			$name = $item['name'];
			$order = explode(';',$item['order']);
			if (count($order)==2)
			{
				if ($sort==$name || $sort==$name.' asc')
					$vsort = $order[0];
				if ($sort==$name.' desc')
					$vsort = $order[1];
			}
		}


		if(!$page) $pag=1;
		else $pag=$page;

		if($vsort)
		{
			if(!strstr($sql,"order by")) $sql.=" order by $vsort";
			else $sql=substr($sql,0,strpos($sql,"order by"))." order by $vsort";
		}

		$num=(($pag-1)*$this->pagecount);

		if($num<0) $num=0;

		if( $this->pagecount && (!strstr($sql,"limit") || $this->pager)) $sql.=" limit $num,$this->pagecount";


		return $sql;
	}

	function drawDots($value)
	{
		switch ($this->export)
		{
			case 'csv':
				$this->drawDotsCSV($value);
				break;
			case 'html':
			case '1':
			case '':
				$this->drawDotsHTML($value);
				break;
		}
	}

	function drawDotsCSV($value)
	{
		echo str_replace('.',',',$value);
	}

	function drawDotsHTML($value)
	{
		echo dots($value);
	}

	function BrowseSerialized()
	{
		global $rowcolor,$number,$_POST,$site,$page,$num,$sort,$st,$lang,$cnum,$r,$noedit;

		if (!count($this->serialized))
			print "<tr><td bgcolor=ffffff>".$this->empty."</td></tr>";

		foreach ($this->serialized as $r)
		{
			$numrow++;

			$_POST['numrow'] = $numrow;

			if ($color!=$this->rowcolor)
				$color = $this->rowcolor;
			elseif ($this->mixcolor)
				$color = $this->mixcolor;

			$rowcolor = $color;

			print "<tr bgcolor=".$color." ";

			if ($this->style)
				print set_params($this->style);

			print ">";

			foreach ($this->items as $item)
			{
				$item->DrawRow($r,$numrow);

				if ($item->count)
				{
					$count[$item->name] += $item->value;
					if ($item->value!=0)
						$c[$item->name]++;
					$max[$item->name] = (array_key_exists($item->name,$max)) ? max($item->value,$max[$item->name]) : $item->value ;
					$min[$item->name] = (array_key_exists($item->name,$min)) ? min($item->value,$min[$item->name]) : $item->value ;
				}
				elseif (!$numcols)
					$cols++;
			}
			print "</tr>";
		}

		if (count($count))
		{
			print "<tr class=header>";

			foreach ($this->items as $item)
			{
				$visible = set_params($item->visibility) != 'hidden';
				if ($item->type!='hidden'&&$visible)
				{
					print "<td>";

					if ($item->align=="center")
						print "<center>";
					elseif ($item->align)
						print "<div align=\"$item->align\">";

					if ($item->count=="avg"&&$c[$item->name]!=0)
					{
						$num = ($count[$item->name]/$c[$item->name]);
						if ($num>1000)
							$round = 0;
						else
							$round = 1;
						print dots(round($num,$round));
					}
					elseif ($item->count=="sum")
						print dots(($count[$item->name]));
					elseif ($item->count=="cnt")
						print $c[$item->name];
					elseif ($item->count=="max")
						print dots($max[$item->name]);
					elseif ($item->count=="min")
						print dots($min[$item->name]);

					print "</td>";
				}
			}
			print "</tr>";
		}
		$this->Pages($cnum+2);
	}

	function drawBrowseHeader($color)
	{
		switch ($this->export)
		{
			case 'csv':
				$this->drawBrowseHeaderCSV($color);
				break;
			case 'html':
			case '1':
			case '':
				$this->drawBrowseHeaderHTML($color);
				break;
		}
	}

	function drawBrowseHeaderCSV($color)
	{

	}

	function drawBrowseHeaderHTML($color)
	{
		print "<tr ";

		if($color) print " bgcolor=".$color." ";

		if($this->style) print set_params($this->style);

		print ">";
	}

	function drawBrowseFooter()
	{
		switch ($this->export)
		{
			case 'csv':
				$this->drawBrowseFooterCSV();
				break;
			case 'html':
			case '1':
			case '':
				$this->drawBrowseFooterHTML();
				break;
		}
	}

	function drawBrowseFooterCSV()
	{
		print "\n";
	}

	function drawBrowseFooterHTML()
	{
		print "</tr>";
	}

	function drawBrowseSummaryHeader()
	{
		switch ($this->export)
		{
			case 'csv':
				$this->drawBrowseSummaryHeaderCSV();
				break;
			case 'html':
			case '1':
			case '':
				$this->drawBrowseSummaryHeaderHTML();
				break;
		}
	}

	function drawBrowseSummaryHeaderCSV()
	{

	}

	function drawBrowseSummaryHeaderHTML()
	{
		print "<tr class=header>";
	}

	function drawBrowseSummaryFooter()
	{
		switch ($this->export)
		{
			case 'csv':
				$this->drawBrowseSummaryFooterCSV();
				break;
			case 'html':
			case '1':
			case '':
				$this->drawBrowseSummaryFooterHTML();
				break;
		}
	}

	function drawBrowseSummaryFooterCSV()
	{
		print "\n";
	}

	function drawBrowseSummaryFooterHTML()
	{
		print "</tr>";
	}

	function drawBrowseSummaryFieldHeader($item)
	{
		switch ($this->export)
		{
			case 'csv':
				$this->drawBrowseSummaryFieldHeaderCSV($item);
				break;
			case 'html':
			case '1':
			case '':
				$this->drawBrowseSummaryFieldHeaderHTML($item);
				break;
		}
	}

	function drawBrowseSummaryFieldHeaderCSV($item)
	{

	}

	function drawBrowseSummaryFieldHeaderHTML($item)
	{
		print "<td>";

		if($item->align=="center") print "<center>";
		elseif($item->align) print "<div align=\"$item->align\">";
	}

	function drawBrowseSummaryFieldFooter()
	{
		switch ($this->export)
		{
			case 'csv':
				$this->drawBrowseSummaryFieldFooterCSV();
				break;
			case 'html':
			case '1':
			case '':
				$this->drawBrowseSummaryFieldFooterHTML();
				break;
		}
	}

	function drawBrowseSummaryFieldFooterCSV()
	{
		print ";";
	}

	function drawBrowseSummaryFieldFooterHTML()
	{
		print "</td>";
	}

	function Browse()
	{
		global $rowcolor,$number,$_POST,$site,$page,$num,$sort,$st,$lang,$cnum,$r,$noedit;

		$number=0;
		$res=$this->sqlres;
		if($page>1) $number=($page-1)*$this->pagecount;

		if(!mysql_num_rows($res)) print "<tr><td bgcolor=#ffffff>".$this->empty."</td></tr>";

		if(!$this->numrows) $this->numrows=mysql_num_rows($res);

		while($r=mysql_fetch_array($res))
		{
			$numrow++;
			$_POST['numrow']=$numrow;

			if(!$this->rowdesign)
			{
				if($color!=$this->rowcolor) $color=$this->rowcolor;
				elseif($this->mixcolor)  $color=$this->mixcolor;

				$rowcolor=$color;
				$this->drawBrowseHeader($color);
				foreach($this->items as $item)
				{
					$item->DrawRow($r,$numrow);
					if($item->count) {
						$count[$item->name]+=$item->value;
						if ($item->value!=0) $c[$item->name]++;
						$max[$item->name] = (array_key_exists($item->name,$max)) ? max($item->value,$max[$item->name]) : $item->value ;
						$min[$item->name] = (array_key_exists($item->name,$min)) ? min($item->value,$min[$item->name]) : $item->value ;
					}
					elseif(!$numcols) $cols++;
				}
				$this->drawBrowseFooter();
			}
			else
			{
				print set_params($this->rowdesign);
			}
		}

		if(count($count))
		{
			$this->drawBrowseSummaryHeader();
			foreach($this->items as $item)
			{
				$this->drawBrowseSummaryFieldHeader($item);
				if($item->count=="avg"&&$c[$item->name]!=0) {
					$num=($count[$item->name]/$c[$item->name]);
					if($num>1000) $round=0;
					else $round=1;
					print $this->drawDots(round($num,$round));
				}
				elseif($item->count=="sum") print $this->drawDots(($count[$item->name]));
				elseif($item->count=="cnt") print $c[$item->name];
				elseif($item->count=="max") print dots($max[$item->name]);
				elseif($item->count=="min") print dots($min[$item->name]);
				$this->drawBrowseSummaryFieldFooter();
			}
			$this->drawBrowseSummaryFooter();
		}
		$this->Pages($cnum+2);
	}


	function checkPermission($step)
	{
		global $project_name,$auth,$site_url,$site;

		if (($project_name=="butsa")&&$this->privacy==1&&!$auth->team)
		{
			echo icon("error",message(loozerselect,"Опция недоступна до тех пор, пока у Вас нет команды.")."<br><a href=\"$site_url"."xml/office/free.php\">".message(sendrequestonteam,"Подать заявку на получение команды.")."</a></center>");
			return false;
		}

		if ($this->privacy==2)
		{
			$r = select("select PermissionID from en_permissions where
			(Site='$site' or Site='".substr($site,0,strlen($site)-1)."' or Type='**') and
			(Type='$this->type' or Type='*' or Type='**') and (Act='' or Act='$this->name')
			and (UserID='$auth->user' or TypeID in
			(select p.TypeID from ut_posts p where p.TypeID=TypeID and p.UserID='$auth->user'))");

			if(!$r[0] || !$auth->user)
			{
				echo icon("error",message(uhaventpermision,"У Вас нет прав для просмотра этой страницы"));
				return false;
			}
		}

		if ($site=='guild/' && !$auth->roleGuildOwner)
		{
			$perm_q = select("select Type from gd_permissions where GuildID='$auth->guild' and UserID='$auth->user' and Type='$this->type'");
			if(!$perm_q[0] || !$auth->user || !$auth->guild)
			{
				echo icon("error",message(NoAccessRightsSection,"У вас нет прав доступа к этому разделу"));
				return false;
			}
		}

		return true;
	}

	function Draw()
	{
		global $form,$site_path,$step,$act,$oldact,$serialized,$gl_file,$cnum,$act,$auth,$lang,$id,$r,$type,$num,$cnum,$site,$form_ok;

		if ($this->export=='csv')
		{
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-cache, must-revalidate");
			header("Cache-Control: post-check=0,pre-check=0", false);
			header("Cache-Control: max-age=0", false);
			header("Pragma: no-cache");

			header("Content-type: application/vnd.ms-excel; charset=UTF-8" );
			header("Content-Disposition: attachment; filename=\"data.csv\"");
			echo chr(239).chr(187).chr(191);
		}

		if ($this->mode==1&&!$this->document)
		{
			print "$type $act, XML error";
			return 0;
		}
		elseif ($this->mode==2&&!$this->attributes)
		{
			print "$type $act, Attributes error";
			return 0;
		}

		if (strstr($act,"search")&&$act!=$this->name)
			$step = 1;

		if (!$this->checkPermission($step))
			return 0;

		if ($this->act=="select"&&!$this->numrows)
		{
			if ($this->empty)
				print icon('green',$this->empty);
			return 0;
		}

		if ($this->select)
		{
			$l = substr($this->select,0,1);
			if ($l=="^")
				eval($this->set_form_params(substr($this->select,1),$i));
			else
				$r = select(settags($this->select));
		}
		elseif ($this->serialize&&$this->act!="select")
		{
			eval($this->set_form_params(substr($this->serialize,1),$i));
			$this->serialized = $serialized;
			$r = $this->serialized;
		}

		if (!is_array($r)&&$this->error)
		{
			if (!$form_ok)
				print icon('error',$this->error);

			if (($oldact!=$act)&&$step&&$oldact)
			{
				print "<br>";
				$form = new cls_form($type,$oldact);
				$act = $oldact;
				$form->draw();
			}
			return 0;
		}

		if (!$this->rowdesign)
			$this->Header();
		if (!$this->rowdesign)
			$cnum = $this->DrawHeader();

		if (!$cnum)
			$cnum = 2;

		$this->DrawFields();
		$this->DrawButton($cnum);

		if (!$this->rowdesign)
			$this->Footer();

		unset($num);
	}


	function DrawFields()
	{
		global $cnum,$r,$step,$er,$auth;


		if($this->act!="select")
		{
			if(strlen($this->attributes['fields']['text'])) print settags($this->attributes['fields']['text']);
			$fields =$this->attributes['field'];

			if(!$this->rowdesign && is_array($fields))
			{
				foreach($fields as $field)
				{
					$visible = set_params($control->visibility) != 'hidden';

					if($control&&$control->type!="hidden"&&$visible&&$color!=$this->rowcolor||!$control) $color=$this->rowcolor;
					elseif($control&&$control->type!="hidden"&&$visible&&$this->mixcolor)  $color=$this->mixcolor;

					$control=$this->getTemplateControl($field);
					$control->rowcolor=$color;

					$control->Draw(1,'');
				}

			}
			else
			{
				print set_params($this->rowdesign);
			}


		}
		elseif($this->serialize) $this->BrowseSerialized();
		elseif($this->act=="select") $this->Browse();

		//tac();
	}

	function DrawButton($cnum)
	{
		global $cfg,$lang,$firstpage;


		$buttons=$this->attributes['button'];


		if($buttons&&($this->act!="select"||$this->numrows>0))
		{

			print "<input type=\"hidden\" name=\"oldact\" value=\"$this->name\"/>\n";


			print "<tr ";
			if($this->hcolor) print "bgcolor=".$this->hcolor;

			if($this->headerclass) print " class=\"".$this->headerclass."\"";

			print " ><td colspan=$cnum>\n";

			$type="submit";

			foreach($buttons as $button)
			{
				$visible = set_params($button['visibility']) != 'hidden';
				if (!$visible)
					continue;
				$onClick = '';

				print $button['text'];

				if($type=="submit" && $newact=($button['act'])) print "<input type=hidden name=act value='$newact'>\n";

				$n="name_".$lang;
				$name=$button[$n];

				if($typ=$button['type']) $type=$typ;


				$format=set_params($button['format']);

				if($align=$button['align']) print "<div align='$align'>\n";


				if(!$type) $type="button";

				if($type=="submit")
					// $format.= ($cfg['GLOBAL']['root']['ui_button']) ? " onclick=\"this.disabled = true; this.form.submit();\" " : " onclick=\"this.disabled = true; this.form.submit();\" ";

				if($type=="return")
				{
					$type="button";
					if($button['redirect'])
						$format.=" onclick=\"location='".set_params($button['redirect'])."'\" ";
					else
						$format.=" onclick=\"location='$firstpage'\" ";
				}

				if($type=="button" && !$format)
					$format=" onclick=\"location='$PHP_SELF?type=".$this->type."&act=".$button['act']."&step=1'\"";

				$id = "btn".rand();
				$style = ($cfg['GLOBAL']['root']['ui_button']) ? " style=\"position: absolute; left: -1000px; top: -1000px;\" " : '' ;

				if ($cfg['GLOBAL']['root']['ui_button'])
					drawButton($name,"if(this.className=='ui-button-disabled') return; this.className='ui-button-disabled'; $('#$id').click();");

				print "<input type=\"$type\" $style id=\"$id\" value=\"$name\"";
				if($this->buttonclass) print " class=\"".$this->buttonclass."\"";
				print " $format/> ";

				unset($type);
			}


			print "</td></tr>\n";
		}

	}

	function DrawImage()
	{
		global $er,$form_ok;

		//if(!$this->document) return 0;

		if(!$er&&!$form_ok&&$this->image) print "<center><img src=\"$this->image\"></center><hr>\n";
	}

}


class cls_controls
{
	var $name;
	var $table;
	var $caption;
	var $type;
	var $rowcolor;

	var $needed;
	var $unique;
	var $showlimit;

	var $maxlength;
	var $minlength;
	var $max;
	var $min;

	var $maxyear;
	var $minyear;

	var $sql;
	var $action;

	var $val;
	var $text;
	var $button;
	var $default;

	var $format;

	var $preg;

	var $cols=40;
	var $rows=3;

	var $size=20;
	var $vars;
	var $vals;

	var $colspan;
	var $align;
	var $valign='';
	var $icon;

	var $tdstyle;
	var $nobr;

	static private $renders = Array();

	static public function addRender($type,$render)
	{
		self::$renders[$type] = $render;
	}

	function cls_controls($field,$table)
	{
		global $lang;

		$this->table=$table;

		foreach($field as $k=>$v)
		{
			//if(strstr($v,"\$")) $v=set_params($v);

			if(substr($k,strlen($k)-4,1)=="_")
			{

				if(substr($k,strlen($k)-3)==$lang)
				{
					$k=substr($k,0,strlen($k)-4);
					$this->$k=$v;
				}
			}
			else $this->$k=$v;
		}

		$this->rowcolor=$table->rowcolor;

		if(strstr($this->type,"\$")) $this->type=set_params($this->type);

		if(!$this->caption) $this->caption=$this->name;

		if($field['name']) $this->name=$field['name'];
		elseif(!$this->name) $this->name=$field['name_eng'];
		$this->text=$field['text'];
	}

	function Draw($drawTable,$form)
	{
		global $img_path,$auth,$er,$gladtypenames,$path,$site_path,$site_url,$_GET,$img_url,$ndate,$r,$id,$_POST,$db,$gd,$lang,$_GLOBALS,$dbname,$day,$month,$year;
		global $cfg,$months,$months_short,$weekdays,$weekdays_short;

		$required = '';
		if($this->needed) $required=" required ";

		if(count($_GET)>count($_POST)) $_POST=$_GET;

		if(!$form) $form=$this->table;
		else $this->rowcolor=$form->rowcolor;

		if(!$ndate) $ndate=0;


		if(strstr($this->text,"\$")) $this->text=set_params($this->text);

		if(strlen($this->text)) $this->val=$this->text;

		if(strlen($r[$this->name])) $this->val=$r[$this->name];

		if($_GLOBALS[$this->name]&&!$this->val) $this->val=$_GLOBALS[$this->name];

		//в случае ошибки формы подставляем старые данные
		if(strlen($_POST[$this->name])&&$this->name!="act"&&(!$this->val || ($r[$this->name] && $er))) $this->val=$_POST[$this->name];


		if($this->sql&&($this->type!="sqlist")&&($this->type!="sqllabel")&&($this->type!="sqlradio")&&($this->type!="sqlmultilist")&&($this->type!="sqltaglist"))
		{

			$res=runsql($this->sql);
			$r1=mysql_fetch_array($res);

			$this->val=$r1[0];
			$_POST[$this->name]=$this->val;
		}

		if($this->type=="sqldatelabel"&&$this->val=="0000-00-00") $this->val="";
		if($this->type=="urllabel"&&$this->val=="http://") $this->val="";

		if(!$this->val&&($this->table->short&&!$this->colspan)&&$this->type!="list"&&$this->type!="sqlist"&&$this->type!="multilist"&&$this->type!="taglist"&&$this->type!="sqlmultilist"&&$this->type!="sqltaglist" || $this->hidden) $this->type="hidden";


		if($this->name!="Xml"&&(strstr($this->val,"&lt")||strstr($this->val,"&gt"))) $this->val=settags($this->val);

		if(strstr($this->format,"&lt")||strstr($this->format,"&gt")) $this->format=settags($this->format);

		if ($cfg['GLOBAL']['root']['caption_set_params'])
			$this->caption = set_params($this->caption);
		$visible = set_params($this->visibility) != 'hidden';
		if($this->type!="hidden" && $visible)
		{
			if($drawTable)
			{
				print "<tr ";

				if($this->rowcolor) print "bgcolor=".$this->rowcolor;

				print "><td valign='{$this->valign}'";
				if($this->colspan>1) print " colspan=".$this->colspan;
				if($this->tdstyle) print " ".$this->tdstyle;
				print ">";

				if($this->nobr) print "<nobr>";

				if($this->align =="center") print "<center>";

				if($this->needed) print "<b>";

				if($form->wrong&&in_array($this->name,$form->wrong)) print "<font color=red>";

				$this->caption=str_replace("&gt;",">",$this->caption);
				$this->caption=str_replace("&lt;","<",$this->caption);

				if(!$this->align || $this->align =="right"|| $this->align =="center") {
					if($this->type != "info") {
						print $this->caption;
					}
				}

				if($this->needed) print "</b>";

				if(!($this->colspan>1))
				{
					print "</td>";

					if($this->icon) print "<td><center><img src=\"$this->icon\"></td>";

					print "<td valign=top ";

					if($this->table->width) print "width=".$this->table->width;

					print ">";
				}
				else print "</b></font>";

			}
		}

		if($this->val=="%") unset($this->val);

		if($this->type=="currentdate"||$this->type=="currentdatetime")
		{
			$day[$ndate]=round(System::date('d',time()));
			$month[$ndate]=round(System::date('m',time()));
			$year[$ndate]=round(System::date('Y',time()));

			$hour[$ndate]=round(System::date('H',time()));
			$minute[$ndate]=round(System::date('i',time()));
                        $this->type="date";
		}

		if($this->type=="datetime")
		{
			$this->type="date";
			$en_datetime=1;
		}
		else $en_datetime=0;

		if($this->type=="sqldate")
		{

			if($this->val)
			{


				$ar=explode("-",$this->val);
				$day[$ndate]=$ar[2];
				$month[$ndate]=$ar[1];
				$year[$ndate]=$ar[0];

			}

			$this->type="date";
		}

		if($this->type!="editor") $this->val=str_replace("\"","&quot;",$this->val);


		$this->val=str_replace("&gt;",">",$this->val);
		$this->val=str_replace("&lt;","<",$this->val);
		if($this->type=="editor")
			$this->val=str_replace("#", "&resh;",$this->val);

		switch($this->type)
		{
			case "string":
			case "url":
			case "stringlike":
				$maxlength = ($this->showlimit) ? " data-maxlength='{$this->maxlength}' "  : " maxlength='{$this->maxlengt}' ";
				$txtInput = "<input $required name='{$this->name}' size='{$this->size}' type='text' $maxlength value='{$this->val}' {$this->format} />";
				if($this->showlimit) {
					print countCharsWrap($txtInput, $this->name, $this->maxlength);
				} else {
					print $txtInput;
				}
				break;
			case 'div':
				echo "<div id=\"$this->name\">$this->val</div>";
				break;
			case "pincode":
				print "<input $required name=\"".$this->name."1\" size=\"".$this->size."\" type=\"text\" maxlength=\"".$this->maxlength."\" value=\"".$this->val."\" $this->format/>&nbsp;-&nbsp;<input name=\"".$this->name."2\" size=\"".$this->size."\" type=\"text\" maxlength=\"".$this->maxlength."\" value=\"".$this->val."\" $this->format/>&nbsp;-&nbsp;<input name=\"".$this->name."3\" size=\"".$this->size."\" type=\"text\" maxlength=\"".$this->maxlength."\" value=\"".$this->val."\" $this->format/>";
				break;
			case "email":

				?>
			<script>
				function checkEmail(email)
				{

					JsHttpRequest.query('/ajax_functions.php',
							{
								'function': 'checkemail',
								'email': email
							},
							function(result, answer)
							{
								if(result && result["error"])
								{
									alert(result["alert"]);
									document.getElementById("checkemail").innerHTML='<img src=<?=$img_path?>icons/smallred.png align=absmiddle>';
								}
								else document.getElementById("checkemail").innerHTML='<img src=<?=$img_path?>icons/smallgreen.png align=absmiddle>'
							},true);

				}

			</script>
			<?
				print "<input $required name='{$this->name}' onchange='checkEmail(this.value)' size='{$this->size}' type=email maxlength='{$this->maxlength}' value='{$this->val}' {$this->format}>
				 <span id=checkemail></span>";
				break;
			case "switch":
				print "<div class=switch ".$this->format."><input name='{$this->name}' type=checkbox ";
				if($this->name === externalLoginNetwork()) print "  checked";
				print "></div>";
				break;
			case "color":
				print "<script language=JavaScript src=\"/cls/js/picker.js\"></script>";
				print "<input type=\"text\" name=\"".$this->name."\" size=8 value=\"#".$this->val."\">
<a href=\"javascript:TCP.popup(document.".$this->table->act.".".$this->name.",1,document.".$this->table->act.".color".$this->name.")\"><input name=\"color".$this->name."\" readonly style='width:16;height:16;border:1px solid black;cursor:pointer;background-color:".$this->val."'></a>";
				break;
			case "secret":
				if ($visible)
				{
					echo " <img src=\"$site_url"."cls/common/code.php?".session_name()."=".session_id()."\" border=1>";
					echo "<br><input $this->format name=\"".$this->name."\" size=\"".$this->size."\" type=\"text\" maxlength=\"".$this->maxlength."\" $this->format/>";
					unset($_SESSION['captcha_keystring']);
				}
				else
				{
					$keystring=time()+rand(1,10000);
					$captcha=substr(md5($keystring."hgfjdj"),0,6);
					$_SESSION['captcha_keystring']=$keystring;
					echo "<input name=\"$this->name\" value=\"$captcha\" type=\"hidden\"/>";
				}
				break;
			case "password":
				if(strlen($this->val)==32) unset($this->val);
				print "<input $required name=\"".$this->name."\" size=\"".$this->size."\" type=\"password\" maxlength=\"".$this->maxlength."\" value=\"".$this->val."\"/>";
				break;


			case "datelabel":

				if(!$this->format) $this->format="d.m.Y";
				if($this->val) print System::date($this->format,$this->val);


				break;

			case "sqldatelabel":

				$ar=explode("-",$this->val);
				$day[$ndate]=$ar[2];
				$month[$ndate]=$ar[1];
				$year[$ndate]=$ar[0];

				if($day[$ndate]&&$day[$ndate]!="00") print "$day[$ndate].$month[$ndate].$year[$ndate]";


				break;
			case "multidate":
				global $weekdays_short;
				$visibleMultidate = set_params($this->mindate) &&  set_params($this->maxdate);
				$fromDate = $this->mindate ? set_params($this->mindate) : time() ;
				$toDate = $this->maxdate ? set_params($this->maxdate) : time() ;
				$toDate = max($toDate,$fromDate);
				$toDateValue = date('d-m-Y',$toDate);

				$date = $fromDate;
				$dateDOW = $weekdays_short[$lang][System::date('N',$date)-1];
				$dateString = System::date('d-m-Y',$date)." ($dateDOW)";
				$dateValue = date('d-m-Y',$date);

				$size = ($this->size) ? $this->size : 5 ;

				$selection=Array();
				$dates = explode(';',$this->val);
				foreach ($dates as $value)
					$selection[$value]=true;

				if ($visibleMultidate)
				{
					echo "<select name=\"$this->name[]\" multiple size=\"$size\">";
					for (;;)
					{
						$selected = $selection[$dateValue] ? ' selected ' : '';
						echo "<option value=\"$dateValue\" $selected>".$dateString;
						if ($dateValue == $toDateValue)
							break;
						$date+=24*3600;
						$dateDOW = $weekdays_short[$lang][System::date('N',$date)-1];
						$dateString = System::date('d-m-Y',$date)." ($dateDOW)";
						$dateValue = date('d-m-Y',$date);
					}
					echo "</select>";
				}
				break;
			case "date":
				$name=$this->name;

				if($_POST['day'][$ndate]) $day[$ndate]=$_POST['day'][$ndate];
				if($_POST['month'][$ndate]) $month[$ndate]=$_POST['month'][$ndate];
				if($_POST['year'][$ndate]) $year[$ndate]=$_POST['year'][$ndate];

				if($_POST['hour'][$ndate]) $hour[$ndate]=$_POST['hour'][$ndate];
				if($_POST['minute'][$ndate]) $minute[$ndate]=$_POST['minute'][$ndate];



				$dd=$day[$ndate];
				$mm=$month[$ndate];
				$yr=$year[$ndate];
				$hour=$hour[$ndate];
				$minute=$minute[$ndate];


				$ndate++;

				if(!$day[$ndate]&&strlen($this->val)>1&&is_numeric($this->val))
				{
					if ($this->timezone)
					{
						$lasttimezone=date_default_timezone_get();
						date_default_timezone_set($this->timezone);
					}
					$dd=System::date("d",$this->val);
					$mm=System::date("m",$this->val);
					$yr=System::date("Y",$this->val);
					$datepickerDate="$dd-$mm-$yr";

					$hour=System::date("H",$this->val);
					$minute=System::date("i",$this->val);
					if ($this->timezone)
						date_default_timezone_set($lasttimezone);
				}

				if(!$dd&&$this->maxyear) $dd=1;

				$strday= "<select $required id='${name}_day' name='day[]' data-type='day'><option value=\"\"></option>";

				for($i=1;$i<=31;$i++)
				{
					$strday.= "<option value=\"$i\"";
					if($i==$dd) $strday.= " selected";
					$strday.= ">$i</option>";
				}

				$strday.= "</select>";

				$strmonth= "<select $required id='${name}_month' name='month[]' data-type='month'>";

				foreach ($cfg['GLOBAL']['supported_languages'] as $language)
				{
					$months_ex[$language]=array_merge(Array(message('monthMsg','месяц')),$months[$language]);
				}

				$i=0;
				foreach($months_ex[$lang] as $m)
				{
					$strmonth.= "<option value=\"$i\"";
					if($i==$mm) $strmonth.= " selected";
					$strmonth.= ">$m</option>";
					$i++;
				}

				$strmonth.="</select>";

				$stryear="<select $required id='${name}_year' name='year[]' data-type='year'><option value=''></option>";

				if(!$maxyear=$this->maxyear) $maxyear=System::date("Y",time())+1;
				if(!$minyear=$this->minyear) $minyear=1930;

				for($i=$maxyear;$i>=$minyear;$i--)
				{
					$stryear.= "<option value=\"$i\"";
					if($i==$yr) $stryear.= " selected";
					$stryear.= ">$i</option>";
				}

				$stryear.="</select>";
				global $date_format_orders;
				if ($date_format_orders[$lang]=='mdY')
					echo "$strmonth $strday $stryear";
				else
					echo "$strday $strmonth $stryear";

				if($en_datetime)
					echo " <input $required type=\"text\"  onkeypress=\"javascript: return checknumeric(event)\" name='hour[]' data-type='hours' size=2 maxlength=2 value=\"$hour\"/>:<input type=\"text\"  onkeypress=\"javascript: return checknumeric(event)\" name='minute[]' data-type='minutes' size=2 maxlength=2 value=\"$minute\"/>&nbsp;";
				if ($this->datepicker)
				{
					$datepickerName=$this->name.'_datepicker';

					$weekdays_short_sun=$weekdays_short[$lang];
					$sun=array_pop($weekdays_short_sun);
					array_unshift($weekdays_short_sun,$sun);

					$daysMinLine='dayNamesMin: [\''.implode('\',\'',$weekdays_short_sun).'\']';
					$daysShortLine='dayNamesShort: [\''.implode('\',\'',$weekdays_short_sun).'\']';
					$monthsLine='monthNames: [\''.implode('\',\'',$months[$lang]).'\']';

					echo "<input id='$datepickerName' name='$datepickerName' type=hidden value='$datepickerDate'>";
					echo "<script src='http://code.jquery.com/ui/1.10.3/jquery-ui.min.js'></script>";
					echo "<script>";
					echo "$(function() {";
					echo "	$(\"#$datepickerName\").datepicker( { $daysMinLine, $daysShortLine, $monthsLine, buttonImage: '${img_path}engine/datepicker.png', buttonImageOnly: true, firstDay: 1, showOn: 'button', dateFormat: 'd-m-yy',";
					echo "    onSelect: function(date) {";
					echo "      parts = date.split('-');";
					echo "      day = parts[0];";
					echo "      month = parts[1];";
					echo "      year = parts[2];";
					echo "      $('#${name}_day').val(day);";
					echo "      $('#${name}_month').val(month);";
					echo "      $('#${name}_year').val(year);";
					echo "         }";
					echo "} );";
					echo "});";
					echo "</script>";
				}
				break;
			case "numeric":
				if($this->size==20) $this->size=3;
				print "<input $required $this->format name='".$this->name."' data-type=numeric type=text maxlength=\"".$this->maxlength."\" value=\"".$this->val."\" size=".$this->size."  onkeypress=\"javascript: return checknumeric(event)\"/>";
				break;

			case "safepassword":

				?>
			<script>
				function checkpass(pass)
				{

					JsHttpRequest.query('/ajax_functions.php',
							{
								'function': 'checkpass',
								'pass': pass
							},
							function(result, answer)
							{
								if(result && result["error"])
								{
									if(result["error"]==1) alert('<?=message(js_minpasslength,'Минимальная длина пароля - 6 символов');?>');
									if(result["error"]==2) alert('<?=message(js_easypass,'Введен слишком простой пароль');?>');
									if(result["error"]==3) alert('<?=message(js_easypass,'Введен слишком простой пароль');?>');
//                                if(result["error"]==3) alert('<?=message(js_easypass,'Нельзя использовать в качестве пароля даты. Сами рискуете!');?>');

									document.getElementById("checkpass").innerHTML='<img src=<?=$img_path?>icons/smallred.png align=absmiddle>';
								}
								else document.getElementById("checkpass").innerHTML='<img src=<?=$img_path?>icons/smallgreen.png align=absmiddle>'
							},true);

				}

			</script>
			<?

				if(strlen($this->val)==32) unset($this->val);
				//print "<input name=\"".$this->name."\" size=\"".$this->size."\" type=\"password\" maxlength=\"".$this->maxlength."\" value=\"".$this->val."\"/>";
				print "<input $required $this->format name=\"".$this->name."\" type=\"password\" maxlength=\"".$this->maxlength."\" value=\"".$this->val."\" size=".$this->size."  onchange='checkpass(this.value);'/> <span id='checkpass'></span>";

				break;



			case "safepassword2":

				?>
			<script>
				function checkpass2(pass,pass2)
				{

					JsHttpRequest.query('/ajax_functions.php',
							{
								'function': 'checkpass2',
								'pass': pass,
								'pass2': pass2
							},
							function(result, answer)
							{
								if(result && result["error"])
								{
									if(result["error"]==1) alert('<?=message(js_unlikepass,'Пароли не совпадают');?>');

									document.getElementById("checkpass2").innerHTML='<img src=<?=$img_path?>icons/smallred.png align=absmiddle>';
								}
								else document.getElementById("checkpass2").innerHTML='<img src=<?=$img_path?>icons/smallgreen.png align=absmiddle>'
							},true);

				}

			</script>
			<?

				if(strlen($this->val)==32) unset($this->val);

				print "<input $required $this->format name=\"".$this->name."\" type=\"password\" maxlength=\"".$this->maxlength."\" value=\"".$this->val."\" size=".$this->size."  onchange='checkpass2(this.value,this.form.Password.value);'/> <span id='checkpass2'></span>";

				break;
			case "login":

				?>
			<script>
				function checkLogin(login)
				{

					JsHttpRequest.query('/ajax_functions.php',
							{
								'function': 'checklogin',
								'login': login
							},


							function(result, answer)
							{
								if(result && result["error"])
								{
									alert(result["alert"]);
									document.getElementById("checklogin").innerHTML='<img src=<?=$img_path?>icons/smallred.png align=absmiddle>';
								}
								else document.getElementById("checklogin").innerHTML='<img src=<?=$img_path?>icons/smallgreen.png align=absmiddle>'
							},true);

				}

			</script>
			<?
				print "<input $required $this->format name=\"".$this->name."\" type=\"text\" maxlength=\"".$this->maxlength."\" value=\"".$this->val."\" size=".$this->size."  onchange='checkLogin(this.value);' /> <span id='checklogin' ></span>";
				break;

			case "institute_selection":
				$citySelector = $r[CityID];
				$instituteSelector = $r[UniversityID];
				?>
			<script>
				function select_city(selected_id)
				{
					document.getElementById('hidden_institutes').innerHTML = '<center><img src="<?=$img_path?>icons/ajax-loader.gif" style="margin-top:5px;" /></center>';
					JsHttpRequest.query('/ajax_functions.php',
							{
								'function': 'showInstitutes',
								'city_id': document.getElementById('city_selector').options[document.getElementById('city_selector').selectedIndex].value,
								'selected_id': selected_id
							},
							function(result, errors) {
								//if (errors) alert(errors);
								document.getElementById('hidden_institutes').innerHTML = result['content'];
							}
							,true);
				}
			</script>

			<select id="city_selector" name="city_selector" onchange="select_city();" style="width:200px;">

				<option value="-1"  <? if ($citySelector == -1) print "selected"; ?>><?=message(selectcity,"Выберите город");?></option>

				<?
				$res=runsql("(select cm_cities.CityID CityID, cm_cities.Name_rus CityName, ut_countries.CountryID CountryID, ut_countries.Name_rus CountryName FROM cm_cities INNER JOIN ut_countries ON cm_cities.CountryID = ut_countries.CountryID WHERE ut_countries.CountryID=129 and (cm_cities.CityID=250 or cm_cities.CityID=349)) UNION ALL (select cm_cities.CityID CityID, cm_cities.Name_rus CityName, ut_countries.CountryID CountryID, ut_countries.Name_rus CountryName FROM cm_cities INNER JOIN ut_countries ON cm_cities.CountryID = ut_countries.CountryID WHERE ut_countries.CountryID=129 ORDER BY cm_cities.Name_rus)");
				$ic = 1;
				while($r1=mysql_fetch_array($res))
				{
					if ($citySelector==$r1[CityID]) $selected_str = "selected"; else $selected_str = "";
					print "<option value='$r1[CityID]' ".$selected_str.">".$r1[CityName]."</option>";
					if ($ic == 2) print '<optgroup label="Россия">';
					$ic++;
				}
				?>
				</optgroup>
				<optgroup label="Украина">
					<?
					$res=runsql("select cm_cities.CityID CityID, cm_cities.Name_rus CityName, ut_countries.CountryID CountryID, ut_countries.Name_rus CountryName FROM cm_cities INNER JOIN ut_countries ON cm_cities.CountryID = ut_countries.CountryID WHERE ut_countries.CountryID=164 ORDER BY cm_cities.Name_rus");
					while($r1=mysql_fetch_array($res))
					{
						if ($citySelector==$r1[CityID]) $selected_str = "selected"; else $selected_str = "";
						print "<option value='$r1[CityID]' ".$selected_str.">".$r1[CityName]."</option>";
					}
					?>
				</optgroup>
			</select>
			</td></tr><tr><td colspan=2>
	<div id="hidden_institutes"></div>

				<? if ($citySelector && $citySelector != -1) {
				?>
			<script>
				select_city(<?=$instituteSelector;?>);
			</script>
				<? } ?>
				<?
				break;

			case "between":
				if($this->size==20) $this->size=3;

				$n1=$this->name."_1";
				if(strlen($r[$n1])) $val1=$r[$n1];
				else $val1=$_POST[$n1];

				$n1=$this->name."_sign1";
				$sval1=$_POST[$n1];

				$n2=$this->name."_2";
				if(strlen($r[$n2])) $val2=$r[$n2];
				else $val2=$_POST[$n2];

				$n2=$this->name."_sign2";
				$sval2=$_POST[$n2];

				if(!strlen($val1)) $val1=$this->min;
				if(!strlen($val2)) $val2=$this->max;


				print message(frm," от ");

				print " <input align=absmiddle $this->format name=\"".$this->name."_1\" type=\"".$this->type."\" maxlength=\"".$this->maxlength."\" value=\"".$val1."\" size=".$this->size."  onkeypress=\"javascript: return checknumeric(event)\" $this->format /> ";


				print message(to," до ");

				print " <input align=absmiddle $this->format name=\"".$this->name."_2\" type=\"".$this->type."\" maxlength=\"".$this->maxlength."\" value=\"".$val2."\" size=".$this->size."  onkeypress=\"javascript: return checknumeric(event)\" $this->format />";

				break;

			case "text":
				$maxlength = ($this->showlimit) ? " data-maxlength='{$this->maxlength}' "  : " maxlength='{$this->maxlength}' ";
				$txtarea = "<textarea $required $this->format name='$this->name' cols='$this->cols' rows='$this->rows' $maxlength>".str_replace("<br />","\r\n",$this->val)."</textarea>";

				if($this->showlimit) {
					print countCharsWrap($txtarea, $this->name, $this->maxlength);
				} else {
					print $txtarea;
				}
				break;

//			case "editor_old":
//				require_once($site_path."cls/editor_old/fckeditor.php") ;
//				$oFCKeditor = new FCKeditor($this->name) ;
//
//				$oFCKeditor->ToolbarSet = 'Basic' ;
//				if(!$this->height) $oFCKeditor->Height = 400 ;
//				else $oFCKeditor->Height = $this->height ;
//
//				if(!$this->width) $oFCKeditor->Width = 600 ;
//				else $oFCKeditor->Width = $this->width;
//
//				if($this->format) $oFCKeditor->ToolbarSet = $this->format ;
//
//				$oFCKeditor->Value = $this->val;
//				$oFCKeditor->Create() ;
//				break;
			case "editor":
				require_once($site_path . "cls/editor/fckeditor.php");
				$oFCKeditor = new FCKeditor($this->name) ;

				$oFCKeditor->ToolbarSet = 'Basic' ;
				if(!$this->height) $oFCKeditor->Height = 400 ;
				else $oFCKeditor->Height = $this->height ;

				if(!$this->width) $oFCKeditor->Width = '100%' ;
				else $oFCKeditor->Width = $this->width;

				if($this->format) $oFCKeditor->ToolbarSet = $this->format ;

				$oFCKeditor->Value = $this->val;
				$oFCKeditor->Create() ;
				break;
			case "image_popup":
				
				if (!$id)
					$id = $r[0];
				//[!!!] showfull deprecated
				if (!($this->showfull || $this->nosmall) && file_exists($site_path."images/".$this->table->table."/small/$id.jpg"))
					$imgSrc = $img_path.$this->table->table."/small/$id.jpg?rnd=".mt_rand(0,1000);
				if (($this->shofull || $this->nosmall) && file_exists($site_path."images/".$this->table->table."/".strtolower($this->name)."/$id.jpg"))
					$imgSrc = $img_path.$this->table->table."/".strtolower($this->name)."/$id.jpg?rnd=".mt_rand(0,1000);
				
				$imgSrc = ($imgSrc) ? $imgSrc : "/images/1px.jpg";
				$imgHide = ($imgSrc) ? "" : "null";
				echo "
						<img class='imageContainer $imgHide hide' src='$imgSrc'>
						<input name='ImageURL' value='' type='hidden'>
						
				";
				echo "<a href='#chooseImage' data-backdrop='static' role='button' class='btn' data-toggle='modal'>Выберите изображение</a>";
				require_once($site_path . "cls/etc/image_popup.php");

				break;
			case "radio":

				if($this->vals) $vals=explode(";",$this->vals);

				$ar=explode(";",set_params($this->vars));
				$n=0;
				$i=0;

				foreach($ar as $v)
				{
					print "<label class=radio>";
					print "<input $this->format ";

					print " name=\"".$this->name."\" type=\"radio\" ".$this->action." ";

					if($vals[$i]) $val=$vals[$i];
					else $val=$n;


					print " value=\"".$val."\"";

					if((($this->default!="0")||($this->val==$val&&strlen($this->val)))&&(($this->default==$val&&!$this->val) || $this->val==$val||$r[$this->name]==$n||($this->text==$v&&!$r[$this->name]))) print " checked";
					print "/>$v</label>";

					$n++;
					$i++;
				}

				break;

			case "list":
				print "<select $required $this->format name=\"".$this->name."\">";

				if ($this->vals)
					$vals = explode(";",$this->vals);

				$n = 0;
				if (!strlen($this->text) && !$this->needed)
				{
					print "<option value=\"$this->default\">-</option>";
					$n = 1;
				}

				$ar = explode(";",$this->vars);

				if (!$this->vars)
					$ar = $_GLOBALS[$this->default];

				$i=0;
				foreach ($ar as $v)
				{
					if (strlen($vals[$i]))
						$var = $vals[$i];
					else
						$var = $n;

					print "<option value=\"$var\"";

					if ($this->val==$var)
						print " selected";
					elseif (!strlen($this->val)&&($this->text=="$var"||($this->text==$v&&!$r[$this->name])))
						print " selected";

					print ">$v</option>";
					$n++;
					$i++;
				}

				print "</select>";
				break;
			case "multilist":
				$size = ($this->size) ? $this->size : 5 ;

				$dates = explode('|',$this->val);

				echo "<select $required $this->format name=\"".$this->name."[]\" multiple size=\"$size\">";

				if ($this->vals)
					$vals = explode(";",$this->vals);

				$n = 0;
// 			if (!strlen($this->text) && !$this->needed)
// 			{
// 				echo "<option value=\"$this->default\">-</option>";
// 				$n = 1;
// 			}

				$ar = explode(";",$this->vars);

				if (!$this->vars)
					$ar = $_GLOBALS[$this->default];

				$i=0;
				foreach ($ar as $v)
				{
					if (strlen($vals[$i]))
						$var = $vals[$i];
					else
						$var = $n;

					echo "<option value=\"$var\"";

					if ($this->val==$var)
						echo " selected";
					elseif (!strlen($this->val)&&($this->text=="$var"||($this->text==$v&&!$r[$this->name])))
						echo " selected";
					elseif (in_array($var,$dates))
						echo " selected";

					echo ">$v</option>";
					$n++;
					$i++;
				}

				echo "</select>";
				break;
			case "taglist":
				$datepickerName=$this->name.'_xxx';

				$size = ($this->size) ? $this->size : 5 ;

				$dates = explode('|',$this->val);

				echo "<select id=\"$datepickerName\" $this->format name=\"".$this->name."[]\" multiple size=\"$size\">";

				if ($this->vals)
					$vals = explode(";",$this->vals);

				$n = 0;
// 			if (!strlen($this->text) && !$this->needed)
// 			{
// 				echo "<option value=\"$this->default\">-</option>";
// 				$n = 1;
// 			}

				$ar = explode(";",$this->vars);

				if (!$this->vars)
					$ar = $_GLOBALS[$this->default];

				$i=0;
				foreach ($ar as $v)
				{
					if (strlen($vals[$i]))
						$var = $vals[$i];
					else
						$var = $n;

					echo "<option value=\"$var\"";

					if ($this->val==$var)
						echo " selected class=\"selected\"";
					elseif (!strlen($this->val)&&($this->text=="$var"||($this->text==$v&&!$r[$this->name])))
						echo " selected class=\"selected\"";
					elseif (in_array($var,$dates))
						echo " selected class=\"selected\"";

					echo ">$v</option>";
					$n++;
					$i++;
				}

				echo "</select>";
				echo "<script type=\"text/javascript\">";
				echo "$(function() {";
				echo "	$(\"#$datepickerName\").fcbkcomplete({";
				echo "  complete_text: '".message('complete_text','Введите текст')."',";
				echo "  filter_begin: true,";
				echo "  filter_selected: true,";
				echo "  newel: false,";
				echo "  select_all_text: '".message('select_all_text','Выбрать всё')."'";
				echo "  });";
				echo "});";
				echo "</script>";
				break;
			case "urllabel":

				$this->val=str_replace(">","&gt;",$this->val);
				$this->val=str_replace("<","&lt;",$this->val);

				if($this->val&&!strstr($this->val,"http://")) $this->val="http://".$this->val;

				if($this->val&&$this->val!="http://") print "<a href=\"$this->val\">$this->val</a>";
				break;

			case "emaillabel":

				$this->val=str_replace(">","&gt;",$this->val);
				$this->val=str_replace("<","&lt;",$this->val);

				if($this->val) print "<a href=\"mailto:$this->val\">$this->val</a>";
				break;

			case "icqlabel":

				$this->val=str_replace(">","&gt;",$this->val);
				$this->val=str_replace("<","&lt;",$this->val);

				$icq=str_replace("-","",$this->val);
				$icq=str_replace(" ","",$icq);
				if($this->val) print "<img src=\"http://status.icq.com/online.gif?icq=$icq&img=5\" height=18px width=18px/> ";
				if($this->val) print "<a href=\"http://wwp.icq.com/scripts/contact.dll?msgto=$icq\">".$this->val."</a>";
				break;
			case "listlabel":
				listlabelRender($this);
				break;
			case "sqllabel":
				$res=runsql($this->sql);
				while($r1=mysql_fetch_array($res))
				{

					if("$this->val"==$r1[0]) print "$r1[1]";

					$n++;
				}
				break;
			case "sqlist":
				echo "<select $required $this->format name=\"".$this->name."\">";

				$res = runsql($this->sql);

				echo "<option value=\"$this->default\">-";
				echo "</option>";

				$n = 1;

				while ($r1=mysql_fetch_array($res))
				{
					$n = "Name_".$lang;
					if (!$r1[$n])
						$n = "Name_rus";
					if (!$r1[$n])
						$n = 1;
					echo "<option value=\"$r1[0]\"";

					if ($r1[color])
						echo " style='background-color:$r1[color]' ";
					if ("$this->val"==$r1[0]||("$this->val"==$r1[$n]))
						echo " selected";

					echo ">$r1[$n]</option>";
					$n++;
				}
				print "</select>";
				break;
			case "sqlmultilist":
				$size = ($this->size) ? $this->size : 5 ;

				$dates = explode('|',$this->val);

				echo "<select $this->format name=\"".$this->name."[]\" multiple size=\"$size\">";

				$res = runsql($this->sql);

// 			echo "<option value=\"$this->default\">-";
// 			echo "</option>";

				$n = 1;

				while ($r1=mysql_fetch_array($res))
				{
					$n = "Name_".$lang;
					if (!$r1[$n])
						$n = "Name_rus";
					if (!$r1[$n])
						$n = 1;
					echo "<option value=\"$r1[0]\"";

					if ($r1[color])
						echo " style='background-color:$r1[color]' ";
					if ("$this->val"==$r1[0]||("$this->val"==$r1[$n]))
						echo " selected";
					elseif (in_array($r1[0],$dates))
						echo " selected";
					elseif (in_array($r1[$n],$dates))
						echo " selected";

					echo ">$r1[$n]</option>";
					$n++;
				}
				print "</select>";
				break;
			case "sqltaglist":
				$datepickerName=$this->name.'_yyy';

				$size = ($this->size) ? $this->size : 5 ;

				$dates = explode('|',$this->val);

				echo "<select $this->format name=\"".$this->name."[]\" multiple size=\"$size\" id=\"$datepickerName\">";

				$res = runsql($this->sql);

// 			echo "<option value=\"$this->default\">-";
// 			echo "</option>";

				$n = 1;

				while ($r1=mysql_fetch_array($res))
				{
					$n = "Name_".$lang;
					if (!$r1[$n])
						$n = "Name_rus";
					if (!$r1[$n])
						$n = 1;
					echo "<option value=\"$r1[0]\"";

					if ($r1[color])
						echo " style='background-color:$r1[color]' ";
					if ("$this->val"==$r1[0]||("$this->val"==$r1[$n]))
						echo " selected class=\"selected\"";
					elseif (in_array($r1[0],$dates))
						echo " selected class=\"selected\"";
					elseif (in_array($r1[$n],$dates))
						echo " selected class=\"selected\"";

					echo ">$r1[$n]</option>";
					$n++;
				}
				print "</select>";
				echo "<script type=\"text/javascript\">";
				echo "$(function() {";
				echo "	$(\"#$datepickerName\").fcbkcomplete({";
				echo "  complete_text: '".message('complete_text','Введите текст')."',";
				echo "  filter_begin: true,";
				echo "  filter_selected: true,";
				echo "  newel: false,";
				echo "  select_all_text: '".message('select_all_text','Выбрать всё')."'";
				echo "  });";
				echo "});";
				echo "</script>";
				break;
			case "sqlradio":


				$res=runsql($this->sql);


				$n=0;

				while($r1=mysql_fetch_array($res))
				{

					$name="Name_".$lang;
					if(!$r1[$name]) $name="Name_rus";
					if(!$r1[$name]) $name=1;

					print "<input ".$this->format." class=\"radio\" name=\"".$this->name."\" type=\"radio\"  value=\"".$r1[0]."\"";
					if(($this->val==$r1[0]&&$this->val)||$r[$this->name]==$r1[0]||(($this->text==$r1[1]||$this->text=="$n")&&!$r[$this->name])) print " checked";
					print "/>$r1[$name]";
					if(!$this->horizontal) print "<br>";

					$n++;
				}

				break;

			case 'checkbox':
				echo "<input $required class=\"checkbox\" $this->format type=\"checkbox\" value=\"1\" name=\"".$this->name."\"";
				if ($this->val)
					echo " checked";
				echo "/>";
				break;
			case 'image':
				if (!$id)
					$id = $r[0];
				//[!!!] showfull deprecated
				if (!($this->showfull || $this->nosmall) && file_exists($site_path."images/".$this->table->table."/small/$id.jpg"))
					echo "<img class=imageContainer src=$img_path".$this->table->table."/small/$id.jpg?rnd=".mt_rand(0,1000).">";
				if (($this->shofull || $this->nosmall) && file_exists($site_path."images/".$this->table->table."/".strtolower($this->name)."/$id.jpg"))
					echo "<img class=imageContainer src=$img_path".$this->table->table."/".strtolower($this->name)."/$id.jpg?rnd=".mt_rand(0,1000).">";
				echo "<input type='file' name='{$this->name}' data-fix-width='{$this->fixwidth}' data-fix-height='{$this->height}'>";
				break;
			case 'autocomplete':


				break;
			case "banner":
				$flash=new Flash( 'banner' );
				$flash->filename = set_params($this->filename);
				$flash->width=set_params($this->width);
				$flash->height=set_params($this->height);
				$flash->toHTML();
				print "<br><input type=\"file\" name=\"".$this->name."\"/>";
				break;
			case "imageeditor":

				$q=select("select RecordID,IDName from en_table_images where TableName='$form->table' and FieldName='$this->name'");
				$record=$q[0];
				$fieldname=$q[1];

				if(strlen($r[$this->name])>0)
				{

					?>

				<iframe src="<?=$site_url?>js/resize.php?width=<?=$this->width?>&height=<?=$this->height?>&id=<?=$id?>&image=1&record=<?=$q[0]?>" width="700" height="700" hspace="10" vspace="10" align="center">
					<?=message(dontsupportiframes,"Ваш браузер не поддерживает плавающие фреймы!");?>
				</iframe>

					<?

				}
				else
				{

					?>

				<iframe src="<?=$site_url?>js/resize.php?width=<?=$this->width?>&height=<?=$this->height?>&image=0&record=<?=$q[0]?>&id=<?=$id?>" width="700" height="700" hspace="10" vspace="10" align="center">
					<?=message(dontsupportiframes,"Ваш браузер не поддерживает плавающие фреймы!");?>
				</iframe>

				<?

				}

				break;

			case "flag":
				if(strlen($r[$this->name])>0) print "<img src=\"$site_url"."cls/images/flag.php?id=$r[0]&dbname=$db->dbname\" title=\"$r[Country]\" width=21px height=13px border=0/><br>";
				print "<input type=\"file\" name=\"".$this->name."\"/>";
				break;

			case "clear":
				print $this->val.$this->format;
				break;
			case "safelabel":
				$this->val=PrettyURL($this->val,$this->maxwordlength);
				$this->val=stripslashes($this->val);
				print "<span $this->format>$this->val</span>";
				break;
			case "info":
				$info_close = ($this->close) ? '<button type=button class=close data-dismiss=alert>&times;</button>' : '';
				print "<div data-info class=alert>{$info_close}{$this->name}</div>";
				break;
			case "label":
				$this->val=str_replace("#dot",";",$this->val);
				$this->val=stripslashes($this->val);
				if(strstr($this->val,"\$")) $this->val=set_params($this->val);
				print "<input data-hello type=\"hidden\" name=\"".$this->name."\"  value=\"".$this->val."\"/>";
				print "<span $this->format>$this->val</span>";
				break;
			case "dots":
				$this->val=trim($this->val);
				print "<input type=\"hidden\" name=\"".$this->name."\" value=\"".$this->val."\"/>";
				print dots($this->val).$this->format;
				break;
			case "money":
				print "<input type=\"hidden\" name=\"".$this->name."\" value=\"".$this->val."\"/>";
				print dots($this->val).$gd;
				break;
			case "icon":
				$this->format=set_params($this->format);
				if($this->text) print "<a href=".set_params($this->text).">";
				$style=set_params($this->style);
				if(file_exists($site_path.$this->format) || file_exists($this->format) || remote_file_exists($this->format)) {
					print "<center><img src=\"$this->format\" $style border=0></a>";
				}
				break;

			default:
				$render = self::$renders[$this->type];
				if ($render)
					$render($this,Array());
				else
					print "<input name=\"".$this->name."\" type=\"".$this->type."\" value=\"".$this->val."\" $this->format/>";
				break;
		}

		if($this->align =="left") print $this->caption;

		if($this->hint) print "<span data-type='hint'>$this->hint</span>";

		if($drawTable&&$this->type!="hidden"&&$visible)	print "</td>\n</tr>\n";

	}
}


class cls_header
{
	var $name;
	var $caption;
	var $lang;
	var $type;
	var $format;
	var $action;
	var $table;
	var $order;
	var $align;
	var $width;
	var $background;
	var $val;
	var $value;
	var $size;
	var $min;
	var $max;
	var $sql;
	var $default;
	var $vars;
	var $text;
	var $colspan;
	var $count;
	var $tdstyle;
	var $style;
	var $title;
	var $src;
	var $target;

	static private $renders = Array();

	static public function addRender($type,$render)
	{
		self::$renders[$type] = $render;
	}

	function cls_header($field,$table)
	{
		global $lang,$r;

		$this->table=$table;

		if($pos=strpos($table->sql,"order by"))
		{
			$order=substr($table->sql,$pos+9);
			if($order==$this->name)
			{
				$this->order=$order;
				if(strstr($this->order,"asc")) $this->order=str_replace("asc","desc",$this->order);
				elseif(!strstr($this->order,"desc")) $this->order.=" desc";
			}
		}

		$this->action=$table->name;

		foreach($field as $k=>$v)
		{
			//if(strstr($v,"\$")) $v=set_params($v);

			if(substr($k,strlen($k)-4,1)=="_")
			{

				if(substr($k,strlen($k)-3)==$lang)
				{
					$k=substr($k,0,strlen($k)-4);
					$this->$k=$v;
				}
			}
			else $this->$k=$v;
		}

		if(strstr($this->type,"\$")) $this->type=set_params($this->type);

		//$this->text=$field->text;

		if(!$this->caption) $this->caption=$this->name;

		{
			if($field['name']) $this->name=$field['name'];
			elseif(!$this->name) $this->name=$field['name_eng'];
		}

		if($this->lang) $this->name.="_$lang";

		if(strstr($this->name,"\$")) $this->name=set_params($this->name);
		if (!$this->order || strpos($this->order,';'))
			$this->order = $this->name;
		if(strstr($this->caption,"\$")) $this->caption=set_params($this->caption);

	}

	function drawHeader()
	{
		switch ($this->export)
		{
			case 'csv':
				$this->drawHeaderCSV();
				break;
			case 'html':
			case '1':
			case '':
				$this->drawHeaderHTML();
				break;
		}
	}

	function drawHeaderCSV()
	{

	}

	function drawHeaderHTML()
	{
		$table=$this->table;
		print "<td ";
		if($this->colspan>1) print "colspan=$this->colspan ";
		if($this->width) print "width=$this->width ";
		if($this->align) print "align=$this->align ";
		if($table->headerbg) print "background=\"$table->headerbg\" ";
		print ">";
	}

	function drawFooter()
	{

		switch ($this->export)
		{
			case 'csv':
				$this->drawFooterCSV();
				break;
			case 'html':
			case '1':
			case '':
				$this->drawFooterHTML();
				break;
		}
	}

	function drawFooterCSV()
	{
		echo ";";
	}

	function drawFooterHTML()
	{
		print "</td>\n";
	}

	function drawDraw($srt)
	{
		switch ($this->export)
		{
			case 'csv':
				$this->drawDrawCSV($srt);
				break;
			case 'html':
			case '1':
			case '':
				$this->drawDrawHTML($srt);
				break;
		}
	}

	function drawDrawCSV($srt)
	{
		echo strip_tags($this->caption);
	}

	function drawDrawHTML($srt)
	{
		global $PHP_SELF,$QUERY_STRING,$img_path;

		if($this->table->act=="select"&&$this->order!="no")
		{
			$url = "$PHP_SELF?";
			if ($QUERY_STRING)
			{
				if ($pos=strpos($QUERY_STRING,"sort"))
					$QUERY_STRING=substr($QUERY_STRING,0,$pos-1);
				$url .= $QUERY_STRING;
				if(!strstr($QUERY_STRING,"type"))
					$url .= "&type=".$this->table->type;
				if(!strstr($QUERY_STRING,"act"))
					$url .= "&act=$this->action";
				if(!strstr($QUERY_STRING,"id"))
					$url .= "&id=$id";
				$url .= "&sort=".$srt;
			}
			else
				$url .= "type=".$this->table->type."&act=$this->action&sort=".$srt;
			$url = filter_var($url, FILTER_SANITIZE_STRING);
			echo "<a class='table-title' href=\"$url\"";

			if($this->title)
				print " title=\"".$this->title."\"";

			print ">";
			print $this->caption."</a>";

			if($this->type=="checkall")
				print "<input type=checkbox onclick='checkall(this,\"$this->name\")' >";
		}
		else
		{
			print "<span ";
			if($this->title) print " title=\"".$this->title."\"";
			print "><b>".$this->caption."</b></span>";
		}
		if ($this->hint)
			echo '&nbsp;<img src="'.$img_path.'engine/hint.gif" width="17px" height="17px" align="absmiddle" title="'.$this->hint.'">';
	}

	function Draw()
	{
		global $st,$sort,$act,$r,$QUERY_STRING,$PHP_SELF,$id;

		$table=$this->table;
		$this->drawHeader();

		if(!$table->noheader)
		{
			if($sort!=$this->order." desc"&&$sort!=$this->order." asc"&&$sort!=$this->order)
				$srt=$this->order;
			else
			{
				if(strstr($sort,"desc"))
					$srt=str_replace("desc","asc",$sort);
				elseif(strstr($sort,"asc"))
					$srt=str_replace("asc","desc",$sort);
				else
					$srt=$sort." desc";
			}
		}

		$this->drawDraw($srt);
		$this->drawFooter();
	}

	function drawFieldHeader()
	{
		switch ($this->export)
		{
			case 'csv':
				$this->drawFieldHeaderCSV();
				break;
			case 'html':
			case '1':
			case '':
				$this->drawFieldHeaderHTML();
				break;
		}
	}

	function drawFieldHeaderCSV()
	{

	}

	function drawFieldHeaderHTML()
	{
		$visible = set_params($this->visibility) != 'hidden';
		if ($this->type=="hidden"||!$visible)
			return;
		if($this->tdeval && eval(set_params($this->tdeval))=='0')
			return 0;
		elseif($this->tdstyle&&strstr($this->tdstyle,"\$"))
			print "<td ".set_params($this->tdstyle);
		elseif($this->tdstyle)
			print "<td ".$this->tdstyle;
		else
			print "<td";

		if($this->colspaneval && $colspan=eval(set_params($this->colspaneval)))
			print " colspan=$colspan";

		print ">\n";
		if(strlen($this->val))
		{
			if($this->align=="center")
				print "<center>";
			elseif($this->align)
				print "<div align=\"$this->align\">";
		}
	}

	function drawFieldFooter()
	{
		switch ($this->export)
		{
			case 'csv':
				$this->drawFieldFooterCSV();
				break;
			case 'html':
			case '1':
			case '':
				$this->drawFieldFooterHTML();
				break;
		}
	}

	function drawFieldFooterCSV()
	{
		echo ';';
	}

	function drawFieldFooterHTML()
	{
		$visible = set_params($this->visibility) != 'hidden';
		if ($this->type=="hidden"||!$visible)
			return;
		print "</td>\n";
	}

	function drawField($r,$numrow,$name,$format)
	{
		switch ($this->export)
		{
			case 'csv':
				$this->drawFieldCSV($r,$numrow,$name,$format);
				break;
			case 'html':
			case '1':
			case '':
				$this->drawFieldHTML($r,$numrow,$name,$format);
				break;
		}
	}

	function drawFieldCSV($r,$numrow,$name,$format)
	{
		global $img_path,$gladtypenames,$type,$act,$site_path,$img_url,$site_url,$r,$gd,$db,$number,$_POST,$auth,$dbname,$img_url,$lang;
		ob_start();

		switch($this->type)
		{
			case "flag":

				print "<img src=\"$img_path"."flag/$r[$name].gif\" width=21px height=13px border=0 title=\"$r[Country]\"/>";
				if($r[CountryID2]) print "<img src=\"$img_path"."flag/$r[CountryID2].gif\" width=21px height=13px border=0 title=\"$r[Country2]\"/>";

				break;
			case "kit":
				if(strlen($r[$name])>0) print "<img src=\"$img_path"."ut_kits/kit/$r[0].jpg\" border=0>";
				break;
			case "showimage":
				$table=$this->table;
				$fname=$table->table."/".strtolower($this->name)."/$r[0].jpg";

				if(file_exists($site_path."images/".$fname)) print "<img src=\"$img_path"."$fname?rnd=".mt_rand(0,10000)."\" title=\"$r[Name]\" alt=\"$r[Name]\" border=0>";

				break;
			case "team":
				$name2=$name."ID";
				if(!$r[TeamID]) $r[$name2];
				if($r[Flag]&&$r[CountryID]) print "<img src=\"/cls/images/flag.php?id=$r[CountryID]\" border=0 title=\"$r[Country]\" width=21px height=13px/> ";
				print "<a href=\"/xml/players/roster.php?id=$r[TeamID]\">";
				if($r[TeamID]==$auth->id) print "<b>";
				print $r[$name];
				print "</a>";
				break;
			case "mail":

				if($r[GuildID]) print "<a href=/guilds/$r[GuildID]><img src=$img_path"."gd_guilds/small/$r[GuildID].jpg border=0 title='".message(gildmaillist,"Рассылка гильдии")."'></a> ";
				if($r[Senate]) print "<img src=$img_path"."vip/left/3.gif border=0 title='".message(senatmaillist,"Рассылка Сената")."' width=15px height=15px> ";

				if($r[Status]==0) print "<img src=\"$img_path"."engine/mail.gif\" width=14px height=11px/>";
				else print "<img src=\"$img_path"."engine/read.gif\">";
				print " <a href=\"$PHP_SELF?id=$r[MessageID]&type=$type&act=message\">";

				if($r[Status]==0) print "<b>".$this->val."</b>";
				else print $this->val;

				print "</a>";
				break;
			case "icon":
				iconRender($this,$format);
				break;
			case "icq":
				$this->val=str_replace(" ","",$this->val);
				$icq=str_replace("-","",$this->val);

				if($this->val) print "<img src=\"http://status.icq.com/online.gif?icq=$icq&img=5\"  height=18px width=18px/> ";
				if($this->val) print "<a href=\"http://wwp.icq.com/scripts/contact.dll?msgto=$icq\">$this->val</a>";

				break;
			case "date":
				if ($this->timezone)
				{
					$lasttimezone=date_default_timezone_get();
					date_default_timezone_set($item->timezone);
				}
				if(is_numeric($r[$name])&&($r[$name]>0))
					print "<nobr>".System::date($format,$this->val)."</nobr>";
				elseif($r[$name]>0)
					print $r[$name];
				if ($this->timezone)
					date_default_timezone_set($lasttimezone);
				break;

			case "gladtypes":

				if($this->val=="1|2|3|4|5|6|7|8|9|10" || !$this->val) print message(alltypes,"любые типы");
				elseif($this->val=="1|2|3|4|5|6|7") print message(allmaintypes,"все основные типы");
				else
				{
					$ar=explode("|",$this->val);
					foreach($ar as $v)
					{
						$gladtypes[$v]=1;
					}

					for($i=1;$i<=10;$i++)
					{
						if($gladtypes[$i]) print "<img src=$img_path"."types/$i.gif title='".$gladtypenames[$lang][$i]."'> ";
					}
				}
				break;

			case "number":
				$number++;
				print "<div align=right>".$number.".";
				break;
			case "divnumber":
				$number++;
				if($number<=3) echo "<div align=right><img src=".$img_path."icons/olympic/$number.png border=0>";
				else print "<div align=right>".$number.".";
				break;
			case "numeric":

				if($this->size==20) $this->size=3;

				print "<input data-type=numeric name=\"".$this->name."[".($numrow-1)."]\" ";

				if(!strstr($this->format,"readonly")) print "onkeypress=\"javascript: return checknumeric(event)\" ";

				print " type=\"text\" maxlength=\"".$this->maxlength."\" value=\"".$this->val."\" size=".$this->size." $format/>";
				break;
			case "string":
				print "<input name=\"".$this->name."[".($numrow-1)."]\" type=\"text\" maxlength=\"".$this->maxlength."\" value=\"".$this->val."\" size=".$this->size." $format/>";
				break;
			case "email":
				print "<input name=\"".$this->name."[".($numrow-1)."]\" type=\"email\" maxlength=\"".$this->maxlength."\" value=\"".$this->val."\" size=".$this->size." $format/>";
				break;
			case "image":
				print "<input type=\"file\" name=\"".$this->name."[".($numrow-1)."]\" style='width:170px'/>";
				break;

			case "text":

				if(!$this->cols) $this->cols=20;
				if(!$this->rows) $this->rows=2;
				print "<textarea $this->format name=\"".$this->name."[".($numrow-1)."]\" cols=".$this->cols." rows=".$this->rows." maxlength=\"".$this->maxlength."\">".str_replace("<br />","\r\n",$this->val)."</textarea>";
				break;

			case "file":
				print "<input type=\"file\" name=\"".$this->name."[]\" style='width:170px'/>";
				break;

			case "preview":
				if(strlen($r[$name])>0) print "<img src=\"$img_url"."?id=$r[0]&table=".$this->table->table."&dbname=".$dbname."\" border=0><br>";
				break;

			case "dots":
				print "<nobr>";
				if($this->val) print dots($this->val).$format;
				else print "0".$format;
				break;
			case "money":
				print "<nobr>";
				if($this->val) print $format.dots($this->val);
				elseif(strlen($this->val)) print $format."0";
				break;

			case "report":
				print "<a href=\"$site_url"."admin/adm_games.php?id=".$r[0]."\"><img src=\"$img_path"."icons/report.gif\" width=17px height=16px border=0 alt=\"".message(edit,"Редактировать")."\" title=\"".message(edit,"Редактировать")."\"/></a>";
				break;
			case "golden":
				print "<nobr>";
				print dots($this->val).$format.$gd;
				break;
			case "edit":
				print "<a href=\"$PHP_SELF?act=update&id=$r[0]&type=".($this->table->type)."\"><img src=\"$img_path"."engine/edit.png\" width=16 height=16 alt=\"".message(edit,"Редактировать")."\" title=\"".message(edit,"Редактировать")."\"/></a>";
				break;
			case "delete":
				print "<a href=\"$PHP_SELF?act=delete&id=$r[0]&step=1&type=".($this->table->type)."\" onclick=\"return confirm('".message(areushure,"Вы уверены? Запись будет удалена")."')\"><img src=\"$img_path"."engine/drop.png\" width=16 height=16 alt=\"".message(delete,"Удалить")."\" title=\"".message(delete,"Удалить")."\"/></a>";
				break;

			case "up":
				print "<a href=\"$PHP_SELF?act=up&id=$r[0]&step=1&type=".($this->table->type)."\"><img src=\"$img_path"."engine/up1.gif\" width=12px height=12px border=0  /></a>";
				break;
			case "down":
				print "<a href=\"$PHP_SELF?act=down&id=$r[0]&step=1&type=".($this->table->type)."\"><img src=\"$img_path"."engine/down1.gif\" width=12px height=12px border=0 /></a>";
				break;

			case "remove":
				print "<a href=\"$PHP_SELF?act=remove&id=$r[0]&step=1&type=".($this->table->type)."\" onclick=\"return confirm('".message(areushure,"Вы уверены? Запись будет удалена")."')\"><img src=\"$img_path"."engine/drop.png\" width=16 height=16 border=0 alt=\"".message(delete,"Удалить")."\" title=\"".message(delete,"Удалить")."\"/></a>";
				break;

			case "checkall":
			case "checkbox":
				print "<input class=\"checkbox\" type=\"checkbox\" name=\"".$this->name."[".($numrow-1)."]\"";
				if($r[$this->name]==1) print " checked";
				print "/>";
				break;
			case "radio":
				if($this->vals) $this->val=set_params($this->vals);

				print "<input  class=\"radio\" type=\"radio\" name=\"".$this->name."\"";
				print " value='$this->val'";

				if($this->val==$this->text) print " checked=1";

				print "/>";
				break;
			case "list":
				print "<select name=\"".$this->name."[]\">";

				if(!strlen($this->text))
				{
					print "<option value=\"$this->default\">-";
					print "</option>";
				}

				$ar=explode(";",$this->vars);
				$n=0;
				foreach($ar as $v)
				{
					print "<option value=\"$n\"";
					if($r[$this->name]==$n||($this->text==$v&&!$r[$this->name])) print " selected";
					print ">$v</option>\n";
					$n++;
				}

				print "</select>\n";
				break;
			case "2dreport":
				if($r[Finished]) print "<a href=\"$site_url"."xml/tour/getmatch.php?id=".set_params("\$MatchID;")."\" target=_blank><img src=\"$img_path"."engine/goal.gif\" width=15px height=15px border=0/></a>";
				break;
			case "listlabel":
				listlabelRender($this);
				break;
			case "sqlist":
				$res=runsql($this->sql);
				if(mysql_num_rows($res))
				{
					print "<select $required name=\"".$this->name."[]\">\n";

					if(!strlen($this->text))
					{
						print "<option value=\"$this->default\">-";
						print "</option>";
					}

					$n=0;
					while($r1=mysql_fetch_array($res))
					{
						print "<option value=\"$r1[0]\"";
						if($r1[2]) print " style='background-color:$r1[2]' ";
						if(($this->val==$r1[0]&&$this->val)||$r[$this->name]==$r1[0]||(($this->text==$r1[1]||$this->text=="$n")&&!$r[$this->name])) print " selected";
						print ">";
						print "$r1[1]</option>\n";
						$n++;
					}

					print "</select>\n";
				}
				else print "<input type=\"hidden\" name=\"$this->name[".($numrow-1)."]\"/>";

				break;
			case 'hidden':
				print "<input type=\"hidden\" name=\"$this->name[".($numrow-1)."]\" $format value=\"$this->val\"/>";
				break;
			default:
				if (preg_match('/^[0-9]+(\.[0-9]+)?$/',$this->val))
					echo str_replace('.',',',$this->val);
				else
					print $format.stripslashes($r[$name]);
				break;
		}
		$result = ob_get_contents();
		ob_end_clean();
		echo strip_tags($result);
	}

	function drawFieldHTML($r,$numrow,$name,$format)
	{
		global $img_path,$gladtypenames,$type,$act,$site_path,$img_url,$site_url,$r,$gd,$db,$number,$_POST,$auth,$dbname,$img_url,$lang;

		switch($this->type)
		{
			case "flag":
				print "<img src=\"$img_path"."flag/$r[$name].gif\" width=21px height=13px border=0 title=\"$r[Country]\"/>";
				if($r[CountryID2]) print "<img src=\"$img_path"."flag/$r[CountryID2].gif\" width=21px height=13px border=0 title=\"$r[Country2]\"/>";
				break;
			case "kit":
				if(strlen($r[$name])>0) print "<img src=\"$img_path"."ut_kits/kit/$r[0].jpg\" border=0>";
				break;
			case "showimage":
				$table=$this->table;
				$fname=$table->table."/".strtolower($this->name)."/$r[0].jpg";

				if(file_exists($site_path."images/".$fname)) print "<img src=\"$img_path"."$fname?rnd=".mt_rand(0,10000)."\" title=\"$r[Name]\" alt=\"$r[Name]\" border=0>";

				break;
			case "team":
				$name2=$name."ID";
				if(!$r[TeamID]) $r[$name2];
				if($r[Flag]&&$r[CountryID]) print "<img src=\"/cls/images/flag.php?id=$r[CountryID]\" border=0 title=\"$r[Country]\" width=21px height=13px/> ";
				print "<a href=\"/xml/players/roster.php?id=$r[TeamID]\">";
				if($r[TeamID]==$auth->id) print "<b>";
				print $r[$name];
				print "</a>";
				break;
			case "mail":

				if($r[GuildID]) print "<a href=/guilds/$r[GuildID]><img src=$img_path"."gd_guilds/small/$r[GuildID].jpg border=0 title='".message(gildmaillist,"Рассылка гильдии")."'></a> ";
				if($r[Senate]) print "<img src=$img_path"."vip/left/3.gif border=0 title='".message(senatmaillist,"Рассылка Сената")."' width=15px height=15px> ";

				if($r[Status]==0) print "<img src=\"$img_path"."engine/mail.gif\" width=14px height=11px/>";
				else print "<img src=\"$img_path"."engine/read.gif\">";
				print " <a href=\"$PHP_SELF?id=$r[MessageID]&type=$type&act=message\">";

				if($r[Status]==0) print "<b>".$this->val."</b>";
				else print $this->val;

				print "</a>";
				break;
			case "icon":
				iconRender($this,$format);
				break;
			case "icq":
				$this->val=str_replace(" ","",$this->val);
				$icq=str_replace("-","",$this->val);

				if($this->val) print "<img src=\"http://status.icq.com/online.gif?icq=$icq&img=5\"  height=18px width=18px/> ";
				if($this->val) print "<a href=\"http://wwp.icq.com/scripts/contact.dll?msgto=$icq\">$this->val</a>";

				break;
			case "date":
				if ($this->timezone)
				{
					$lasttimezone=date_default_timezone_get();
					date_default_timezone_set($item->timezone);
				}
				if(is_numeric($r[$name])&&($r[$name]>0))
					print "<nobr>".System::date($format,$this->val)."</nobr>";
				elseif($r[$name]>0)
					print $r[$name];
				if ($item->timezone)
					date_default_timezone_set($lasttimezone);
				break;

			case "gladtypes":

				if($this->val=="1|2|3|4|5|6|7|8|9|10" || !$this->val) print message(alltypes,"любые типы");
				elseif($this->val=="1|2|3|4|5|6|7") print message(allmaintypes,"все основные типы");
				else
				{
					$ar=explode("|",$this->val);
					foreach($ar as $v)
					{
						$gladtypes[$v]=1;
					}

					for($i=1;$i<=10;$i++)
					{
						if($gladtypes[$i]) print "<img src=$img_path"."types/$i.gif title='".$gladtypenames[$lang][$i]."'> ";
					}
				}
				break;

			case "number":
				$number++;
				print "<div align=right>".$number.".";
				break;
			case "divnumber":
				$number++;
				if($number<=3) echo "<div align=right><img src=".$img_path."icons/olympic/$number.png border=0>";
				else print "<div align=right>".$number.".";
				break;
			case "numeric":

				if($this->size==20) $this->size=3;

				print "<input data-type=numeric name=\"".$this->name."[".($numrow-1)."]\" ";

				if(!strstr($this->format,"readonly")) print "onkeypress=\"javascript: return checknumeric(event)\" ";

				print " type=text maxlength=\"".$this->maxlength."\" value=\"".$this->val."\" size=".$this->size." $format/>";
				break;
			case "string":
				print "<input name=\"".$this->name."[".($numrow-1)."]\" type=\"text\" maxlength=\"".$this->maxlength."\" value=\"".$this->val."\" size=".$this->size." $format/>";
				break;
			case "email":
				print "<input name=\"".$this->name."[".($numrow-1)."]\" type=\"email\" maxlength=\"".$this->maxlength."\" value=\"".$this->val."\" size=".$this->size." $format/>";
				break;
			case "image":
				print "<input type=\"file\" name=\"".$this->name."[".($numrow-1)."]\" style='width:170px'/>";
				break;

			case "text":

				if(!$this->cols) $this->cols=20;
				if(!$this->rows) $this->rows=2;
				print "<textarea $this->format name=\"".$this->name."[".($numrow-1)."]\" cols=".$this->cols." rows=".$this->rows." maxlength=\"".$this->maxlength."\">".str_replace("<br />","\r\n",$this->val)."</textarea>";


				break;

			case "file":
				print "<input type=\"file\" name=\"".$this->name."[]\" style='width:170px'/>";
				break;

			case "preview":
				if(strlen($r[$name])>0) print "<img src=\"$img_url"."?id=$r[0]&table=".$this->table->table."&dbname=".$dbname."\" border=0><br>";
				break;

			case "dots":
				print "<nobr>";
				if($this->val) print dots($this->val).$format;
				else print "0".$format;
				break;
			case "money":
				print "<nobr>";
				if($this->val) print $format.dots($this->val);
				elseif(strlen($this->val)) print $format."0";
				break;

			case "report":
				print "<a href=\"$site_url"."admin/adm_games.php?id=".$r[0]."\"><img src=\"$img_path"."icons/report.gif\" width=17px height=16px border=0 alt=\"".message(edit,"Редактировать")."\" title=\"".message(edit,"Редактировать")."\"/></a>";
				break;
			case "golden":
				print "<nobr>";
				print dots($this->val).$format.$gd;
				break;
			case "edit":
				print "<a href=\"$PHP_SELF?act=update&id=$r[0]&type=".($this->table->type)."\"><img src=\"$img_path"."engine/edit.png\" width=16 height=16 border=0 alt=\"".message(edit,"Редактировать")."\" title=\"".message(edit,"Редактировать")."\"/></a>";
				break;
			case "delete":
				print "<a href=\"$PHP_SELF?act=delete&id=$r[0]&step=1&type=".($this->table->type)."\" onclick=\"return confirm('".message(areushure,"Вы уверены? Запись будет удалена")."')\"><img src=\"$img_path"."engine/drop.png\" width=16 height=16 border=0 alt=\"".message(delete,"Удалить")."\" title=\"".message(delete,"Удалить")."\"/></a>";
				break;

			case "up":
				print "<a href=\"$PHP_SELF?act=up&id=$r[0]&step=1&type=".($this->table->type)."\"><img src=\"$img_path"."engine/up1.gif\" width=12px height=12px border=0  /></a>";
				break;
			case "down":
				print "<a href=\"$PHP_SELF?act=down&id=$r[0]&step=1&type=".($this->table->type)."\"><img src=\"$img_path"."engine/down1.gif\" width=12px height=12px border=0 /></a>";
				break;

			case "remove":
				print "<a href=\"$PHP_SELF?act=remove&id=$r[0]&step=1&type=".($this->table->type)."\" onclick=\"return confirm('".message(areushure,"Вы уверены? Запись будет удалена")."')\"><img src=\"$img_path"."engine/drop.png\" width=16px height=16px border=0 alt=\"".message(delete,"Удалить")."\" title=\"".message(delete,"Удалить")."\"/></a>";
				break;

			case "checkall":
			case "checkbox":
				print "<input class=\"checkbox\" type=\"checkbox\" name=\"".$this->name."[".($numrow-1)."]\"";
				if($r[$this->name]==1) print " checked";
				print "/>";
				break;
			case "radio":


				if($this->vals) $this->val=set_params($this->vals);

				print "<input  class=\"radio\" type=\"radio\" name=\"".$this->name."\"";
				print " value='$this->val'";

				if($this->val==$this->text) print " checked=1";

				print "/>";
				break;
			case "list":
				print "<select name=\"".$this->name."[]\">";

				if(!strlen($this->text))
				{
					print "<option value=\"$this->default\">-";
					print "</option>";
				}

				$ar=explode(";",$this->vars);
				$n=0;
				foreach($ar as $v)
				{
					print "<option value=\"$n\"";
					if($r[$this->name]==$n||($this->text==$v&&!$r[$this->name])) print " selected";
					print ">$v</option>\n";
					$n++;
				}

				print "</select>\n";
				break;
			case "2dreport":
				if($r[Finished]) print "<a href=\"$site_url"."xml/tour/getmatch.php?id=".set_params("\$MatchID;")."\" target=_blank><img src=\"$img_path"."engine/goal.gif\" width=15px height=15px border=0/></a>";
				break;
			case "listlabel":
				listlabelRender($this);
				break;
			case "sqlist":
				$res=runsql($this->sql);
				if(mysql_num_rows($res))
				{
					print "<select $required name=\"".$this->name."[]\">\n";
					if(!strlen($this->text))
					{
						print "<option value=\"$this->default\">-";
						print "</option>";
					}

					$n=0;
					while($r1=mysql_fetch_array($res))
					{

						print "<option value=\"$r1[0]\"";
						if($r1[2]) print " style='background-color:$r1[2]' ";
						if(($this->val==$r1[0]&&$this->val)||$r[$this->name]==$r1[0]||(($this->text==$r1[1]||$this->text=="$n")&&!$r[$this->name])) print " selected";
						print ">";
						print "$r1[1]</option>\n";
						$n++;
					}

					print "</select>\n";
				}
				else print "<input type=\"hidden\" name=\"$this->name[".($numrow-1)."]\"/>";

				break;
			case 'hidden':
				print "<input type=\"hidden\" name=\"$this->name[".($numrow-1)."]\" $format value=\"$this->val\"/>";
				break;
			default:
				$render = self::$renders[$this->type];
				if ($render)
					$render($this,$r);
				else
					print $format.stripslashes($r[$name]);
				break;
		}
	}

	function DrawRow($r,$numrow)
	{
		global $img_path,$gladtypenames,$type,$act,$site_path,$img_url,$site_url,$r,$gd,$db,$number,$_POST,$auth,$dbname,$img_url,$lang;

		if($this->default)
			$this->val=$this->default;

		if($r[$this->name])
			$this->val=$r[$this->name];
		elseif($_POST[$this->name])
			$this->val=$_POST[$this->name];
		elseif($this->text)
			$this->val=$this->text;

		if(is_Array($this->val))
			$this->val=$this->val[$numrow];

		$name=$this->name;
		if(strlen($r[$name]))
			$this->val=$r[$name];

		if(strstr($this->format,"&lt")||strstr($this->format,"&gt"))
			$this->format=settags($this->format);
		if(strstr($this->format,"\$"))
			$format=set_params($this->format);
		else
			$format=$this->format;

		$this->drawFieldHeader();
		$this->drawField($r,$numrow,$name,$format);
		$this->drawFieldFooter();

		$this->value=$this->val;
		unset($this->val);
	}

}


function cut_end_word($word){
//функция отрезания окончания слова, минимальная длина слова 4 символа, если меньше, то не отрезаем
	if (strlen($word)>=6) {
		//удалим окончания прилагательных, существительных
		# $word=preg_replace('/(.*)(?:у|е|ы|а|о|э|я|и|ю|ого|ому|ая|ое|ую|ой|ым)$/u', "\1",$word);
		if (preg_match('/(.*)(?:ого|ому|ему|ая|ое|яя|ую|ой|ым|им)$/u', $word, $m)) {
			$word=$m[1];
		}
		else if (preg_match('/(.*)(?:у|е|ы|а|о|э|я|и|ю)$/u', $word, $m)) {
			$word=$m[1];
		}
	}
	elseif (strlen($word)==5) {
		//удалим окончания прилагательных женского рода, существительных
		if (preg_match('/(.*)(?:ая|ое|ую|яя|ой|ым|им)$/u', $word, $m)) {
			$word=$m[1];
		}
		else if (preg_match('/(.*)(?:у|е|ы|а|о|э|я|и|ю)$/u', $word, $m)) {
			$word=$m[1];
		}
	}
	elseif (strlen($word)==4) {
		//удалим окончания существительных
		if (preg_match('/(.*)(?:у|е|ы|а|о|э|я|и|ю)$/u', $word, $m)) {
			$word=$m[1];
		}
	}
	return $word;
}


function WordLength($value)
{
	return strlen($value);
}

function GetWordMaxLength($text)
{
	$text=str_replace("<br />","\r\n",$text);
	$text=strip_tags($text);

	$words=split("[\n\r\t ]+",$text);

	$lengths = array_map("WordLength", $words);

	return max($lengths);

}

function callbackPrettyURL($value)
{
	global $cfg,$gl_max_length,$gl_awaymode;
	$max_length = $gl_max_length;
	$ellipse_ratio = 2.0/3.0;
	$url = $value[0];
	$name = $url;
	if ($max_length && strlen($name)>$max_length)
		$name = substr($name,0,max(6,$max_length)*$ellipse_ratio-2).'...'.substr($name,-max(6,$max_length)*(1-$ellipse_ratio)+1);
	$isPrefixed=false;
	$prefixes=Array('http://','https://','ftp://');
	foreach ($prefixes as $prefix)
		$isPrefixed=$isPrefixed || (strncasecmp($url,$prefix,strlen($prefix))==0);
	if (!$isPrefixed)
		$url = "http://".$url;
	$domain = parse_url($url,PHP_URL_HOST);
	if ($gl_awaymode && !in_array($domain,$cfg['GLOBAL']['safe_domains']) )
		return "<A HREF=\"javascript:showModalAway('$url')\">$name</A>";
	else
		return "<A HREF=\"$url\">$name</A>";
}

function PrettyURL($text,$maxlength=40,$awaymode=true)
{
	global $cfg,$gl_max_length,$gl_awaymode;

	$maxlength = ($maxlength!='') ? $maxlength : 40 ;
	$gl_max_length = $maxlength;
	$gl_awaymode = $awaymode;

	$text = str_replace("<br />","\r\n",$text);
	$text = strip_tags($text);

	if (!$cfg['GLOBAL']['root']['no_email'])
		$text = preg_replace('=([^\s]*)\@(.*)\.([^\s]*)=u','<a href=mailto:\\1@\\2.\\3>\\1@\\2.\\3</a>',$text);
	$text = preg_replace_callback(System::urlPattern,'callbackPrettyURL',$text);
	$text = str_replace(" &lt;span&gt;union&lt;/span&gt; "," union ",$text);
	$text = str_replace(" &lt;span&gt;join&lt;/span&gt; "," join ",$text);

	return str_replace("\r\n","<br />",$text);
}

function pageOnCreate($pageSite,$strictAccess)
{
	global $PHP_SELF,$QUERY_STRING,$cfg,$site,$auth,$site_path,$act,$type,$form,$form_result,$newact,$step,$runsql,$er,$db,$form_ok,$id;
	global $page_title,$form_title,$user;

	if ($strictAccess)
		if (!System::isIPInArray($_SERVER['REMOTE_ADDR'],$cfg['GLOBAL']['whitelist_ip']))
		{
			$userIP = $_SERVER['REMOTE_ADDR'];
			msg_var(userIP,$userIP);

			print message("bannedip1","Ваш IP ($userIP) не разрешен");
			exit;
		}

	if ( $cfg['GLOBAL']['lock'])
	{
		if (System::isIPInArray($_SERVER['REMOTE_ADDR'],$cfg['GLOBAL']['whitelist_ip']))
		{
			$cfg['GLOBAL']['lock_message']=file_get_contents($site_path.'config/lock');
			if (trim($cfg['GLOBAL']['lock_message']==''))
				$cfg['GLOBAL']['lock_message']='Site closed';
			echo $cfg['GLOBAL']['lock_message'];
		}
	}

	if($auth->user)
		touch($site_path."files/logon/".$auth->user);

	if($auth->user)
	{
		if(!$user)
			$user=$auth->user;
		$auth->checkBanned();
	}

	$site=$pageSite;

	if (!$auth->getBanned())
	{

		if ($type&&$act)
		{
			$form = new cls_form($type,$act);
			$form_result=null;

			if ($step&&strstr($form->act,"search"))
			{
				$searchform=$form;

				if($newact) $form = new cls_form($type,$newact);
				else $form = new cls_form($type,'select');

				$form->sql=$searchform->sql;
				$form->vip=$searchform->vip;

				$form->getrows();

				$searchform=null;
				$step=null;
			}

			if($step&&!$form->sql)
				$form->sql=$form->select;

			$runsql=null;

			if ($step)
			{
				$form_result=$form->runsql(1);
				$runsql=1;
				if(!$er&&$form->action)
					$form=new cls_form($type,$act);
			}

			if($form_ok==1)
				$form_result=icon('ok',set_params($form->success));

			if($form->redirect)
				$form->redirect=set_params($form->redirect);

			if($step&&!$er&&$form->redirect&&$form_result)
			{
				if(strstr($form->redirect,"?"))
					System::redirect("$form->redirect&form_ok=1");
				else
					System::redirect("$form->redirect?form_ok=1");
			}
			elseif($step&&!$er&&$form_result)
			{
				if(strstr($QUERY_STRING,"step"))
					$QUERY_STRING=str_replace("step","st",$QUERY_STRING);

				if($id)
					$QUERY_STRING.="&id=$id";

				System::redirect("$PHP_SELF?$QUERY_STRING&form_ok=1");
			}
		}

		if($auth_name&&$auth->user&&$url)
			System::redirect($url);
	}

	if ($form_title) $page_title=$form_title;
	if ($form->title) $page_title=$form->title;
}

function drawButton($caption,$action='validateForm(this)',$enabled=true,$style='',$options=Array())
{
	$class = $enabled ? 'ui-button' : 'ui-button-disabled' ;
	$action = $enabled ? $action : '' ;

	$defaultOptions = Array();
	$defaultOptions['DivStyle'] = 'padding-top:4px; padding-left:4px; float: left;';
	$defaultOptions['DivClass'] = '';

	$options = array_merge($defaultOptions,$options);

	$divStyle = $options['DivStyle'];
	$divClass = $options['DivClass'];

	print "<div class='$divClass' style='$divStyle'><table cellpadding=\"0\" class=\"$class\" cellspacing=\"0\" border=\"0\" onmouseover=\"if(this.className=='ui-button-disabled') return; this.className='ui-button-hover'\" onmouseout=\"if(this.className=='ui-button-disabled') return; this.className='ui-button'\" onclick=\"$action\" style=\"$style\">
	<tr>
		<td id='left'>&nbsp;</td>
		<td id='center'>$caption</td>
		<td id='right'>&nbsp;</td>
	</tr>
</table></div>";
}

function countCharsWrap($elem, $name, $maxlength)
{
	print "<script>
		\$(function(){
				countChars('[name=$name]');
			});
		</script>
		<div class=inputWrap>
			$elem
			<div class=charsBlock_wrap>
				<p class=charsBlock>Символов осталось: <span class=remainingCharacters>$maxlength</span></p>
			</div>
		</div>
	";
}

?>
