<?php
/**
 * Printemps Exception
 *
 * @package Printemps
 * @subpackage Expception
 * @version  2
 * @author   kirainmoe<kirainmoe@gmail.com>
 * @link     https://github.com/kirainmoe/Printemps/
 */
class Printemps_Exception extends Exception
{
	/**
	 * display detail about exception and halt
	 * 
	 * @param  boolean $godie exit application
	 * @return void
	 */
	public static function halt()
	{
		echo "<html>\n<head>\n<meta charset=\"UTF-8\">\n<title>An error has been detected.</title>\n";
		echo "
		<style>
			body{
				margin:0px auto;
				background-color: #8BC34A;
				color:#fff;
				font-family:Roboto,Monaco,Microsoft Yahei,WenQuanYi Micro Hei;
				display: flex;
				flex-wrap: wrap;
				text-align: left;			
			}
			.printemps-fatal-error-container{
				margin: auto;
				width: 80%;
			}
			.printemps-fatal-error-container > p{
				text-align: center;
			}
			.printemps-oh{
				text-align:center;
				font-size: 50px;
			}
			.printemps-fatal-error{
				padding:5px 10px;
				background-color:#388E3C;
				margin: 10px 0px;
			}
			.printemps-summary{
				color: rgb(245,90,100);
			}
			.this-line{
				color: #FFEB3B;
			}
			.powered-by{
				font-family: Consolas, Roboto, Monaco;
				font-style: italic;
				text-align: center;
			}
			pre{
				font-family: Consolas;
			}
			pre > code{
				font-family: Consolas;
				font-style: italic;
			}
		</style>\n";
		echo "<body>\n<div class=\"printemps-fatal-error-container\">\n";
		echo "<h1 class=\"printemps-oh\">Oh...</h1>\n";
		echo "<p>Something unusual has happened and application could not continue.</p>\n";
		if( APP_DEBUG_MODE )
		{
			$trace = debug_backtrace();
			$args = func_get_args();	

			echo "<p>What I can do is to help you to trace and find out where is the error.</p>";
			if(isset($trace[0]['args'][1]))		$summary = $trace[0]['args'][1];
			elseif(isset($args[0]))						$summary = $args[0];

			echo empty($summary) ? "" : "<p>Error reported that: <b class=\"printemps-summary\"> $summary </b></p>\n";

			foreach($trace as $key => $value)
			{
				if( !isset($value['file']) )
					continue;

				$errfile = $value['file'];
				$errline = $value['line'];
				$errfunc = '';

				/* get error function called from */

				if(isset($value['class']))
					$errfunc .= $value['class'] . $value['type'] . $value['function'] . "()";
				elseif(isset($value['function']))
					$errfunc .= $value['function'] . "();";
				else
					$errfunc .= "somewhere";

				/* get error code content */
				$files = file_get_contents($errfile);
				$line = explode("\n", $files);
				$allleng = count($line);
			//$content = $line[$errline-1];
				echo "<div class=\"printemps-fatal-error\" stack=\"" . $key . "\">\n";
				echo "<p>#$key : called $errfunc at $errfile, line $errline</p>\n";
				for ($i = $errline - 4; $i < $errline + 2; $i++)
				{
					if($i > 0 && $i < $allleng)
					{
						$l = $i+1;
						$msg = "<pre>[" .$l."] <code>" . str_replace("\t","",$line[$i]) . "</code></pre>";

						if($i == $errline - 1)
							echo "<b class=\"this-line\">" . $msg . "</b>";
						else
							echo $msg;					
					}
				}
				echo "</div>\n";
			}
			echo "<p>This error was caught at " . date("Y-m-d H:i:s",time()) . " while application <i>" . APP_NAME . " " . APP_VERSION . "</i> is running. Error log has been recorded by Printemps Logcat.</p>\n";
			echo "<p>Unfortunately.</p>\n";
			echo "<p class=\"powered-by\">Powered by Printemps 2.0 - A light and beautiful PHP Framework.</p>";
			die();
		}
		else
		{
			echo "<p>Try to access here few minutes later, maybe it will be fixed.</p>\n";
			echo "<p>Unfortunately.</p>\n";
			echo "<p class=\"powered-by\">" . APP_NAME . " / " . APP_VERSION . ", Powered by Printemps 2</p>";
		}
		echo "</div>\n</body>\n</html>";
		die();
	}

	public static function notice()
	{
		if( !APP_DEBUG_MODE )
			return;
		$trace = debug_backtrace();
		$args = func_get_args();
		$errline = $trace[0]['line'];
		$errfile = $trace[0]['file'];

		if(isset($trace[0]['args'][1]))
			$errinfo = $trace[0]['args'][1];
		elseif(isset($args[0]))
			$errinfo = $args[0];
		else
			$errinfo = "Unknown error detail";

		$errmsg = "<p>Attention! Caught a error of <b>\"" . $errinfo . "\"</b> at file <b> " . $errfile . "</b>, line <b>" . $errline . " </b>.</p>\n";
		$errmsg .= "<p>but it seems to be less serious now. Anyway, you ought to fix it as possible as you can.</p>\n";

		echo "<div class=\"printemps-notice-error\" caught=\"" . time() . "\" style=\"padding: 5px 10px; background-color: #8BC34A; color: #fff; font-family: Consolas,Monaco ,Roboto , Microsoft Yahei, WenQuanYi Micro Hei; font-style: italic;font-size: 18px;line-height: 0.9;\">\n";
		echo $errmsg;
		echo "</div>\n";
	}
}