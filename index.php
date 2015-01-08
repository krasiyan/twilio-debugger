<?php
	error_reporting(E_ALL);
	// error_reporting(0);
	
	$sid = "SID";
	$token = "TOKEN";
	$last_sid_container = 'last_sid.txt';
	$max_log_count = 50;
	$mail = 'receiver_email@email';
	$mail_topic = "Twilio errors";
	$autorefresh_seconds = 10;
	$logfile = 'logs.txt';
	
	$log_levels = array('ERROR', 'WARNING', '', 'ACCOUNT BALLANCE');
	require_once('twilio_errors.php');
	
	require_once('vendor/twilio/sdk/Services/Twilio.php');
	$client = new Services_Twilio($sid, $token, '2010-04-01');
	
	$last_sid = file_get_contents($last_sid_container);
	$filter_loglevel = "";
	
	if(php_sapi_name() == 'cli'){
		if(isset($argv[1])){
			if($argv[1] != '-1')
				$filter_loglevel = $argv[1];
			if(isset($argv[2])){
				$last_sid = $argv[2];
			}
		}
	}
	else {
		if(isset($_GET['loglevel']) and $_GET['loglevel'])
			$filter_loglevel = $_GET['loglevel'];
		if(isset($_GET['lastsid']) and $_GET['lastsid'])
			$last_sid = $_GET['lastsid'];
	}
	
	$notifications = $client->account->notifications;
	ob_start();
?>
	<style type="text/css">
		table, th, td {
			width: 100%;
		}
	</style>
	<table border="1" style="border-collapse:collapse;">
		<thead>
			<th>SID</th>
			<th>Date</th>
			<th>Call</th>
			<th>Level</th>
			<th>Error</th>
			<th>URL</th>
			<th>Method</th>
			<th>Request variables</th>
			<th>Message</th>
		</thead>
		<tbody>
			<?php
				$first_iteration = TRUE;
				$log_count = 0;
				foreach($notifications as $notification):
					if($first_iteration){
						$new_last_sid = $notification->sid;
						$first_iteration = FALSE;
					}
					
					if($notification->sid == $last_sid) break;
						
					if(($filter_loglevel and $notification->log==$filter_loglevel) or !$filter_loglevel):
						$log_count += 1;
						if($log_count == $max_log_count) break;	
			?>
				<tr>
					<td><?=$notification->sid ?></td>
					<td><?=substr($notification->date_created,0,-5) ?></td>
					<td><?=$notification->call_sid ?></td>
					<td><?=$notification->log . " - " . $log_levels[$notification->log] ?></td>
					<td><?=$notification->error_code ? $notification->error_code . " - " . $twilio_errors[$notification->error_code] : "unknown" ?></td>
					<td>
						<?php
							if($notification->request_url) echo $notification->request_url;
							else{
								$message_array = array();
								parse_str($notification->message_text, $message_array);
								if(isset($message_array['url']))
									echo urldecode($message_array['url']);
								else
									echo 'unknown';
							}
						?>
					</td>
					<td><?=$notification->request_method ?: "N/A" ?></td>
					<td><?= implode('<br/>', array_map('urldecode', explode('&',$notification->request_variables))) ?: "none" ?></td>
					<td><?= implode('<br/>', array_map('urldecode', explode('&',$notification->message_text))) ?: "none" ?></td>
				</tr>
			<?php
				endif;
				endforeach;
			?>
		</tbody>
	</table>
<?php
	$output = ob_get_contents(); ob_end_clean();
	
	if(php_sapi_name() == 'cli'){
		if($log_count) {
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			mail($mail, $mail_topic, $output, $headers);
			
			$logdata = "[" . date("Y-m-d H:i:s") . "]" . " Sent " . $log_count . " logs to " . $mail . ". The old stopper SID was " . ($last_sid ?: "UNKNOWN") . ". The new stopper SID is " . $new_last_sid . ".\n";
			//echo $logdata;
			file_put_contents($logfile, $logdata, FILE_APPEND);
			
			file_put_contents($last_sid_container, $new_last_sid);
		}
		else {
			$logdata = "[" . date("Y-m-d H:i:s") . "]" . " Nothing new to send! The current stopper SID is " . ($last_sid ?: "UNKNOWN") . ".\n";
			//echo $logdata;
			file_put_contents($logfile, $logdata, FILE_APPEND);
		}
	}
	else{
		if($log_count) echo $output;
		else echo 'nothing new here...';
		echo '<script>setTimeout("location.reload(true);",'.($autorefresh_seconds*1000).');</script>';
	}
	
?>