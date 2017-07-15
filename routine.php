<?php 

    $inserted = 0;
    $errors = [];

    open_connections($connections);

    if(!empty($truncates) AND $options['t']){
        truncate($truncates);
    }