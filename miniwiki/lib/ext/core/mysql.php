<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  # extension Core MySQL Storage (bundled)

  class EXT_CoreMySQLStorage extends MW_Extension {

    function get_name() {
      return "Core MySQL Storage";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "MySQL storage.";
    }

    function initialize() {
      return true;
    }

  }

  register_extension(new EXT_CoreMySQLStorage());

  define("MW_RESOURCE_KEY_AUTHOR_COMPATIBLE", "user");
  
  # database access class
  class MW_MySQL_Storage extends MW_Storage {
    # [private] attributes
    # host name
    var $host;
    # user name
    var $user;
    # user password
    var $pass;
    # database name
    var $dbname;
    # MySQL connection or null
    var $conn;
    
    # constructor
    function MW_MySQL_Storage() {
      global $mw_db_host, $mw_db_user, $mw_db_pass, $mw_db_name;
      $this->host = $mw_db_host;
      $this->user = $mw_db_user;
      $this->pass = $mw_db_pass;
      $this->dbname = $mw_db_name;
    }

    # ordered by name
    function get_resource_names($dataspace) {
      $query = $this->open_query('select distinct('.MW_RESOURCE_KEY_NAME.') from '.$dataspace. ' order by '. MW_RESOURCE_KEY_NAME);
      $ret = array();
      while (($result = $this->fetch_query_result($query))) {
        $name = $result[MW_RESOURCE_KEY_NAME];
        array_push($ret, $name);
      }
      $this->close_query($query);
      return $ret;
    }
    
    function exists($dataspace, $name) {
      $query = $this->open_query('select '.MW_RESOURCE_KEY_NAME.' from '.$dataspace. ' where '.
         MW_RESOURCE_KEY_NAME.'=?',
         $name);
      $ret = (($result = $this->fetch_query_result($query)));
      $this->close_query($query);
      return $ret;
    }

    function get_resource_internal($dataspace, $name, $revision, $with_data) {
      $ds_def = $this->dataspace_defs[$dataspace];
      $has_content = ($ds_def->get_content_type() != MW_RESOURCE_CONTENT_TYPE_NONE);
      $is_versioned = ($ds_def->is_versioned());
      # TODO schema update is not yet working
      $has_timestamp = ($dataspace != MW_DS_USERS);
      if (isset($revision) && $is_versioned) {
        if ($revision == MW_REVISION_HEAD) {
          $query = $this->open_query('select max(revision) from '.$dataspace. ' where '.MW_RESOURCE_KEY_NAME.'=?', $name);
          if (($result = $this->fetch_query_result($query))) {
            $revision = $result[0];
          }
          $this->close_query($query);
        }
      }
      return $this->open_query('select '.
         ($has_content && $with_data ? MW_RESOURCE_KEY_CONTENT.',' : '').
         ($has_content ? 'length('.MW_RESOURCE_KEY_CONTENT.') as '.MW_RESOURCE_KEY_CONTENT_LENGTH.',' : '').
         ($has_timestamp ? MW_RESOURCE_KEY_LAST_MODIFIED.',' : '').
         ($is_versioned ? MW_RESOURCE_KEY_MESSAGE.',' : '').
         ($is_versioned ? MW_RESOURCE_KEY_AUTHOR_COMPATIBLE.',' : '').
         (!isset($revison) && $is_versioned ? MW_RESOURCE_KEY_REVISION.',' : '').
         (sizeof($ds_def->get_custom_keys()) > 0 ? implode(array_keys($ds_def->get_custom_keys()), ',').',' : '').
         MW_RESOURCE_KEY_NAME.
         ' from '.$dataspace. ' where '.
         MW_RESOURCE_KEY_NAME.'=?'.(isset($revision) && $is_versioned ? ' and '.MW_RESOURCE_KEY_REVISION.'=?' : '').
         (!isset($revision) && $is_versioned ? ' order by '.MW_RESOURCE_KEY_REVISION.' desc' : ''),
         $name, $revision);
    }
    
    function get_resource($dataspace, $name, $revision, $with_data) {
      $query = $this->get_resource_internal($dataspace, $name, $revision, $with_data);
      $res = null;
      if (($result = $this->fetch_query_result($query))) {
        $res = new MW_Resource();
        foreach ($result as $key => $value) {
          if (!is_int($key)) {
            if ($key == MW_RESOURCE_KEY_AUTHOR_COMPATIBLE) {
              $key = MW_RESOURCE_KEY_AUTHOR;
            }
            $res->set($key, $value);
          }
        }
      }
      $this->close_query($query);
      return $res;
    }
    
    function delete_resource($dataspace, $name) {
      $this->exec_statement('delete from '.$dataspace. ' where '.MW_RESOURCE_KEY_NAME.'=?', $name);
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
        if ($key == MW_RESOURCE_KEY_AUTHOR) {
          $key = MW_RESOURCE_KEY_AUTHOR_COMPATIBLE;
        }
        array_push($keys, $key);
        array_push($placeholders, '?');
        $cols[$key] = $value;
      }
      if ($is_versioned || $should_create) {
        $this->exec_statement('insert into '.$dataspace.
          ' ('.implode(', ', $keys).
          ') values ('.implode(', ', $placeholders).')',
          array_values($cols));
      } else {
        $set = array();
        foreach ($keys as $key) {
          array_push($set, $key. '=?');
        }
        $this->exec_statement('update '.$dataspace.
          ' set '.implode(', ', $set).
          ' where '.MW_RESOURCE_KEY_NAME.'=?',
          array_values($cols),
          $resource->get(MW_RESOURCE_KEY_NAME));
      }
    }
    
    # ordered by revision from last to first
    function get_resource_history($dataspace, $name, $with_data) {
      $query = $this->get_resource_internal($dataspace, $name, null, $with_data);
      $ret = array();
      while (($result = $this->fetch_query_result($query))) {
        $res = new MW_Resource();
        foreach ($result as $key => $value) {
          if (!is_int($key)) {
            if ($key == MW_RESOURCE_KEY_AUTHOR_COMPATIBLE) {
              $key = MW_RESOURCE_KEY_AUTHOR;
            }
            $res->set($key, $value);
          }
        }
        array_push ($ret, $res);
      }
      $this->close_query($query);
      return $ret;
    }
    
    # [private] initialize connection if not already open
    function init() {
      if (!isset ($this->conn)) {
        $this->conn = mysql_connect($this->host, $this->user, $this->pass) or die ("Can't connect to server : " . mysql_error());
        mysql_select_db($this->dbname, $this->conn) or die ("Can't select database : " . mysql_error());
        global $mw_db_use_server_collation, $mw_db_encoding;
        if ($mw_db_use_server_collation) {
          mysql_query("SET CHARACTER SET '".$mw_db_encoding."'", $this->conn);
        } else {
          mysql_query("SET NAMES '".$mw_db_encoding."'", $this->conn);
        }
      }
    }
    
    # destroy database connection
    # must be called before script ends
    function destroy() {
      if (isset ($this->conn)) {
        mysql_close($this->conn) or die ("Can't close connection : " . mysql_error());
        unset($this->conn);
      }
    }

    # [private] escape dangerous chars and quote value if needed
    # value: value to escape and quote
    # returns escaped and quoted value
    function quote_smart($value) {
       if (get_magic_quotes_gpc()) {
           $value = stripslashes($value);
       }
       if (!is_numeric($value)) {
           $value = "'" . mysql_real_escape_string($value) . "'";
       }
       return $value;
    }
    
    # execute non-query statement
    # st: statement with placeholders ('?')
    # ...: values to be used instead of placeholders
    # returns TRUE on success and FALSE on error
    function exec_statement($st) {
      return $this->open_query_from_array(func_get_args());
    }

    # [private] execute statement
    # query_array: array (statement with placeholders ('?'), values to be used instead of placeholders, arrays are unwind)
    # returns TRUE or MySQL resource on success and FALSE on error
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

    # execute query
    # query: query with placeholders ('?')
    # ...: values to be used instead of placeholders
    # returns query result identifier
    function open_query($query) {
      return $this->open_query_from_array(func_get_args());
    }
    
    # close query result
    # result: result from open_query()
    function close_query($result) {
      mysql_free_result($result);
    }

    # returns next row from query result as array indexed by column numbers and also by column names
    # result: result from open_query()
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
        case MW_RESOURCE_KEY_AUTHOR_COMPATIBLE:
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
                trigger_error("Unknown custom key type: ".$type_name);
            }
          }
      }
      trigger_error("Unknown resource key: ".$name);
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
         array_push($defs, $this->get_column_definition($dataspace_def, MW_RESOURCE_KEY_AUTHOR_COMPATIBLE));
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
      $db_coldef = $db_coldefs[$name];
      if ($db_coldef == null) {
        $sql = 'alter table '.$ds_name. ' add '.$our_coldef;
        show_install_message("Adding column $ds_name.$name");
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
         $this->alter_column_definition($dataspace_def, $db_coldefs, MW_RESOURCE_KEY_AUTHOR_COMPATIBLE);
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
      global $install_mode;
      $ds_name = $dataspace_def->get_name();
      if ($install_mode) {
        if (!$this->exists_table($ds_name)) {
          $this->create_dataspace_table($dataspace_def);
        } else {
          $this->alter_dataspace_table($dataspace_def);
        }
      }
      if (isset($this->dataspace_defs[$ds_name])) {
        trigger_error("Duplicate dataspace definition: " . $ds_name);
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
