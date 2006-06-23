<?php
  # $Id: export.php 68 2006-06-23 18:25:23Z src $
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * Data import (for use from command line).
  */

  define("MW_DEBUG", false);
  
  /**
  * error handler
  */
  function error_handler($errno, $errstr, $errfile, $errline) {
    echo "OOPS! Something is wrong: $errstr(error code $errno, file $errfile, line $errline)\n";
  }
  set_error_handler("error_handler");
  
  /**
  * echo debug message (if MW_DEBUG is true)
  * msg: message to show
  */
  function debug($msg) {
    if (MW_DEBUG) {
      echo 'DEBUG: '.$msg."\n";
    }
  }
  
  ini_set('include_path', ini_get('include_path').':../lib');

  if (php_sapi_name() !== 'cli') {
    die("Must be run from command line");
  }
  
  include('../userdefs.php');
  include('settings.php');
  include('miniwiki.php');
  miniwiki_boot();
  # forces intialization of delayed dataspace definitions which is what we need
  $storage = new_storage();
    
  if ($argc < 2) {
    echo MW_NAME, ' ', MW_VERSION, ' (c)2005,2006 Stepan Roh <src@srnet.cz>', "\n";
?>

Usage: <?php echo $argv[0]; ?> [--omit-history] [--force-import] file dataspace*

Available formats:
<?php
    foreach(get_importers() as $importer) {
      echo('  '.$importer->get_format()."\n");
    }
    exit();
  }

  array_shift($argv);
  $with_history = true;
  $force_import = false;
  if ($argv[0] === '--omit-history') {
    $with_history = false;
    array_shift($argv);
  }
  if ($argv[0] === '--force-import') {
    $force_import = true;
    array_shift($argv);
  }
  $format = array_shift($argv);
  $file = array_shift($argv);
  $dataspaces = $argv;
  
  echo "Importing from $file...";
  $status = import($file, $with_history, $dataspaces, $force_import);
  if ($status === null) {
    echo "\nUnknown file format\n";
  } else if ($status !== true) {
    echo "\nError: ", $status, "\n";
  } else {
    echo 'done', "\n";
  }
    
?>
