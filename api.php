<?php
if(isset($_GET)){
	$commands = array(
		'pm' => '{JAR_DIR}/bin/php7/bin/php {JAR} --memory-limit={MAX_MEMORY}M',
		'nukkit' => '"{JAVA}" -Xmx{MAX_MEMORY}M -Xms{START_MEMORY}M -Djline.terminal=jline.UnsupportedTerminal -jar "{JAR}" nogui',
		'bukkit' => '"{JAVA}" -Xmx{MAX_MEMORY}M -Xms{START_MEMORY}M -Djline.terminal=jline.UnsupportedTerminal -jar "{JAR}" nogui',
		'spigot' => '"{JAVA}" -server -Xmx{MAX_MEMORY}M -Xms{START_MEMORY}M -Djline.terminal=jline.UnsupportedTerminal -XX:+UseConcMarkSweepGC -XX:+UseParNewGC -XX:+CMSIncrementalPacing -XX:ParallelGCThreads=2 -XX:+AggressiveOpts -Xincgc -jar "{JAR}" nogui',
		'chunkster' => '"{JAVA}" -Xmx{MAX_MEMORY}M -Xms{START_MEMORY}M -jar "{JAR}" "{WORLD}"',
		'minecraft_optimized' => '"{JAVA}" -server -Xmx{MAX_MEMORY}M -Xms{START_MEMORY}M -Djline.terminal=jline.UnsupportedTerminal -XX:+UseConcMarkSweepGC -XX:+UseParNewGC -XX:+CMSIncrementalPacing -XX:ParallelGCThreads=2 -XX:+AggressiveOpts -Xincgc -jar "{JAR}" nogui',
		'minecraft_server' => '"{JAVA}" -Xmx{MAX_MEMORY}M -Xms{START_MEMORY}M -Djline.terminal=jline.UnsupportedTerminal -jar "{JAR}" nogui',
	); //启动参数列表
	if($_GET['mode']=='upload'){  //上传模式
		$id = md5($_FILES["file"]["name"].microtime());
		mkdir('cache/'.$id);
		copy($_FILES["file"]["tmp_name"], 'cache/'.$id.'/'.$_FILES["file"]["name"]);
		echo json_encode(array('id'=>$id));
	} elseif($_GET['mode']=='getconf'){ //下载模式
		if(!isset($_GET['id'])){
			if($_GET['jartype'] == 'other'){  //手动模式
				$info = array(
					'command' => $_GET['startval'],
					'name' => $_GET['jarname'],
					'decode' => 'system',
				);
			} else { //半自动模式
				$info = array(
					'command' => $commands[$_GET['jartype']],
					'name' => $_GET['jarname'],
					'decode' => (($_GET['jartype'])=='pm'?'utf-8':'system'),
				);
			}
			$filename = $_GET['filename'];
		} else { //全自动模式
			$file = glob('cache/'.$_GET['id'].'/*');
			foreach($file as $one){
				if(preg_match('/(\.jar|\.phar)$/',$one)){
					$file = $one;
					break;
				}
			}
			if(preg_match('/\.phar$/', basename($file))){
				$info = array(
					'command' => $commands['pm'],
					'name' => $_GET['jarname'],
					'decode' => 'utf-8',
				);
			} elseif(preg_match('/\.jar$/', basename($file))) {
				$packages = array(
					'cn.nukkit.Nukkit' => 'nukkit',
					'org.bukkit.craftbukkit.Main' => 'bukkit',
					'net.minecraft.server.MinecraftServer' => 'minecraft_server',
					'cpw.mods.fml.relauncher.ServerLaunchWrapper' => 'bukkit',
				);
				if(!file_exists('cache/'.$_GET['id'].'/META-INF/MANIFEST.MF')){
					$zip = new ZipArchive();
					if($zip->open($file)==false){
						exit();
					}
					$zip->extractTo('cache/'.$_GET['id'].'/',array('META-INF/MANIFEST.MF'));
					$zip->close();
				}
				$manifest = file_get_contents('cache/'.$_GET['id'].'/META-INF/MANIFEST.MF');
				$temp = explode("\n", $manifest);
				$manifest = array();
				foreach($temp as $one){
					if(trim($one) != ''){
						$one = explode(':', $one);
						$manifest[strtolower(trim($one[0]))] = trim($one[1]);
					}
				}
				unset($temp);
				$type = isset($packages[$manifest['main-class']])?$packages[$manifest['main-class']]:'bukkit';
				$info = array(
					'command' => $commands[$type],
					'name' => $_GET['jarname'],
					'decode' => 'system',
				);
			}
			$filename = basename($file);
		}
		$conf = file_get_contents('files/default.conf');
		foreach(array_keys($info) as $key){
			$conf = str_replace('%'.$key.'%',$info[$key],$conf);
		}
		header('Content-Type:text/plain'); //发送指定文件MIME类型的头信息
		header('Content-Disposition:attachment; filename="'.$filename.'.conf"'); //发送描述文件的头信息，附件和文件名
		header('Content-Length:'.strlen($conf)); //发送指定文件大小的信息，单位字节
		echo $conf;
	} elseif($_GET['mode']=='clear'){
		deldir('cache');
		mkdir('cache');
		echo 'Clear Cron Ok!';
	}
} else {
	header('Location: .');
}
