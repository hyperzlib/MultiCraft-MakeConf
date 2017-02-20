<?php

$page = array();
$page['title'] = '生成Conf';
include('head.php');
?>
<form method="post" class="form-horizontal" enctype="multipart/form-data" id="uploadform" onsubmit="return false;">
	<div class="form-inline">
		<label for="file">文件名:</label>
		<input id="lefile" type="file" name="file" style="display:none">
		<input id="filename" name="filename" class="form-control" type="text" style="height:30px;"> 
		<a class="btn btn-zan-solid-pi btn-small" onclick="$('#lefile').click();">选择文件</a> 
		<button id="submit" class="btn btn-zan-solid-pi btn-small" style="display: none;">上传</button>
		<button class="btn btn-zan-solid-pi btn-small" id="showmainform">手动输入数据</button>
	</div><br />
	<div class="progress" id="progressdiv" style="display: none;">
		<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%" id="progress">
			<span class="sr-only"></span>
		</div>
	</div>
	<script type="text/javascript">
	var progressbar=$('#progress');
	
	var filename;
	var jar;
	var fileinfo;
	
	$('#showmainform').click(function(){
		$('#uploadform').hide(1000);
		$('#otherinfo').show();
		$('#mainform').show(2000);
	});
	
	$('#lefile').change(function() {
		filename = $(this).val();
		$('#filename').val(filename.match(/[^\/\\\\]+$/)); 
		$('#submit').show();
	});
	
	function onprogress(evt){
		var loaded = evt.loaded;	//已经上传大小情况  
		var tot = evt.total;		//附件总大小  
		var per = Math.floor(100*loaded/tot);  //已经上传的百分比
		console.log(per);
		progressbar.attr('aria-valuenow',per);
		progressbar.attr('style','width: '+per+'%');
	}

	
	$('#submit').click(function(){
		var formdata = new FormData();
		formdata.append("file",$("#lefile")[0].files[0]);
		$('#submit').text("上传中……");
		$('#submit').attr({"disabled":"disabled"});
		$('#progressdiv').show();
		var request = $.ajax({
			type: "POST",
			url: "api.php?mode=upload",
			data: formdata,			//这里上传的数据使用了formData 对象
			processData : false, 	//必须false才会自动加上正确的Content-Type
			contentType : false,
			
			//这里我们先拿到jQuery产生的XMLHttpRequest对象，为其增加 progress 事件绑定，然后再返回交给ajax使用
			xhr: function(){
				var xhr = $.ajaxSettings.xhr();
				if(onprogress && xhr.upload) {
					xhr.upload.addEventListener("progress" , onprogress, false);　
					return xhr;
				}
			},
			
			//上传成功后回调
			success: function(result){
				console.log(result);
				$('#submit').text("成功");
				$('#showmainform').hide();
				$('#mainform').show(2000);
				eval('fileinfo='+result);
			},
			
			//上传失败后回调
			error: function(){
				$('#submit').removeAttr("disabled");
				$('#submit').text("重传");
			}
				
			});
	});
	</script> 
	<br />
</form>
<form class="form-horizontal" id="mainform" style="display: none;" onsubmit="return false;">
	<div style="display: none;" id="otherinfo">
		<button class="btn btn-zan-solid-pi btn-small" id="showuploadform">自动生成</button><br />
		<label for="jartype">核心类型:</label>
		<select class="form-control" id="jartype" name="jartype">
		  <option value="pm">PocketMine-MP系列</option>
		  <option value="spigot">Spigot</option>
		  <option value="bukkit">CraftBukkit</option>
		  <option value="nukkit">Nukkit</option>
		  <option value="chunkster">Chunkster</option>
		  <option value="minecraft_optimized">Minecraft Optimized</option>
		  <option value="minecraft_server">Minecraft Server</option>
		  <option value="other">自定义启动参数</option>
		</select>
		<div style="display: none;" id="startvaldiv">
			<label for="startval">自定义启动参数:</label>
			<input id="startval" name="startval" class="form-control" type="text" style="height:30px;"></input>
		</div>
		<label for="filename">核心文件名:</label>
		<input id="filename" name="filename" class="form-control" type="text" style="height:30px;"></input>
	</div>
	<script language="javascript">
	$(function(){
		$('#showuploadform').click(function(){
			$('#mainform').hide(1000);
			$('#otherinfo').hide(1000);
			$('#uploadform').show(1000);
		});
	});
	$('#jartype').change(function(){
		console.log($('#jartype').val());
		if(($('#jartype').val()) == 'other'){
			$('#startvaldiv').show(1000);
		} else {
			$('#startvaldiv').hide(1000);
		}
	});
	</script>
	<label for="jarname">核心显示名:</label>
	<input id="jarname" name="jarname" class="form-control" type="text" style="height:30px;"></input>
	<br />
	<button id="getconf" class="btn btn-zan-solid-pi btn-small">获取Conf文件</button>
	<script language="javascript">
	$('#getconf').click(function(){
		var query;
		if(typeof(fileinfo)=='undefined'){
			query = $('#mainform').serialize();
		} else {
			query = 'id='+fileinfo.id+'&jarname='+$('#jarname').val();
		}
		console.log(query);
		window.location.href='api.php?mode=getconf&'+query;
	});
	console.log('done');
	</script>
</form>
<?php
include('foot.php');