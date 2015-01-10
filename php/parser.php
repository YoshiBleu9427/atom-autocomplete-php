<?php

/**
 * @author Axel Anceau <Peekmo>
 *
 * This script returns all functions, classes & methods in the given directory.
 * Internals and user's one
 **/
 require_once(__DIR__ . '/classes_parser.php');

 /**
  * Get functions and classes declared in the given directory
  * @param string $dir Root directory for the script
  **/
 function get_functions_and_classes($dir) {
     if (!is_dir($dir)) {
         die(sprintf('Fatal error : %s is not a directory', $dir));
     }

     $mapping = array(
         'classes'   => array(),
         'functions' => array()
     );
     $current = explode('/', $dir);
     $files = scandir($dir);

     foreach ($files as $file) {
         // OSX & linux users
         if ('.' !== $file && '..' !== $file) {
             $path = $dir . '/' . $file;
             if (is_dir($path)) {
                 $mapping = array_merge_recursive($mapping, get_functions_and_classes($path));
             } else {
                 if (in_array(pathinfo($path, PATHINFO_EXTENSION), array('php', 'inc', 'hh'))) {
                     $mapping = array_merge_recursive($mapping, parse_php_file($path));
                 }
             }
         }
     }

     return $mapping;
 }

 function getMethodsAndAttributes($className) {
    $reflection = new \ReflectionClass();

    return array(
        'methods'    => $reflection->getMethods(),
        'attributes' => $reflection->getAttributes(),
        'constants'  => $reflection->getConstants(),
    );
 }

 if (count($argv) != 2) {
     die('Usage : php parser.php <dirname>');
 }

 $mapping = get_functions_and_classes($argv[1]);

 // Adding PHP internal functions and classes
 $defined_functions = get_defined_functions();

 $internals = array(
     'classes' => get_declared_classes(),
     'functions' => $defined_functions['internal']
 );

 $mapping = array_merge_recursive($mapping, $internals);

 // Returns json for the JS
 echo json_encode($mapping);