<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core Filesystem Storage (bundled)
  *
  * Filesystem storage has several disadvantages:
  * - no support for revisions (including authors and messages)
  * - no support for custom keys in dataspaces with content
  * - no support for more than one binary and one text dataspace
  * - flock() is used for locking - see PHP documentation for potential problems
  * - pretty unusable in safe_mode
  * - pretty much untested
  * - generally it is not recommended for daily usage
  */

  class MW_CoreFilesystemStorageExtension extends MW_Extension {

    function get_name() {
      return "Core Filesystem Storage";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "Filesystem storage.";
    }

    function initialize() {
      register_storage(new MW_FilesystemStorage());
      register_datetime_class("MW_FilesystemDateTime");
      return true;
    }

  }

  register_extension(new MW_CoreFilesystemStorageExtension());

  class MW_FilesystemDateTime extends MW_DateTime {

    var $unix_timestamp;

    function MW_FilesystemDateTime($unix_timestamp) {
      $this->unix_timestamp = $unix_timestamp;
    }
  
    function as_unix_timestamp() {
      return $this->unix_timestamp;
    }

    /** [static] */
    function &from_unix_timestamp($ts) {
      $datetime = new MW_FilesystemDateTime($ts);
      return $datetime;
    }
    
  }

  define("MW_WIKI_FILE_EXT", "wiki");
  define("MW_WIKI_FILE_SUFFIX", "." . MW_WIKI_FILE_EXT);
  
  class MW_FilesystemStorage extends MW_Storage {
    /** @private */
    var $root_dir;
    
    /** constructor */
    function MW_FilesystemStorage() {
      $this->root_dir = config('fs_root_dir');
      if (empty($this->root_dir)) {
        die("fs_root_dir not set");
      }
    }

    /** @private */
    var $lock_fp = null;

    /** @private */
    function init_lock() {
      if ($this->lock_fp === null) {
        $this->lock_fp = fopen($this->root_dir.'/lock.txt', "w+");
      }
    }

    /** @private */
    function lock_read() {
      $this->init_lock();
      flock($this->lock_fp, LOCK_SH);
    }

    /** @private */
    function lock_write() {
      $this->init_lock();
      flock($this->lock_fp, LOCK_EX);
    }

    /** @private */
    function unlock() {
      flock($this->lock_fp, LOCK_UN);
      fclose($this->lock_fp);
      $this->lock_fp = null;
    }

    /** @private */
    function get_ds_dirname_url($dataspace) {
      return $this->root_dir."/".$dataspace;
    }

    /** @private */
    function get_path_for_resource_name($dataspace, $resname) {
      if ($this->binary_dataspace === $dataspace) {
        $root = $this->get_ds_dirname_url($this->text_dataspace);
      } else {
        $root = $this->get_ds_dirname_url($dataspace);
      }
      $ds_def = $this->dataspace_defs[$dataspace];
      $path = $root.'/'.preg_replace('/(^(?=\S+[:\/])*\S+):/', '$1/', $resname);
      if ($ds_def->get_content_type() === MW_RESOURCE_CONTENT_TYPE_TEXT) {
        $path .= MW_WIKI_FILE_SUFFIX;
      }
      return $path;
    }

    /** @private */
    function is_forbidden_name($name) {
      if (preg_match('/^[. ]/', $name)) {
        return true;
      }
      if (preg_match('/[\t\n\r\\\\]/', $name)) {
        return true;
      }
      if (strpos($name, '..')) {
        return true;
      }
      return false;
    }
    
    /** @private */
    function get_resource_names_from_dir($dirname, $recurse, $parent_path, $type, &$resnames) {
      $dir = dir($dirname);
      while (false !== ($entry = $dir->read())) {
        if ($entry[0] == '.') {
          continue;
        }
        $fullentry = $dirname."/".$entry;
        if (is_dir($fullentry)) {
          if (!$recurse) {
            continue;
          }
          $dir_path = $parent_path;
          $dir_path[] = $entry;
          $this->get_resource_names_from_dir($fullentry, $recurse, $dir_path, $type, $resnames);
          continue;
        }
        if (!is_file($fullentry)) {
          continue;
        }
        $ext = pathinfo($entry, PATHINFO_EXTENSION);
        if (($type === MW_RESOURCE_CONTENT_TYPE_TEXT) && ($ext === MW_WIKI_FILE_EXT)) {
          $entry = basename($entry, MW_WIKI_FILE_SUFFIX);
          if (sizeof($parent_path) > 0) {
            $entry = join(':', $parent_path).':'.$entry;
          }
          $resnames[] = $entry;
        } elseif (($type === MW_RESOURCE_CONTENT_TYPE_NONE) || (($type === MW_RESOURCE_CONTENT_TYPE_BINARY) && ($ext !== MW_WIKI_FILE_EXT))) {
          if (sizeof($parent_path) > 0) {
            $entry = join('/', $parent_path).'/'.$entry;
          }
          $resnames[] = $entry;
        }
      }
    }

    /** @private */
    function append_namespace($dirname, $namespace) {
      if (empty($namespace)) {
        return $dirname;
      }
      # to disable .dir access (a bit dumb, I know)
      $namespace = str_replace('.', '_', $namespace);
      $namespace = str_replace(':', '/', $namespace);
      return $dirname.'/'.$namespace;
    }

    /** ordered by name */
    function get_resource_names($dataspace, $namespace = null) {
      $ds_def = $this->dataspace_defs[$dataspace];
      if ($this->binary_dataspace === $dataspace) {
        $dirname = $this->get_ds_dirname_url($this->text_dataspace);
      } else {
        $dirname = $this->get_ds_dirname_url($dataspace);
      }
      $resnames = array();
      $dirname = $this->append_namespace($dirname);
      $this->get_resource_names_from_dir($dirname, ($namespace !== ''), $prefix, array(), $ds_def->get_content_type(), $resnames);
      sort($resnames);
      return $resnames;
    }
    
    /** @private */
    function normalize_namespace($namespace, $for_upload = false) {
      if (empty($namespace)) {
        return $namespace;
      }
      if ($for_upload) {
        return str_replace(':', '/', $namespace);
      }
      return str_replace('/', ':', $namespace);
    }
    
    function get_namespaces($dataspace, $namespace = null) {
      $for_upload = false;
      if ($this->binary_dataspace === $dataspace) {
        $dirname = $this->get_ds_dirname_url($this->text_dataspace);
        $for_upload = true;
      } else {
        $dirname = $this->get_ds_dirname_url($dataspace);
      }
      $namespace = $this->normalize_namespace($namespace, $for_upload);
      $dirname = $this->append_namespace($dirname);
      $dir = dir($dirname);
      $namespaces = array();
      while (false !== ($entry = $dir->read())) {
        if ($entry[0] == '.') {
          continue;
        }
        $fullentry = $dirname."/".$entry;
        if (is_dir($fullentry)) {
          $ns = $entry;
          if (!empty($namespace)) {
            $ns = $namespace.($for_upload ? '/' : ':').$ns;
            $namespaces[] = $ns;
          }
        }
      }
      sort($namespaces);
      return $namespaces;
    }
    
    function exists($dataspace, $name) {
      if ($this->is_forbidden_name($name)) {
        return false;
      }
      $path = $this->get_path_for_resource_name($dataspace, $name);
      return is_file($path);
    }

    function get_resource($dataspace, $name, $revision, $with_data) {
      if ($this->is_forbidden_name($name)) {
        return null;
      }
      $path = $this->get_path_for_resource_name($dataspace, $name);
      if (!is_file($path)) {
        return null;
      }
      $ds_def = $this->dataspace_defs[$dataspace];
      $has_content = ($ds_def->get_content_type() != MW_RESOURCE_CONTENT_TYPE_NONE);
      $is_versioned = ($ds_def->is_versioned());
      $res = new MW_Resource();
      $this->lock_read();
      # custom keys are only for non-content dataspaces
      if (!$has_content) {
        # format of custom keys file is:
        # # ignored...
        # NAME
        #  VALUE (starts with space)
        #  VALUE (dtto)
        # NAME
        # ...
        $handle = fopen($path, "r");
        if ($handle) {
          $curname = null;
          $curvalue = null;
          while (!feof($handle)) {
              $line = rtrim(fgets($handle, 4096), "\n\r");
              if (strpos($line, '#') === 0) {
                continue;
              }
              if (strpos($line, ' ') === 0) {
                if ($curvalue !== null) {
                  $curvalue .= "\n";
                }
                $curvalue .= substr($line, 1);
              } else {
                if ($curname !== null) {
                  $res->set($curname, $curvalue);
                }
                $curname = $line;
              }
          }
          if ($curname !== null) {
            $res->set($curname, $curvalue);
          }
          fclose($handle);
        }
      }
      if ($has_content) {
        if ($with_data) {
          $res->set(MW_RESOURCE_KEY_CONTENT, file_get_contents($path));
        }
        $res->set(MW_RESOURCE_KEY_CONTENT_LENGTH, filesize($path));
      }
      $res->set(MW_RESOURCE_KEY_LAST_MODIFIED, new MW_FilesystemDateTime(filemtime($path)));
      $res->set(MW_RESOURCE_KEY_NAME, $name);
      $this->unlock();
      return $res;
    }
    
    function delete_resource($dataspace, $name) {
      if ($this->is_forbidden_name($name)) {
        return false;
      }
      $this->lock_write();
      $path = $this->get_path_for_resource_name($dataspace, $name);
      $ret = unlink($path);
      $this->unlock();
      return $ret;
    }
    
    function rename_resource($dataspace, $old_name, $new_name) {
      if ($this->is_forbidden_name($old_name)) {
        return false;
      }
      if ($this->is_forbidden_name($new_name)) {
        return false;
      }
      $this->lock_write();
      $old_path = $this->get_path_for_resource_name($dataspace, $old_name);
      $new_path = $this->get_path_for_resource_name($dataspace, $new_name);
      $ret = rename($old_path, $new_path);
      $this->unlock();
      return $ret;
    }
    
    function create_resource($dataspace, $resource) {
      $this->update_resource_internal($dataspace, $resource, true);
    }
    
    function update_resource($dataspace, $resource) {
      $this->update_resource_internal($dataspace, $resource, false);
    }
    
    function update_resource_internal($dataspace, $resource, $should_create) {
      $name = $resource->get(MW_RESOURCE_KEY_NAME);
      if ($this->is_forbidden_name($name)) {
        return false;
      }
      $ds_def = $this->dataspace_defs[$dataspace];
      $has_content = ($ds_def->get_content_type() != MW_RESOURCE_CONTENT_TYPE_NONE);
      $path = $this->get_path_for_resource_name($dataspace, $name);
      if ($has_content) {
        $content = $resource->get(MW_RESOURCE_KEY_CONTENT);
      } else {
        $content = '';
        foreach ($resource->data as $key => $value) {
          if (($key == MW_RESOURCE_KEY_REVISION)
          || ($key == MW_RESOURCE_KEY_CONTENT_LENGTH)
          || ($key == MW_RESOURCE_KEY_LAST_MODIFIED)
          || ($key == MW_RESOURCE_KEY_NAME)) {
            continue;
          }
          $value = str_replace("\r", "", $value);
          $value = str_replace("\n", "\n ", $value);
          $content .= $key."\n ".$value."\n";
        }
      }
      $this->lock_write();
      $this->mkdirs_for_path($path);
      if (!$handle = fopen($path, "wb")) {
        return false;
      }
      if (fwrite($handle, $content) === false) {
        return false;
      }
      fclose($handle);
      $this->unlock();
      return true;
    }

    /** @private */
    function mkdirs_for_path($path) {
      $dir = dirname($path);
      if (!is_dir($dir)) {
        if (!$this->mkdirs_for_path($dir)) {
          return false;
        }
        return mkdir($dir);
      }
      return true;
    }
    
    /** ordered by revision from last to first */
    function get_resource_history($dataspace, $name, $with_data) {
      if ($this->is_forbidden_name($name)) {
        return false;
      }
      $res = $this->get_resource($dataspace, $name, null, $with_data);
      return ($res === null) ? array() : array($res);
    }
    
    var $dataspace_defs = array();
    var $text_dataspace;
    var $binary_dataspace;

    function register_dataspace($dataspace_def) {
      $ds_name = $dataspace_def->get_name();
      if (isset($this->dataspace_defs[$ds_name])) {
        trigger_error("Duplicate dataspace definition: " . $ds_name, E_USER_ERROR);
      }
      if ($dataspace_def->get_content_type() === MW_RESOURCE_CONTENT_TYPE_BINARY) {
        if (isset($this->binary_dataspace)) {
          trigger_error("Only one dataspace with binary content allowed: ".$this->binary_dataspace.", " . $ds_name, E_USER_ERROR);
        }
        $this->binary_dataspace = $ds_name;
      }
      if ($dataspace_def->get_content_type() === MW_RESOURCE_CONTENT_TYPE_TEXT) {
        if (isset($this->text_dataspace)) {
          trigger_error("Only one dataspace with text content allowed: ".$this->text_dataspace.", " . $ds_name, E_USER_ERROR);
        }
        $this->text_dataspace = $ds_name;
      }
      if ($dataspace_def->get_content_type() !== MW_RESOURCE_CONTENT_TYPE_NONE) {
        if (sizeof($dataspace_def->get_custom_keys()) > 0) {
          trigger_error("No custom keys allows in dataspace with content: " . $ds_name, E_USER_ERROR);
        }
      }
      if (config('install_mode')) {
        if ($this->binary_dataspace === $ds_name) {
          $root = $this->get_ds_dirname_url($this->text_dataspace);
        } else {
          $root = $this->get_ds_dirname_url($ds_name);
        }
        show_install_message("Dataspace ".$ds_name.": root directory ".$root);
        if (!is_dir($root)) {
          show_install_message("Creating root directory $root for dataspace $ds_name");
          if (!mkdir($root)) {
            trigger_error("Error creating root directory $root for dataspace $ds_name", E_USER_ERROR);
          }
        }
      }
      $this->dataspace_defs[$ds_name] = $dataspace_def;
    }

    function get_dataspace_names() {
      return array_keys($this->dataspace_defs);
    }

    function get_dataspace_definition($dataspace) {
      return $this->dataspace_defs[$dataspace];
    }

  }
  
?>
