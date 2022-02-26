<?php

$temp = realpath("temp");

$temp_folders = scandir($temp);
unset($temp_folders[0]);
unset($temp_folders[1]);

foreach($temp_folders as $temp_folder) {
    delete_folder("$temp/$temp_folder/");
}

function delete_folder($target) {
    if(is_dir($target)){
        $files = glob( $target . '*', GLOB_MARK );

        foreach( $files as $file ){
            delete_folder( $file );      
        }

        rmdir($target);
    } elseif(is_file($target)) {
        if ($target !== ".htaccess" || $target !== "index.html" || $target !== "index.php") {
            unlink($target);
        }
    }
}