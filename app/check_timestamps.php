<?php

    // Check user input timestamps when video timestamps option is enabled.
    if(!empty($timestamp_start) && !empty($timestamp_end)) {
        $start = explode(":", $timestamp_start);
        $start = explode(".", $timestamp_start);
        $end = explode(":", $timestamp_end);
        $end = explode(".", $timestamp_end);

        // There must be a minute and second timecode.
        if(isset($start[0]) && isset($start[1]) && isset($end[0]) && isset($end[1])) {
            $m = $start[0];
            $s = $start[1];

            if(($s < 0 || $s > 60) || ($m < 0 || $m > 1) || ((empty($s) && empty($m)))) {
                $data['error'] = "Bad start timestamp, try again.";
                echo json_encode($data);
                die();
            } else {
                $timestamp_start_minute = $m;
                $timestamp_start_second = $s;
            }

            $m = $end[0];
            $s = $end[1];

            if(($s < 0 || $s > 60) || ($m < 0 || $m > 1) || ((empty($s) && empty($m)))) {
                $data['error'] = "Bad end timestamp, try again.";
                echo json_encode($data);
                die();
            } else {
                $timestamp_end_minute = $m;
                $timestamp_end_second = $s;
            }

            $trim = true;
        } else {
            $data['error'] = "Bad timestamp, try again.";
            echo json_encode($data);
            die();
        }   
    } else {
        $trim = false;
    }