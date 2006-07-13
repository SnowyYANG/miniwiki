<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core Filesystem Storage (bundled)
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
    function get_ds_dirname_url($dataspace) {
      return "file://".$this->root_dir."/".$dataspace;
    }

    /** @private */
    function get_resource_names_from_dir($dirname, $parent_path, &$resnames) {
      $dir = dir($dirname);
      while (false !== ($entry = $dir->read())) {
        if ($entry[0] == '.') {
          continue;
        }
        $fullentry = $dirname."/".$entry;
        if (is_dir($fullentry)) {
          $dir_path = $parent_path;
          $dir_path[] = $entry;
          $this->get_resource_names_from_dir($fullentry, $dir_path, $resnames);
          continue;
        }
        if (!is_file($fullentry)) {
          continue;
        }
        $pathinfo = pathinfo($entry, PATHINFO_EXTENSION);
        $ext = $pathinfo['extension'];
        if ($ext === MW_WIKI_FILE_SUFFIX) {
          $entry = basename($entry, MW_WIKI_FILE_SUFFIX);
          if (sizeof($parent_path) > 0) {
            $entry = join(':', $parent_path).':'.$entry;
          }
        } else {
          if (sizeof($parent_path) > 0) {
            $entry = join('/', $parent_path).'/'.$entry;
          }
        }
        $resnames[] = $entry;
      }
    }

    /** @private */
    function get_path_for_resource_name($root, $resname) {
      return $root.'/'.preg_replace('/(\S):(\S)/', '$1/$2', $resname);
    }

    /** ordered by name */
    function get_resource_names($dataspace) {
      $dirname = $this->get_ds_dirname_url($dataspace);
      $resnames = array();
      $this->get_resource_names_from_dir($dirname, array(), $resnames);
      sort($resnames);
      return $resnames;
    }
    
    function exists($dataspace, $name) {
      $dirname = $this->get_ds_dirname_url($dataspace);
      $path = $this->get_path_for_resource_name($name);
      if (is_file($path)) {
        return true;
      }
      if (is_file($path.'.'.MW_WIKI_FILE_EXT)) {
        return true;
      }
      return false;
    }

    function get_resource($dataspace, $name, $revision, $with_data) {
      $ds_def = $this->dataspace_defs[$dataspace];
      $path = $this->get_path_for_resource_name($name);
      $has_content = ($ds_def->get_content_type() != MW_RESOURCE_CONTENT_TYPE_NONE);
      $is_versioned = ($ds_def->is_versioned());
      $dirname = $this->get_ds_dirname_url($dataspace);
      $res = new MW_Resource();
      /** @todo check dataspace properties */
      if ($has_content) {
        $res->set(MW_RESOURCE_KEY_CONTENT, file_get_contents(path));
      }
      $res->set(MW_RESOURCE_KEY_CONTENT_LENGTH, filesize($path));
      $res->set(MW_RESOURCE_KEY_LAST_MODIFIED, new MW_FilesystemDateTime(filemtime($path)));
      $res->set(MW_RESOURCE_KEY_NAME, $name);
      # no checking whether dataspace is versioned or not
      $res->set(MW_RESOURCE_KEY_REVISION, $revision);
      /** @todo custom keys */
      
      $query = $this->get_resource_internal($dataspace, $name, $revision, $with_data);
      $res = null;
      if (($result = $this->fetch_query_result($query))) {
        $res =& $this->create_resource_object_from_result($result);
      }
      $this->close_query($query);
      return $res;
    }
    
    function delete_resource($dataspace, $name) {
      return $this->exec_statement('delete from '.$dataspace. ' where '.MW_RESOURCE_KEY_NAME.'=?', $name);
    }
    
    function rename_resource($dataspace, $old_name, $new_name) {
      # hack last_modified=last_modified makes MySQL to preserve current value and not set it to current time
      return $this->exec_statement('update '.$dataspace. ' set '.MW_RESOURCE_KEY_NAME.'=?, '. MW_RESOURCE_KEY_LAST_MODIFIED.'='.MW_RESOURCE_KEY_LAST_MODIFIED.' where '.MW_RESOURCE_KEY_NAME.'=?', $new_name, $old_name);
    }
    
    function create_resource($dataspace, $resource) {
      $this->update_resource_internal($dataspace, $resource, true);
    }
    
    function update_resource($dataspace, $resource) {
      $this->update_resource_internal($dataspace, $resource, false);
    }
    
    function update_resource_internal($dataspace, $resource, $should_create) {
      $ds_def = $this->dataspace_defs[$dataspace];
      $is_versioned = ($ds_def->is_versioned());
      $keys = array();
      $cols = array();
      $placeholders = array();
      foreach ($resource->data as $key => $value) {
        if (($key == MW_RESOURCE_KEY_REVISION)
         || ($key == MW_RESOURCE_KEY_CONTENT_LENGTH)
         || ($key == MW_RESOURCE_KEY_LAST_MODIFIED)) {
          continue;
        }
        if (!$is_versioned && !$should_create && ($key == MW_RESOURCE_KEY_NAME)) {
          continue;
        }
        array_push($keys, $key);
        array_push($placeholders, '?');
        $cols[$key] = $value;
      }
      if ($is_versioned || $should_create) {
        return $this->exec_statement('insert into '.$dataspace.
          ' ('.implode(', ', $keys).
          ') values ('.implode(', ', $placeholders).')',
          array_values($cols));
      } else {
        $set = array();
        foreach ($keys as $key) {
          array_push($set, $key. '=?');
        }
        return $this->exec_statement('update '.$dataspace.
          ' set '.implode(', ', $set).
          ' where '.MW_RESOURCE_KEY_NAME.'=?',
          array_values($cols),
          $resource->get(MW_RESOURCE_KEY_NAME));
      }
    }
    
    /** ordered by revision from last to first */
    function get_resource_history($dataspace, $name, $with_data) {
      $query = $this->get_resource_internal($dataspace, $name, null, $with_data);
      $ret = array();
      while (($result = $this->fetch_query_result($query))) {
        $res =& $this->create_resource_object_from_result($result);
        array_push ($ret, $res);
      }
      $this->close_query($query);
      return $ret;
    }
    
    /** @private initialize connection if not already open */
    function init() {
      if (!isset ($this->conn)) {
        $this->conn = mysql_connect($this->host, $this->user, $this->pass) or die ("Can't connect to server : " . mysql_error());
        mysql_select_db($this->dbname, $this->conn) or die ("Can't select database : " . mysql_error());
        if (config('db_use_server_collation')) {
          mysql_query("SET CHARACTER SET '".config('db_encoding')."'", $this->conn);
        } else {
          mysql_query("SET NAMES '".config('db_encoding')."'", $this->conn);
        }
        # try to set time zone to UTC
        mysql_query("SET time_zone = '+0:0'", $this->conn);
      }
    }
    
    /**
    * destroy database connection
    * must be called before script ends
    */
    function shutdown() {
      if (isset ($this->conn)) {
        mysql_close($this->conn) or die ("Can't close connection : " . mysql_error());
        unset($this->conn);
      }
    }

    /** @private
    * escape dangerous chars and quote value if needed
    * @param value value to escape and quote
    * @returns escaped and quoted value
    */
    function quote_smart($value) {
       if (get_magic_quotes_gpc()) {
           $value = stripslashes($value);
       }
       if (!is_numeric($value)) {
           $value = "'" . mysql_real_escape_string($value) . "'";
       }
       return $value;
    }
    
    /**
    * execute non-query statement
    * @param st statement with placeholders ('?')
    * @param ... values to be used instead of placeholders
    * @returns TRUE on success and FALSE on error
    */
    function exec_statement($st) {
      return $this->open_query_from_array(func_get_args());
    }

    /** @private
    * execute statement
    * @param query_array array (statement with placeholders ('?'), values to be used instead of placeholders, arrays are unwind)
    * @returns TRUE or MySQL resource on success and FALSE on error
    */
    function open_query_from_array($query_array) {
      $query = array_shift($query_array);
      $args = array();
      foreach ($query_array as $arg) {
        if (is_array($arg)) {
          $args = array_merge_recursive($args, $arg);
        } else {
          array_push($args, $arg);
        }
      }
      debug('MW_Database.open_query(query='.$query. ')');
      $this->init();
      $i = 0;
      # this is the only preg_replace() with inline PHP code, but since we do not use backreferences
      # (they must be surrounded by ' or " and some chars are escaped in the process) we are safe here
      $query = preg_replace('/(\?)/e', '$this->quote_smart($args[$i++])', $query);
      debug('MW_Database.open_query: query='.$query);
      $result = mysql_query($query, $this->conn) or die ("Can't perform query : " . mysql_error());
      return $result;
    }

    /**
    * execute query
    * @param query query with placeholders ('?')
    * @param ... values to be used instead of placeholders
    * @returns query result identifier
    */
    function open_query($query) {
      return $this->open_query_from_array(func_get_args());
    }
    
    /**
    * close query result
    * @param result result from open_query()
    */
    function close_query($result) {
      mysql_free_result($result);
    }

    /**
    * returns next row from query result as array indexed by column numbers and also by column names
    * @param result result from open_query()
    */
    function fetch_query_result($result) {
      return mysql_fetch_array($result);
    }

    var $dataspace_defs = array();

    function exists_table($name) {
      $query = $this->open_query("show tables from ".$this->dbname." like ?", $name);
      $exists = ($this->fetch_query_result($query));
      $this->close_query($query);
      return $exists;
    }

    function get_column_definition($dataspace_def, $name) {
      switch ($name) {
        case MW_RESOURCE_KEY_NAME:
          return $name." varchar(100) NOT NULL default ''";
        case MW_RESOURCE_KEY_REVISION:
          return $name." int(11) NOT NULL AUTO_INCREMENT";
        case MW_RESOURCE_KEY_CONTENT:
          return $name." ".(($dataspace_def->get_content_type() == MW_RESOURCE_CONTENT_TYPE_TEXT) ? "text" : "mediumblob")." default NULL";
        case MW_RESOURCE_KEY_LAST_MODIFIED:
          return $name." timestamp(14) NOT NULL";
        case MW_RESOURCE_KEY_MESSAGE:
          return $name." varchar(250) default NULL";
        case MW_RESOURCE_KEY_AUTHOR:
          return $name." varchar(100) default NULL";
        default:
          $custom_keys = $dataspace_def->get_custom_keys();
          if (isset($custom_keys[$name])) {
            $type = $custom_keys[$name];
            list($type_name, $type_arg) = explode(':', $type);
            if ($type_name.':' === MW_RESOURCE_CUSTOM_KEY_TYPE_TEXT) {
                if (!isset($type_arg)) {
                  $type_arg = "100";
                }
                return $name." varchar($type_arg) default NULL";
            } else {
                trigger_error("Unknown custom key type: ".$type_name, E_USER_ERROR);
            }
          }
      }
      trigger_error("Unknown resource key: ".$name, E_USER_ERROR);
    }

    function query_column_definitions($table_name) {
      $query = $this->open_query("show columns from ".$table_name);
      $coldefs = array();
      while (($res = $this->fetch_query_result($query))) {
        $name = $res['Field'];
        $type = $res['Type'];
        $not_null = ($res['Null'] !== "YES");
        $primary_key = (stripos($res['Key'], "PRI") !== false);
        $default = $res['Default'];
        $auto_increment = (stripos($res['Extra'], "auto_increment") !== false);
        if (strcasecmp($type, 'timestamp') == 0) {
          # to be compatible with all MySQL versions
          $type .= '(14)';
        }
        if (stripos($type, 'timestamp') !== false) {
          if (!$not_null && (strcasecmp($default, 'current_timestamp') == 0)) {
            $not_null = true;
            $default = null;
          }
        }
        $coldef = $name.' '.$type;
        if ($not_null) {
          $coldef .= ' not null';
        }
        if ($default !== null) {
          $coldef .= ' default ';
          if (!is_numeric($default)) {
            $coldef .= "'" . mysql_real_escape_string($default) . "'";
          } else {
            $coldef .= $default;
          }
        } elseif (!$not_null) {
          $coldef .= ' default NULL';
        }
        if ($auto_increment) {
          $coldef .= ' auto_increment';
        }
        $coldefs[$name] = $coldef;
        if ($primary_key) {
          if (!isset($coldefs['#PRIMARY_KEYS'])) {
            $coldefs['#PRIMARY_KEYS'] = array();
          }
          $coldefs['#PRIMARY_KEYS'][$name] = true;
        }
      }
      $this->close_query($query);
      return $coldefs;
    }

    function create_dataspace_table($dataspace_def) {
      $ds_name = $dataspace_def->get_name();
      $defs = array();
      # column definitons are ordered to be shown in nice order by default in select *
      array_push($defs, $this->get_column_definition($dataspace_def, MW_RESOURCE_KEY_NAME));
      if ($dataspace_def->is_versioned()) {
         array_push($defs, $this->get_column_definition($dataspace_def, MW_RESOURCE_KEY_REVISION));
      }
      if ($dataspace_def->get_content_type() != MW_RESOURCE_CONTENT_TYPE_NONE) {
         array_push($defs, $this->get_column_definition($dataspace_def, MW_RESOURCE_KEY_CONTENT));
      }
      array_push($defs, $this->get_column_definition($dataspace_def, MW_RESOURCE_KEY_LAST_MODIFIED));
      if ($dataspace_def->is_versioned()) {
         array_push($defs, $this->get_column_definition($dataspace_def, MW_RESOURCE_KEY_MESSAGE));
         array_push($defs, $this->get_column_definition($dataspace_def, MW_RESOURCE_KEY_AUTHOR));
      }
      foreach (array_keys($dataspace_def->get_custom_keys()) as $key) {
         array_push($defs, $this->get_column_definition($dataspace_def, $key));
      }
      if ($dataspace_def->is_versioned()) {
         array_push($defs, "PRIMARY KEY (".MW_RESOURCE_KEY_NAME.",".MW_RESOURCE_KEY_REVISION.")");
      } else {
         array_push($defs, "PRIMARY KEY (".MW_RESOURCE_KEY_NAME.")");
      }
      show_install_message("Creating table $ds_name");
      $sql = 'create table '.$ds_name. ' ('.implode($defs, ', ').')';
      $this->exec_statement($sql);
    }

    function alter_column_definition($dataspace_def, $db_coldefs, $name) {
      $ds_name = $dataspace_def->get_name();
      $our_coldef = $this->get_column_definition($dataspace_def, $name);
      $db_coldef = (isset($db_coldefs[$name]) ? $db_coldefs[$name] : null);
      if ($db_coldef === null) {
        if (($name == MW_RESOURCE_KEY_AUTHOR) && isset($db_coldefs[MW_RESOURCE_KEY_AUTHOR_0_2])) {
          $sql = 'alter table '.$ds_name. ' change '.MW_RESOURCE_KEY_AUTHOR_0_2.' '.$our_coldef;
          show_install_message("Renaming column $ds_name.".MW_RESOURCE_KEY_AUTHOR_0_2." to $ds_name.$name");
        } else {
          $sql = 'alter table '.$ds_name. ' add '.$our_coldef;
          show_install_message("Adding column $ds_name.$name");
        }
        $this->exec_statement($sql);
      } elseif (strcasecmp($our_coldef, $db_coldef) != 0) {
        $sql = 'alter table '.$ds_name. ' modify '.$our_coldef;
        show_install_message("Modifying column $ds_name.$name");
        $this->exec_statement($sql);
      }
    }

    function alter_dataspace_table($dataspace_def) {
      $ds_name = $dataspace_def->get_name();
      $db_coldefs = $this->query_column_definitions($dataspace_def->get_name());
      $this->alter_column_definition($dataspace_def, $db_coldefs, MW_RESOURCE_KEY_NAME);
      if ($dataspace_def->is_versioned()) {
         $this->alter_column_definition($dataspace_def, $db_coldefs, MW_RESOURCE_KEY_REVISION);
      }
      if ($dataspace_def->get_content_type() != MW_RESOURCE_CONTENT_TYPE_NONE) {
         $this->alter_column_definition($dataspace_def, $db_coldefs, MW_RESOURCE_KEY_CONTENT);
      }
      $this->alter_column_definition($dataspace_def, $db_coldefs, MW_RESOURCE_KEY_LAST_MODIFIED);
      if ($dataspace_def->is_versioned()) {
         $this->alter_column_definition($dataspace_def, $db_coldefs, MW_RESOURCE_KEY_MESSAGE);
         $this->alter_column_definition($dataspace_def, $db_coldefs, MW_RESOURCE_KEY_AUTHOR);
      }
      foreach (array_keys($dataspace_def->get_custom_keys()) as $key) {
         $this->alter_column_definition($dataspace_def, $db_coldefs, $key);
      }
      $alter_primary_keys = false;
      if (!isset($db_coldefs['#PRIMARY_KEYS'])) {
        $alter_primary_keys = true;
      } else if (!isset($db_coldefs['#PRIMARY_KEYS'][MW_RESOURCE_KEY_NAME])) {
        $alter_primary_keys = true;
      } else if ($dataspace_def->is_versioned()
                 && !isset($db_coldefs['#PRIMARY_KEYS'][MW_RESOURCE_KEY_REVISION])) {
        $alter_primary_keys = true;
      }
      if ($alter_primary_keys) {
        show_install_message("Adding primary keys to $ds_name");
        if (isset($db_coldefs['#PRIMARY_KEYS'])) {
          $sql = 'alter table '.$ds_name. ' drop primary key';
          $this->exec_statement($sql);
        }
        $sql = 'alter table '.$ds_name. ' add primary key';
        if ($dataspace_def->is_versioned()) {
           $sql .= "(".MW_RESOURCE_KEY_NAME.",".MW_RESOURCE_KEY_REVISION.")";
        } else {
           $sql .= "(".MW_RESOURCE_KEY_NAME.")";
        }
        $this->exec_statement($sql);
      }
    }

    function register_dataspace($dataspace_def) {
      $ds_name = $dataspace_def->get_name();
      if (config('install_mode')) {
        show_install_message("Dataspace ".$ds_name.": MySQL database ".$this->dbname."@".$this->host.", table ".$ds_name);
        if (!$this->exists_table($ds_name)) {
          $this->create_dataspace_table($dataspace_def);
        } else {
          $this->alter_dataspace_table($dataspace_def);
        }
      }
      if (isset($this->dataspace_defs[$ds_name])) {
        trigger_error("Duplicate dataspace definition: " . $ds_name, E_USER_ERROR);
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
