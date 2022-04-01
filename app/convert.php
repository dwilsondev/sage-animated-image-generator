<?php

    include "config.php";
    include "env.php";

    header('Content-Type: application/json; charset=utf-8');

    if(empty($_POST)) {
        jError("Critical server error, no data was sent to the server.");
    }

    if(empty($_FILES)) {
        jError("Critical server error, files were sent to the server.");
    }

    #####################################################################################
    #
    #   CHECK USER INPUT
    #
    #####################################################################################
    $uploadType = filter_input(INPUT_POST, 'uploadType', FILTER_SANITIZE_STRING);
    $resolution = filter_input(INPUT_POST, 'resolution', FILTER_SANITIZE_STRING);
    $fps = filter_input(INPUT_POST, 'fps', FILTER_SANITIZE_STRING);
    $loopOption = filter_input(INPUT_POST, 'loopOption', FILTER_SANITIZE_STRING);
    $timestamp_start = filter_input(INPUT_POST, 'timestamp_start', FILTER_SANITIZE_STRING);
    $timestamp_end = filter_input(INPUT_POST, 'timestamp_end', FILTER_SANITIZE_STRING);

    include "check_timestamps.php";
    
    // Upload option error.
    if(empty($uploadType) && $uploadType !== "animated_gifs" && $uploadType !== "animated_gifs_hq" && $uploadType !== "animated_webp" && $uploadType !== "animated_png" && $uploadType !== "animated_gifs_to_video") {
        jError("Unsupported upload option. Upload failed.");
    }

    #####################################################################################
    #
    #   CHECK UPLOADED FILES FOR ERRORS
    #
    #####################################################################################
    foreach($_FILES as $key => $file) {
        try {
            if (isset($file['error']) && $file['error'] > 0) {
              throw new RuntimeException('One or more of the files uploaded has problems and returned invalid parameters.');
            }
          
            switch ($file['error']) {
              case UPLOAD_ERR_OK:
                break;
              case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No files were received.');
              case UPLOAD_ERR_INI_SIZE:
              case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('The files exceeded this Webserver\'s filesize limit.');
              default:
                throw new RuntimeException('Unknown error.');
            }
        } catch (RuntimeException $e) {
            jError($e->getMessage());
        }

        // Check file type. Remove and unset files that are not supported. 
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = mime_content_type($file['tmp_name']);

        if(!in_array($file_ext, $supported_filetypes) || (($file_ext !== "mp4" && $mime !== "video/mp4") && ($file_ext !== "png" && $mime !== "image/png") && ($file_ext !== "webp" && $mime !== "image/webp") && ($file_ext !== "jpeg" && $mime !== "image/jpg") && ($file_ext !== "jpg" && $mime !== "image/jpg") && ($file_ext !== "gif" && $mime !== "image/gif") && ($file_ext !== "tif" && $mime !== "image/tiff") && ($file_ext !== "tiff" && $mime !== "image/tiff") && ($file_ext !== "zip" && $mime !== "application/zip"))) {
            unlink($file['tmp_name']);
            unset($_FILES[$key]);
            continue;
        }

        // Check filesize.
        $filesize = filesize($file['tmp_name']);
        $filesize = round($filesize / 1024 / 1024, 1);

        if($filesize > $filesize_limit || $filesize < 0) {
            jError("One or more of the files are too big. Files should be less than $filesize_limit megs all together.");
        }
    }

    if(empty($_FILES)) {
        jError("None of the files uploaded are supported.");
    }

    #####################################################################################
    #
    #   RENAME AND MOVE FILES
    #
    #####################################################################################
    if(!is_dir("temp")) {
        mkdir("temp");
    }

    $folder = "temp_".uniqid();
    mkdir("temp/".$folder);
        
    foreach($_FILES as $key => $file) {
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = mime_content_type($file['tmp_name']);
        
        if($rename_temp_files == true) {
            $filename = uniqid().".".$file_ext;
        } else {
            $filename = $file['name'];
            $filename = str_replace(" ", "-", $filename);
        }

        // Move file.
        if(!move_uploaded_file($file['tmp_name'], "temp"."/".$folder."/".$filename)) {
            cleanUp($folder);   
            
            jError("Failed to move some files. Can't continue.");
        }   
    }

    // If uploading one file, set single file extension and set multi_upload to false.
    if(sizeof($_FILES) == 1) {
        $ext = strtolower(pathinfo($_FILES['file_0']['name'], PATHINFO_EXTENSION));
        $multi_upload = false;
    } else {
        $ext = "";
        $multi_upload = true;
    }

    #####################################################################################
    #
    #   SET CONVERSION OPTIONS
    #
    #####################################################################################
    // Loop option.
    if($loopOption == "true") {
        $loop = "-loop 0";
        $webpLoop = "-loop 65535";
        $apngLoop = "-plays 0";
        $gifskiLoop = "--repeat 0";
    } else {
        $loop = "-loop -1";
        $webpLoop = "-loop 1";
        $apngLoop = "-plays 1";
        $gifskiLoop = "--repeat -1";
    }

    // Resolution option.
    if($resolution == "1920") {
        $resolution = "-vf scale=1920:-1";
        $gifskiRes = "--width 1920";
    } elseif($resolution == "1280") {
        $resolution = "-vf scale=1280:-1";
        $gifskiRes = "--width 1280";
    } elseif($resolution == "854") {
        $resolution = "-vf scale=854:-1";
        $gifskiRes = "--width 854";
    } elseif($resolution == "640") {
        $resolution = "-vf scale=640:-1";
        $gifskiRes = "--width 640";
    } else {
        $resolution = "";
        $gifskiRes = "";
    }

    // FPS option.
    if(!is_numeric($fps) || $fps < 0 || $fps > 60) {
        $gifskiFPS = "--fps 30";
        $fps = "-framerate 30";
    } else {
        $gifskiFPS = "--fps $fps";
        $fps = "-framerate $fps";
    }

    // Final file extension.
    if($uploadType == "animated_png") {
        $ext_final = "png";
    } elseif($uploadType == "animated_webp") {
        $ext_final = "webp";
    } elseif($uploadType == "animated_gifs_to_video") {
        $ext_final = "mp4";
    }  else {
        $ext_final = "gif";
    }

    $data['display_image_preview'] = true;

    #####################################################################################
    #
    #   VIDEO CONVERSION
    #
    #####################################################################################
    if($ext == "mp4") {
        if($trim == true) {
            $trim = "-ss 00:$timestamp_start_minute:$timestamp_start_second -to 00:$timestamp_end_minute:$timestamp_end_second";
        } else {
            $trim = "";
        }

        if ($uploadType == "animated_gifs_hq" && $upload_options['animated_gifs_hq'] == "enabled") {
            convert("$ffmpeg -i temp/$folder/$filename $trim $resolution temp/$folder/sequence_%04d.png");
            convert("$gifski --quality 100 $gifskiRes $gifskiFPS $gifskiLoop -o temp/$folder/animated.gif temp/$folder/sequence_*.png");
        } elseif($uploadType == "animated_webp" && $upload_options['animated_webp'] == "enabled") {
            if($webp_encoder == "ffmpeg" && $libwebp == "enabled") {
                convert("$ffmpeg -i temp/$folder/$filename $trim $fps $resolution $webpLoop -c libwebp temp/$folder/animated.webp");
            } else {
                convert("$ffmpeg -i temp/$folder/$filename $trim $fps $resolution $webpLoop temp/$folder/animated.webp");
            }
        } elseif($uploadType == "animated_png" && $upload_options['animated_png'] == "enabled") {
            convert("$ffmpeg -i temp/$folder/$filename $trim $fps $resolution $apngLoop temp/$folder/animated.apng");

            rename("temp/$folder/animated.apng", "temp/$folder/animated.png");
        } elseif($upload_options['animated_gifs'] == "enabled") {
            convert("$ffmpeg -i temp/$folder/$filename $trim $fps $resolution $loop temp/$folder/animated.gif");
        }       
    }

    #####################################################################################
    #
    #   ANIMATED GIF TO VIDEO CONVERSION
    #
    #####################################################################################
    if($ext == "gif" && isGIFAnimated("temp/$folder/$filename") && $uploadType == "animated_gifs_to_video" && $upload_options['animated_gifs_to_video'] == "enabled") {
        convert("$ffmpeg -i temp/$folder/$filename temp/$folder/animated.mp4");
        $data['display_image_preview'] = false;    
    } elseif($ext !== "gif" && $uploadType == "animated_gifs_to_video" && $upload_options['animated_gifs_to_video'] == "enabled") {
        jError("The image you uploaded is not an animated GIF.");
    }

    #####################################################################################
    #
    #   ANIMATED GIF TO ANIMATED IMAGE CONVERSION
    #
    #####################################################################################
    if($ext == "gif" && isGIFAnimated("temp/$folder/$filename") &&  $uploadType == "animated_gifs") {    
        convert("$ffmpeg -i temp/$folder/$filename $resolution $webpLoop temp/$folder/animated.gif");
    } elseif($ext == "gif" && isGIFAnimated("temp/$folder/$filename") && $uploadType == "animated_webp") {
        if($webp_encoder == "ffmpeg" && $libwebp == "enabled") {
            convert("$ffmpeg -i temp/$folder/$filename $resolution $webpLoop -c libwebp temp/$folder/animated.webp");
        } else {
            convert("$ffmpeg -i temp/$folder/$filename $resolution $webpLoop temp/$folder/animated.webp");
        }
    } elseif($ext == "gif" && isGIFAnimated("temp/$folder/$filename") && $uploadType == "animated_png") {
        convert("$ffmpeg -i temp/$folder/$filename $resolution $apngLoop temp/$folder/animated.apng");

        rename("temp/$folder/animated.apng", "temp/$folder/animated.png");
    } 

    #####################################################################################
    #
    #   MULTI-IMAGE CONVERSION
    #
    #####################################################################################
    if($ext == "zip" || $multi_upload == true) {
        if ($ext == "zip") {
            $zip = new ZipArchive();

            if ($zip->open("temp/$folder/$filename") == true) {
                $zip->extractTo("temp/$folder");
                $zip->close();
            } else {
                jError("Failed to extract zip. Upload failed.");
            }
        }

        $files = scandir("temp/$folder", SCANDIR_SORT_ASCENDING);
        unset($files[0]);
        unset($files[1]);

        if(empty($files)) {
            jError("No files were found in zip.");
        }

        natsort($files);

        // Create image sequence find all PNG and JPEG images.
        // Convert JPEGs to PNG and rename all PNGs. Create Webp string for img2webp encoding.
        $itr = 0;
        $img2webp_string = "";

        foreach($files as $f) {
            if(exif_imagetype("temp/$folder/$f") == IMAGETYPE_PNG || exif_imagetype("temp/$folder/$f") == IMAGETYPE_JPEG || exif_imagetype("temp/$folder/$f") == IMAGETYPE_WEBP || exif_imagetype("temp/$folder/$f") == IMAGETYPE_TIFF_II || exif_imagetype("temp/$folder/$f") == IMAGETYPE_TIFF_MM || exif_imagetype("temp/$folder/$f") == IMAGETYPE_GIF) { 
                $fext = strtolower(pathinfo($f, PATHINFO_EXTENSION));;
                rename("temp/$folder/$f", "temp/$folder/sequence_$itr.$fext");

                convert("$ffmpeg -i temp/$folder/sequence_$itr.$fext temp/$folder/sequence_$itr.png");

                $img2webp_string .= " temp/$folder/sequence_$itr.png";  
                $itr = $itr + 1;
            }
        }

        // Create animated image from image sequence.
        if($uploadType == "animated_gifs_hq" && $upload_options['animated_gifs_hq'] == "enabled") {
            convert("$gifski --quality 100 $gifskiRes $gifskiFPS $gifskiLoop -o temp/$folder/animated.gif temp/$folder/sequence_*.png");
        } elseif($uploadType == "animated_webp" && $upload_options['animated_webp'] == "enabled") {
            if($webp_encoder == "img2webp") {
                convert("$img2webp $webpLoop $img2webp_string -d 100 -o temp/$folder/animated.webp");
            } elseif($webp_encoder == "ffmpeg" && $libwebp == "enabled") {
                convert("$ffmpeg $fps -i temp/$folder/sequence_%d.png $resolution $webpLoop -c libwebp temp/$folder/animated.webp");
            } else {
                convert("$ffmpeg $fps -i temp/$folder/sequence_%d.png $resolution $webpLoop temp/$folder/animated.webp");
            }
        } elseif($uploadType == "animated_png" && $upload_options['animated_png'] == "enabled") {
            convert("$ffmpeg $fps -i temp/$folder/sequence_%d.png $resolution $apngLoop temp/$folder/animated.apng");
            rename("temp/$folder/animated.apng", "temp/$folder/animated.png");
        } elseif($uploadType == "animated_gifs" && $upload_options['animated_gifs'] == "enabled") {
            convert("$ffmpeg $fps -i temp/$folder/sequence_%d.png $resolution $loop  temp/$folder/animated.gif");
        } 
    }

    #####################################################################################
    #
    #   CHECK GENERATED IMAGE
    #
    #####################################################################################
    if(!file_exists("temp/$folder/animated.gif") && !file_exists("temp/$folder/animated.webp") && !file_exists("temp/$folder/animated.png") && !file_exists("temp/$folder/animated.mp4")) {
        cleanUp($folder);

        jError("Could not generate image or video.");
    }

    #####################################################################################
    #
    #   GENERATE DOWNLOAD LINK
    #
    #####################################################################################
    cleanUp($folder);
    
    $data['link'] = "app/temp/$folder/animated.$ext_final";

    echo json_encode($data);

    #####################################################################################
    #
    #   HELPER FUNCTIONS
    #
    #####################################################################################
    function convert($command) {
        exec($command." 2>&1", $output, $result);

        if($result) {
            jError("System Error: ".$output[0]);
        }
    }

    // Sends app error back to user.
    function jError($message) {
        $data['error'] = $message;
        echo json_encode($data);
        die();
    }

    // Clean up temp folder.
    function cleanUp($folder) {
        $files = scandir("temp/$folder");
        unset($files[0]);
        unset($files[1]);
    
        foreach ($files as $file) {
            if($file !== "animated.mp4" && $file !== "animated.gif" && $file !== "animated.png" && $file !== "animated.webp") {
                unlink("temp/$folder/$file");
            }
        }
    }

    // Code by ZeBadger https://www.php.net/manual/en/function.imagecreatefromgif.php#59787
    // Only slightly modified.
    function isGIFAnimated($filename) {
        $filecontents = file_get_contents($filename);

        $str_loc = 0;
        $count = 0;

        while($count < 2) { # There is no point in continuing after we find a 2nd frame
            $where1 = strpos($filecontents, "\x00\x21\xF9\x04", $str_loc);
            
            if($where1 === FALSE) {
                break;
            } else {
                $str_loc = $where1 + 1;
                $where2 = strpos($filecontents, "\x00\x2C", $str_loc);
                
                if($where2 === FALSE) {
                    break;
                } else {
                    if($where1 + 8 == $where2) {
                        $count++;
                    }
                
                    $str_loc = $where2 + 1;
                }
            }
        }

        if ($count > 1) {
            return true;
        } else {
            jError("The image you uploaded is not animated.");
        }
    }