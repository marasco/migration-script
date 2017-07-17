<?php 

    $inserted = 0;
    $errors = [];
    $t = !empty($options['t'])?$options['t']:0;
    
    open_connections($connections);

    if(!empty($truncates) AND $t){
        truncate($truncates);
    }