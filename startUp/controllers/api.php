<?php

namespace Harm;

class Api
{

	public function index()
	{
            $params = [];
            $request = explode('/', $_SERVER['REQUEST_URI']);

            foreach (array_reverse($request) as $part) {
                if (trim($part) === '') {
                    continue;
                }
                if (method_exists($this, $part)) {
                    return call_user_func_array([$this, $part], $params);
                }
                array_unshift($params, $part);
            }
	}

	public function record()
	{
		file_put_contents(HARM_START_UP_FILES_PATH . '/record', 'ON');
	}

	public function stop()
	{
		file_put_contents(HARM_START_UP_FILES_PATH . '/record', 'OFF');
	}

	public function get_latest()
	{
		$dir = realpath(HARM_START_UP_FILES_PATH . '/export/');

		if ( ! $dir || ! is_dir($dir) ) {
			return;
		}

		$dir_read = dir($dir);

		echo '[';
		$glue = '';
		while ($file = $dir_read->read()) {
			if (in_array($file, ['.', '..'])) {
				continue;
			}			
			if (is_file($dir.'/'.$file)) {
				echo $glue;
				$echo = str_replace('\\', '\\\\', file_get_contents($dir.'/'.$file));
				if (is_null(json_decode("[1,$echo,1]"))) {
					$echo = '"JSON_ERROR('.str_replace('"', '&quot;', $echo).')"';
				}
				echo $echo;
				unlink($dir.'/'.$file);
				$glue = ',';
			}
		}
		echo ']';

		if (!empty($_GET['delete_old'])) {
			$this->delete_old();
		}
	}

	public function reference( $id ) {
		$file = realpath(HARM_START_UP_FILES_PATH . '/export/references/'.$id);
		if (file_exists($file)) {
			echo '{"data":';
			echo file_get_contents($file);
			echo '}';
		} else {
			echo '{}';
		}
	}

	public function get_latest_old()
	{
		if (file_exists(HARM_START_UP_FILES_PATH . '/recorded')) {
			$file = fopen(HARM_START_UP_FILES_PATH . '/recorded', 'r+');
			if (flock($file, LOCK_EX) && filesize(HARM_START_UP_FILES_PATH . '/recorded')) {
				echo fread($file, filesize(HARM_START_UP_FILES_PATH . '/recorded'));
				ftruncate($file, 0);
				flock($file, LOCK_UN);
			}
			fclose($file);
		}
	}

	public function delete_all()
	{
		$dir = realpath(HARM_START_UP_FILES_PATH . '/export');

		if ( ! $dir || ! is_dir($dir) ) {
			return;
		}
                $command = "cd $dir; rm ./*;";
                `$command`;
                
		$dir = realpath(HARM_START_UP_FILES_PATH . '/export/references');

		if ( ! $dir || ! is_dir($dir) ) {
			return;
		}
                $command = "cd $dir; rm ./*;";
                `$command`;
	}

	private function delete_old()
	{
		$dir = realpath(HARM_START_UP_FILES_PATH . '/export/references');

		if ( ! $dir || ! is_dir($dir) ) {
			return;
		}

		$dir_read = dir($dir);
		$now = time();
		while ($file = $dir_read->read()) {
			if (in_array($file, ['.', '..'])) {
				continue;
			}
			if (is_file($dir.'/'.$file)) {
				$time = (int) substr($file, 0, 10);
				if ($now - $time > 3600) {
					unlink($dir.'/'.$file);
				}
			}
		}
	}

	public function open_netbeans()
	{
		if (!isset($_GET['file'])) {
			return;
		}
		$file = $_GET['file'];
		$notubiz_base = 'c:\\data\\notubiz\\';
		$trigger_base = "@ECHO OFF\n".'"c:\Program Files\NetBeans 8.2\bin\netbeans.exe" --open ';
		$trigger_end = " --console suppress";
		$trigger_file = '/media/sf_utils/netbeans.bat';
		$matches = [];
		$test = trim( (string) $file );
		if (preg_match('/^\/media\/sf_notubiz\/(.*)$/',$test, $matches )) {
			$targetfile = str_replace('/','\\', (string) $matches[1]);
			$trigger = $trigger_base.'"'.$notubiz_base.$targetfile.'"'.$trigger_end;
			file_put_contents($trigger_file, $trigger);
		}
	}

}

