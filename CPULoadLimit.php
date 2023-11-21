<?php
	$path_1gb = $_SERVER["SCRIPT_FILENAME"];
	if ($path_1gb == )
		$path_1gb = $_SERVER["PATH_TRANSLATED"];
	$path_1gb = substr( $path_1gb, 0, - strlen($_SERVER['SCRIPT_NAME'])) . '/';
	
	$config_1gb = "$path_1gb/.cpu_limit.conf";

	if( !($cfg_1gb = @file($config_1gb ) ) )
		return;
       
	$logfile_1gb_path = $path_1gb . '/.proclimit_' . strtolower( @md5($path_1gb) );
	
	@mkdir($logfile_1gb_path);
	
	$logfile_1gb = "$logfile_1gb_path/.cpu_limit_".date('Y-m-d').".log";
	$logfile_1gb_debug = "$logfile_1gb_path/.cpu_limit_".date('Y-m-d')."_ok.log";

	$full_time_1gb = 60 * 60 * 1000;

	$ip_1gb = $_SERVER["REMOTE_ADDR"];
	@list($ip_1gb) = @split( ',', $ip_1gb );
	$ip_parts_1gb = @split( '\.', $ip_1gb );
	if (count ($ip_parts_1gb) != 4)
		return;
	$ip_1gb = @intval( $ip_parts_1gb[0] ) << 24;
	$ip_1gb |= @intval( $ip_parts_1gb[1] ) << 16;
 	$ip_1gb |= @intval( $ip_parts_1gb[2] ) << 8;
	$ip_1gb |= @intval( $ip_parts_1gb[3] );

	if( $ip_1gb > 2147483647 )
	{
		// Значит у нас 64-битная система. Нужно получить отрицательное число, как в 32-битной
		$ip_1gb |= 0xFFFFFFFF00000000;
	}

	$host_1gb = @addslashes(@strtoupper($_SERVER["HTTP_HOST"]));
	if( @substr($host_1gb, 0, 4) == "WWW." )
		$host_1gb = substr( $host_1gb, 4 );
	$site_id_1gb = @strtoupper( @md5( $host_1gb ) );
	
	$full_block_1gb = 0.50 * $full_time_1gb;
	$client_block_1gb = 0.50 * $full_time_1gb;
	foreach( $cfg_1gb as $lin1_1gb )
	{
		$lparts_1gb = @split("=", $lin1_1gb);
		if( count( $lparts_1gb) != 2 )
			continue;
		$name_1gb = strtoupper( trim( $lparts_1gb[0] ) );
		$val_1gb = trim( $lparts_1gb[1] );
		
		if( $name_1gb == "FULL_BLOCK" )
			$full_block_1gb = @floatval( $val_1gb ) / 100 * $full_time_1gb;
		if( $name_1gb == "IP_BLOCK" )
			$client_block_1gb = @floatval( $val_1gb ) / 100 * $full_time_1gb;
	}
	
	$con_1gb = @mysql_connect( "127.0.0.1:3399", "user" );
	if( !$con_1gb )
	{
		if( $logfile = @fopen( $logfile_1gb_debug, "at+" ) )
		{
			@fwrite( $logfile, date( "Y-m-d H:i:s" ) . ", accounting database is offline\n" );
			@fclose( $logfile );
		}
		return;	
	}
	@mysql_query( "use ProcLimit;", $con_1gb);

	
	$q_1gb = "SELECT ProcessorTime FROM Summary WHERE Summary.Site_ID = '$site_id_1gb'";
	$res_1gb = @mysql_query( $q_1gb, $con_1gb );
	if( $res_1gb )
		$res_1gb = @mysql_fetch_row( $res_1gb );

	if( $res_1gb )
	{
		$load_from_ip_1gb = @round ($res_1gb[0] * 100 / $full_time_1gb, 2);
		if( $res_1gb[0] >= $full_block_1gb )
		{
			if( $logfile = @fopen( $logfile_1gb, "at+" ) )
			{
				@fwrite( $logfile, date( "Y-m-d H:i:s" ) . ", blocked: $_SERVER[REMOTE_ADDR] ($load_from_ip_1gb %)\n" );
				@fclose( $logfile );
			}
			die( "Сервер перегружен, попробуйте зайти позже" );
		}
	}

				
	$q_1gb = "SELECT ProcessorTime FROM IPSummary WHERE IPSummary.Site_ID = '$site_id_1gb' AND IPSummary.IP = '$ip_1gb'";
	$res_1gb = @mysql_query( $q_1gb, $con_1gb );
	if( $res_1gb )
 		$res_1gb = @mysql_fetch_row( $res_1gb );
	
	if( $res_1gb )
	{
		$load_total_1gb = @round ($res_1gb[0] * 100 / $full_time_1gb, 2);
		if( $res_1gb[0] > $client_block_1gb )
		{
			if( $logfile = @fopen( $logfile_1gb, "at+" ) )
			{
				@fwrite( $logfile, date( "Y-m-d H:i:s" ) . ", blocked: $_SERVER[REMOTE_ADDR] (IP load = $load_total_1gb %)\n" );
				@fclose( $logfile );
			}
			die( "Сервер перегружен, попробуйте зайти позже" );
		}
	}
 
	
	if( $logfile = @fopen( $logfile_1gb_debug, "at+" ) )
	{
		@fwrite( $logfile, date( "Y-m-d H:i:s" ) . ", ok $_SERVER[REMOTE_ADDR] (IP load = $load_total_1gb %, total = $load_from_ip_1gb %)\n" );
		@fclose( $logfile );
	}
	
	
	unset( 
		$path_1gb, $config_gile, $ip_1gb, $host_1gb, $logfile_1gb, $logfile_1gb_debug, $logfile, $cfg_1gb, $full_1gb, $full_block_1gb, $client_block_1gb, 
		$lin1_1gb, $lparts_1gb, $name_1gb, $val_1gb, $q_1gb, $res_1gb, $ip_parts_1gb, $full_time_1gb, $load_from_ip_1gb, $load_total_1gb, $site_id_1gb, $logfile_1gb_path
		);
	
?>
