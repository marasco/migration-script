<?php 

    $commands = "t:r:i:n:c:w:s:";
    $options = getopt($commands);

    function truncate($trunc){
        global $mysql;
        foreach($trunc as $id => $tables){
            foreach($tables as $table){
                print colorize("Note: {$id}.{$table} will be truncated","WARNING");
                $mysql[$id]->query("SET FOREIGN_KEY_CHECKS = 0;");
                $mysql[$id]->query("TRUNCATE " . $table . ";");
                $mysql[$id]->query("SET FOREIGN_KEY_CHECKS = 1;");
            }
        }
    }

    function endscript(){
        global $errors, $options, $mysql, $inserted;

        $inserted = 0;
        $errors = [];   
        $ext = ".php";
        $with = !empty($options['w'])?$options['w']:0;
        $source = str_replace($ext,"",$_SERVER["SCRIPT_NAME"]);
        $target = $source . "_" . $with . $ext;

        if($with){
            if(file_exists($target)){
                include_once $target;
            } else {
                print colorize("\nError: ". $target  . " does not exists","FAILURE");
            }            
        }        

        close_connections();
    }

    function close_connections(){
        global $mysql;

        foreach($mysql as $conn){        
            @$conn->close();
        }
    }

    function open_connections($list){
        global $mysql;

        foreach($list as $id => $conn){
            $link = new mysqli($conn[0],$conn[1],$conn[2],$id);
            if ($link->connect_error) {
                die(colorize("\n" . 'Error : ('. $link->connect_errno .') '. $link->connect_error,"FAILURE"));
            }
            $mysql[$id] = $link;
        }
    }

    function show_status($errors, $inserted, $total){
        $status = "";
        if(count($errors)){
            foreach($errors as $e){
                $status.= colorize("\nError: " . $e,"FAILURE");
            }
        }

        $perc = floor($inserted/$total*100);
        $icon = "👑";
        $msgstatus = "SUCCESS";

        if($perc < 100){
            if($perc > 99){
                $icon = "😄";
            } else if($perc > 90){
                $msgstatus = "WARNING";
                $icon = "😊";
            } else {
                $msgstatus = "FAILURE";
                $icon = "😔";
            }
        }

        $status.= colorize("inserted " . $inserted . " of " . $total . " " . $icon,$msgstatus);
        if($inserted < $total)
        $status.= colorize("success " . $perc . "%",$msgstatus);

        echo $status;
    }

    function show_progress($done, $total, $size=30){
        if ( php_sapi_name() == "cli") {
            show_cli_progress($done, $total, $size);
        }
    }

    function show_cli_progress($done, $total, $size=30) {

        static $start_time;

        // if we go over our bound, just ignore it
        if($done > $total) return;

        if(empty($start_time)) $start_time=time();
        $now = time();

        $perc=(double)($done/$total);

        $bar=floor($perc*$size);

        $status_bar="\r[";
        $status_bar.=str_repeat("=", $bar);
        if($bar<$size){
            $status_bar.=">";
            $status_bar.=str_repeat(" ", $size-$bar);
        } else {
            $status_bar.="=";
        }

        $disp=number_format($perc*100, 0);

        $status_bar.="] $disp%  $done/$total";

        $rate = ($now-$start_time)/($done?:1);
        $left = $total - $done;
        $eta = round($rate * $left, 2);

        $elapsed = $now - $start_time;

        $status_bar.= " remaining: ".number_format($eta)." sec.  elapsed: ".number_format($elapsed)." sec.";

        echo "$status_bar  ";

        flush();

        // when done, send a newline
        if($done == $total) {
            echo "--\n";
        }
    }

    function colorize($text, $status) {
        $out = "";
        switch($status) {
            case "SUCCESS":
            $out = "1;31"; //Green background
            break;
            case "FAILURE":
            $out = "1;32"; //Red background
            break;
            case "WARNING":
            $out = "0;30"; //Yellow background
            break;
            case "NOTE":
            $out = "0;37"; //Blue background
            break;
            default:
            throw new Exception("Invalid status: " . $status);
        }
        return "\033[{$out}m {$text} \033[0m" . "\n";
    }    