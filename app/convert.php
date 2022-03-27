<?php

    include "config.php";
    include "env.php";

    header('Content-Type: application/json; charset=utf-8');

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
        $data['error'] = "Unsupported upload option. Upload failed.";
        echo json_encode($data);
        die();
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
            $data['error'] = $e->getMessage();
            echo json_encode($data);
            die();
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
            $data['error'] = "One or more of the files are too big. Files should be less than $filesize_limit megs all together.";
            echo json_encode($data);
            die();
        }
    }

    if(empty($_FILES)) {
        $data['error'] = "None of the files uploaded are supported.";
        echo json_encode($data);
        die();
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
        }

        // Move file.
        if(!move_uploaded_file($file['tmp_name'], "temp"."/".$folder."/".$filename)) {
            cleanUp($folder);

            $data['error'] = "Failed to move some files. Can't continue.";
            echo json_encode($data);
            die();                
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
            exec("$ffmpeg -i temp/$folder/$filename $trim $resolution temp/$folder/sequence_%04d.png");
            exec("$gifski --quality 100 $gifskiRes $gifskiFPS $gifskiLoop -o temp/$folder/animated.gif temp/$folder/sequence_*.png");
        }  elseif($uploadType == "animated_webp" && $upload_options['animated_webp'] == "enabled") {
            if($webp_encoder == "ffmpeg" && $libwebp == "enabled") {
                exec("$ffmpeg -i temp/$folder/$filename $trim $fps $resolution $webpLoop -c libwebp temp/$folder/animated.webp");
            } else {
                exec("$ffmpeg -i temp/$folder/$filename $trim $fps $resolution $webpLoop temp/$folder/animated.webp");
            }
        } elseif($uploadType == "animated_png" && $upload_options['animated_png'] == "enabled") {
            exec("$ffmpeg -i temp/$folder/$filename $trim $fps $resolution $apngLoop temp/$folder/animated.apng");
            
            rename("temp/$folder/animated.apng", "temp/$folder/animated.png");
        } elseif($upload_options['animated_gifs'] == "enabled") {
            exec("$ffmpeg -i temp/$folder/$filename $trim $fps $resolution $loop temp/$folder/animated.gif");
        }       

        $data['display_image_preview'] = false;  
    }

    if($ext == "gif" && $uploadType == "animated_gifs_to_video" && $upload_options['animated_gifs_to_video'] == "enabled") {
        exec("$ffmpeg -i temp/$folder/$filename temp/$folder/animated.mp4");  

        $data['display_image_preview'] = false;    
    } elseif($ext !== "gif" && $uploadType == "animated_gifs_to_video" && $upload_options['animated_gifs_to_video'] == "enabled") {
        $data['error'] = "Upload an animated GIF to make a video.";
        echo json_encode($data);
        die();
    }

    #####################################################################################
    #
    #   MULTI-FILE CONVERSION
    #
    #####################################################################################
    if($ext == "zip" || $multi_upload == true) {
        if ($ext == "zip") {
            $zip = new ZipArchive();

            if ($zip->open("temp/$folder/$filename") == true) {
                $zip->extractTo("temp/$folder");
                $zip->close();
            } else {
                $data['error'] = "Failed to extract zip. Upload failed.";
                echo json_encode($data);
                die();
            }
        }

        $files = scandir("temp/$folder", SCANDIR_SORT_ASCENDING);
        unset($files[0]);
        unset($files[1]);

        natsort($files);

        // Create image sequence find all PNG and JPEG images.
        // Convert JPEGs to PNG and rename all PNGs. Create Webp string for img2webp encoding.
        $itr = 0;
        $img2webp_string = "";

        foreach($files as $f) {
            if(exif_imagetype("temp/$folder/$f") == IMAGETYPE_PNG || exif_imagetype("temp/$folder/$f") == IMAGETYPE_JPEG || exif_imagetype("temp/$folder/$f") == IMAGETYPE_WEBP || exif_imagetype("temp/$folder/$f") == IMAGETYPE_TIFF_II || exif_imagetype("temp/$folder/$f") == IMAGETYPE_TIFF_MM) { 
                if(exif_imagetype("temp/$folder/$f") == IMAGETYPE_JPEG) {
                    rename("temp/$folder/$f", "temp/$folder/sequence_$itr.jpg");
                    exec("$ffmpeg -i temp/$folder/sequence_$itr.jpg temp/$folder/sequence_$itr.png");
                } elseif(exif_imagetype("temp/$folder/$f") == IMAGETYPE_WEBP) {
                    rename("temp/$folder/$f", "temp/$folder/sequence_$itr.webp");
                    exec("$ffmpeg -i temp/$folder/sequence_$itr.webp temp/$folder/sequence_$itr.png");
                } elseif(exif_imagetype("temp/$folder/$f") == IMAGETYPE_TIFF_II || exif_imagetype("temp/$folder/$f") == IMAGETYPE_TIFF_MM) {
                    rename("temp/$folder/$f", "temp/$folder/sequence_$itr.tif");
                    exec("$ffmpeg -i temp/$folder/sequence_$itr.tif temp/$folder/sequence_$itr.png");
                } elseif(exif_imagetype("temp/$folder/$f") == IMAGETYPE_PNG) {
                    rename("temp/$folder/$f", "temp/$folder/sequence_$itr.png");
                }  

                $img2webp_string .= " temp/$folder/sequence_$itr.png";  
                $itr = $itr + 1;
            }
        }

        // Create animated image from image sequence.
        if($uploadType == "animated_gifs_hq" && $upload_options['animated_gifs_hq'] == "enabled") {
            exec("$gifski --quality 100 $gifskiRes $gifskiFPS $gifskiLoop -o temp/$folder/animated.gif temp/$folder/sequence_*.png");
        } elseif($uploadType == "animated_webp" && $upload_options['animated_webp'] == "enabled") {
            if($webp_encoder == "img2webp") {
                exec("$img2webp $webpLoop $img2webp_string -d 100 -o temp/$folder/animated.webp");
            } elseif($webp_encoder == "ffmpeg" && $libwebp == "enabled") {
                exec("$ffmpeg $fps -i temp/$folder/sequence_%d.png $resolution $webpLoop -c libwebp temp/$folder/animated.webp");
            } else {
                exec("$ffmpeg $fps -i temp/$folder/sequence_%d.png $resolution $webpLoop temp/$folder/animated.webp");
            }
        } elseif($uploadType == "animated_png" && $upload_options['animated_png'] == "enabled") {
            exec("$ffmpeg $fps -i temp/$folder/sequence_%d.png $resolution $apngLoop temp/$folder/animated.apng");
            
            rename("temp/$folder/animated.apng", "temp/$folder/animated.png");
        } elseif($uploadType == "animated_gifs" && $upload_options['animated_gifs'] == "enabled") {
            exec("$ffmpeg $fps -i temp/$folder/sequence_%d.png $resolution $loop  temp/$folder/animated.gif");
        } 
    }

    #####################################################################################
    #
    #   CHECK GENERATED IMAGE
    #
    #####################################################################################
    if(!file_exists("temp/$folder/animated.gif") && !file_exists("temp/$folder/animated.webp") && !file_exists("temp/$folder/animated.png") && !file_exists("temp/$folder/animated.mp4")) {
        cleanUp($folder);

        $data['error'] = "Could not generate image or video.";
        echo json_encode($data);
        die();
    }

    #####################################################################################
    #
    #   CLEAN UP
    #
    #####################################################################################
    cleanUp($folder);

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

    #####################################################################################
    #
    #   GENERATE DOWNLOAD LINK
    #
    #####################################################################################
    $data['link'] = "app/temp/$folder/animated.$ext_final";

    echo json_encode($data);