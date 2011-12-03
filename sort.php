#!/usr/bin/env php
<?php

if ($argc < 2) {
    echo "Usage: {$argv[0]} <filename>" . PHP_EOL;
    echo "The sorted result will be piped to stdout" . PHP_EOL;
    exit(1);
}

$fname = $argv[1];
if (!file_exists($fname) || !is_readable($fname)) {
    echo "Error: File {$fname} does not exists or is not readable" . PHP_EOL;
    exit(2);
}


function create_slice($fp, $max_size = 1048576) {
    $size = 0;
    $lines = array();

    // Read input lines until we reach a max size
    while (FALSE !== ($ln = fgets($fp, 4096))) {
        $lines[] = $ln;

        $size += strlen($ln);
        if ($size > $max_size) { 
            break;
        }
    }

    // Sort sliced lines
    sort($lines);

    // Dump sliced lines to a temporary file
    $sliceFp = tmpfile();
    if (FALSE === $sliceFp) {
        die('Unable to create temporary file');
    }

    foreach ($lines as $ln) {
        fputs($sliceFp, $ln);
    }

    // Reset the file pointer
    fseek($sliceFp, 0);

    return $sliceFp;
}

// Read some lines from an slice file
function consume_slice($fp, $max_size = 131072) {
    $size = 0;
    $lines = array();

    while (FALSE !== ($ln = fgets($fp, 4096))) {
        $lines[] = $ln;

        $size += strlen($ln);
        if ($size > $max_size) {
            break;
        }
    }

    return $lines;
}



// Will keep file pointers to the slices 
$slices = array();

// Read the input file and slice it
// OPTIMIZATION: We can parallelize this task
$fp = fopen($fname, 'r');
while (!feof($fp)) {
    $slices[] = create_slice($fp);
}

// We no longer need the original file
fclose($fp);






// Merge the sorted slices
$buffers = array();
while (true) {

    $selected = null;
    $lowest = null;
    // Go thru each slice
    foreach ($slices as $idx=>$slice) {
        // If the buffer for the slice is empty fill it
        if (empty($buffers[$idx])) {
            $buffers[$idx] = consume_slice($slice);
        }

        // If the buffer still has data
        if (!empty($buffers[$idx])) {
            // Check if it has the lowest value so far
            if ($lowest === NULL || $buffers[$idx][0] < $lowest) {
                $lowest = $buffers[$idx][0];
                $selected = $idx;
            }
        }
    }

    // No selected slice means we just consumed all the input
    if (NULL === $selected) {
        break;
    }

    // Remove the top value from the selected slice
    array_shift($buffers[$selected]);

    // Print out the result
    echo $lowest;
}

