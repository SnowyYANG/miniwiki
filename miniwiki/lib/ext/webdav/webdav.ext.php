<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension WebDAV Storage (bundled)
  *
  * WebDAV storage has several disadvantages:
  * - supports at most two dataspaces, one of them with binary content and the other with text content
  * - supprots only HTTP Basic Authorization
  * - pretty much untested
  * - generally it is not recommended for daily usage
  * - @todo no history (yet)
  */

  class MW_WebDAVStorageExtension extends MW_Extension {

    function get_name() {
      return "WebDAV Storage";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      $storage =& get_storage();
      $server = '???';
      if (($storage !== null) && is_a($storage, 'MW_WebDAVStorage')) {
        $client =& $storage->get_current_webdav_client();
        $resp = $client->options($storage->root_path);
        if (isset($resp['header']['Server'])) {
          $server = $resp['header']['Server'];
        } elseif (isset($resp['header']['server'])) {
          $server = $resp['header']['server'];
        }
      }
      return "WebDAV: $server";
    }

    function initialize() {
      register_storage(new MW_WebDAVStorage());
      register_datetime_class("MW_WebDAVDateTime");
      register_users_manager(new MW_WebDAVUsersManager());
      return true;
    }

  }
  
  require_once("class_webdav_client.php");

  register_extension(new MW_WebDAVStorageExtension());

  class MW_WebDAVDateTime extends MW_DateTime {

    var $iso8601_time;

    function MW_WebDAVDateTime($iso8601_time) {
      $this->iso8601_time = $iso8601_time;
    }
  
    function as_unix_timestamp() {
      return webdav_client::iso8601totime($this->iso8601_time);
    }

    /** [static] */
    function &from_unix_timestamp($ts) {
      $datetime = new MW_WebDAVDateTime(gmstrftime("%Y-%m-%dT%H:%M:%SZ", $ts));
      return $datetime;
    }
    
  }

  class MW_WebDAVStorage extends MW_Storage {
    
    /** @private */
    var $server;
    /** @private */
    var $port;
    /** @private
     * always ends with slash ('/')
     */
    var $root_path;
    
    /** constructor */
    function MW_WebDAVStorage() {
      $root_url = config('webdav_root_url');
      if (empty($root_url)) {
        die("webdav_root_url is not set");
      }
      $url_comps = parse_url($root_url);
      if (($url_comps === false) || !isset($url_comps['host'])) {
        die("webdav_root_url is malformed");
      }
      $this->server = $url_comps['host'];
      $this->port = isset($url_comps['port']) ? $url_comps['port'] : 80;
      $this->root_path = isset($url_comps['path']) ? $url_comps['path'] : '/';
      if ($this->root_path[strlen($this->root_path) - 1] != '/') {
        $this->root_path .= '/';
      }
    }

    function &get_webdav_client($user, $pass) {
      $client = new webdav_client();
      $client->set_server($this->server);
      $client->set_port($this->port);
      $client->set_user($user);
      $client->set_pass($pass);
      $client->open();
      return $client;
    }
    
    var $cur_client = null;
    
    function &get_current_webdav_client() {
      if ($this->cur_client === null) {
        $auth =& get_auth();
        if ($auth->is_invalid()) {
          $user = null;
          $pass = null;
        } else {
          $user = $auth->user;
          $pass = $auth->pass;
        }
        $this->cur_client = $this->get_webdav_client($user, $pass);
      }
      return $this->cur_client;
    }
    
    /** @private */
    function get_full_path($path) {
      return $this->root_path . $path;
    }
    
    /** @private */
    function get_path_for_resource_name($dataspace, $resname) {
      $ds_def = $this->get_dataspace_definition($dataspace);
      $path = $this->get_full_path($resname);
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
      $client =& $this->get_current_webdav_client();
      $dirinfo = $client->ls($dirname);
      if (!is_array($dirinfo)) {
        return array();
      }
      foreach ($dirinfo as $item) {
		$entry = basename(urldecode($item['href']));
        $fullentry = $dirname."/".$entry;
        if (isset($item['resourcetype']) && ($item['resourcetype'] == 'collection')) {
          if (!$recurse) {
            continue;
          }
          $dir_path = $parent_path;
          $dir_path[] = $entry;
          $this->get_resource_names_from_dir($fullentry, $recurse, $dir_path, $type, $resnames);
          continue;
		}
        $ext = pathinfo($entry, PATHINFO_EXTENSION);
        if (($type === MW_RESOURCE_CONTENT_TYPE_TEXT) && ($ext === MW_WIKI_FILE_EXT)) {
          $entry = basename($entry, MW_WIKI_FILE_SUFFIX);
          if (sizeof($parent_path) > 0) {
            $entry = join('/', $parent_path).'/'.$entry;
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
      if ($dirname[strlen($dirname) - 1] != '/') {
        $dirname .= '/';
      }
      return $dirname.$namespace;
    }

    /** ordered by name */
    function get_resource_names($dataspace, $namespace = null) {
      $ds_def = $this->get_dataspace_definition($dataspace);
      if ($ds_def === null) {
        return array();
      }
      $resnames = array();
      $dirname = $this->append_namespace($this->get_full_path(''), $namespace);
      $this->get_resource_names_from_dir($dirname, ($namespace !== ''), array(), $ds_def->get_content_type(), $resnames);
      sort($resnames);
      return $resnames;
    }
    
    /** @private */
    function normalize_namespace($namespace) {
      if (empty($namespace)) {
        return $namespace;
      }
      $namespace = preg_replace('/\/+$/', '', $namespace);
      return $namespace;
    }
    
    function get_namespaces($dataspace, $namespace = null) {
      $ds_def = $this->get_dataspace_definition($dataspace);
      if ($ds_def === null) {
        return array();
      }
      $namespace = $this->normalize_namespace($namespace);
      $dirname = $this->append_namespace($this->get_full_path(''), $namespace);
      $client =& $this->get_current_webdav_client();
      $dirinfo = $client->ls($dirname);
      $namespaces = array();
      foreach ($dirinfo as $item) {
		$filename = basename(urldecode($item['href']));
		if ($item['resourcetype'] == 'collection') {
          $ns = $entry;
          if (!empty($namespace)) {
            $ns = $namespace.'/'.$ns;
          }
          $namespaces[] = $ns;
		}
      }
      sort($namespaces);
      return $namespaces;
    }
    
    function exists($dataspace, $name) {
      $ds_def = $this->get_dataspace_definition($dataspace);
      if ($ds_def === null) {
        return false;
      }
      if ($this->is_forbidden_name($name)) {
        return false;
      }
      $path = $this->get_path_for_resource_name($dataspace, $name);
      $client =& $this->get_current_webdav_client();
      return ($client->gpi($path) !== false);
    }

    function get_resource($dataspace, $name, $revision, $with_data) {
      if ($this->is_forbidden_name($name)) {
        return null;
      }
      $path = $this->get_path_for_resource_name($dataspace, $name);
      $client =& $this->get_current_webdav_client();
      $item = $client->gpi($path);
      if ($item === false) {
        return null;
      }
	  if (isset($item['resourcetype']) && ($item['resourcetype'] == 'collection')){
        return null;
      }
      $ds_def = $this->get_dataspace_definition($dataspace);
      if ($ds_def === null) {
        return null;
      }
      $is_versioned = ($ds_def->is_versioned());
      $res = new MW_Resource($dataspace);
      if ($with_data) {
        $data = '';
        if (!$this->is_success($client->get($path, $data))) {
          return null;
        }
        $res->set(MW_RESOURCE_KEY_CONTENT, $data);
      }
      $res->set(MW_RESOURCE_KEY_CONTENT_LENGTH, $item['getcontentlength']);
      $res->set(MW_RESOURCE_KEY_LAST_MODIFIED, new MW_WebDAVDateTime($item['lastmodified']));
      $res->set(MW_RESOURCE_KEY_NAME, $name);
      return $res;
    }
    
    /** @private */
    function is_success($code) {
      return ($code >= 200) && ($code < 300);
    }
    
    function delete_resource($dataspace, $name) {
      $ds_def = $this->get_dataspace_definition($dataspace);
      if ($ds_def === null) {
        return false;
      }
      if ($this->is_forbidden_name($name)) {
        return false;
      }
      $path = $this->get_path_for_resource_name($dataspace, $name);
      $client =& $this->get_current_webdav_client();
      return $this->is_success($client->delete($path));
    }
    
    function rename_resource($dataspace, $old_name, $new_name) {
      $ds_def = $this->get_dataspace_definition($dataspace);
      if ($ds_def === null) {
        return false;
      }
      if ($this->is_forbidden_name($old_name)) {
        return false;
      }
      if ($this->is_forbidden_name($new_name)) {
        return false;
      }
      $old_path = $this->get_path_for_resource_name($dataspace, $old_name);
      $new_path = $this->get_path_for_resource_name($dataspace, $new_name);
      $client =& $this->get_current_webdav_client();
      return $this->is_success($client->move($old_path, $new_path, false));
    }
    
    function create_resource($dataspace, $resource) {
      return $this->update_resource_internal($dataspace, $resource, true);
    }
    
    function update_resource($dataspace, $resource) {
      return $this->update_resource_internal($dataspace, $resource, false);
    }
    
    function update_resource_internal($dataspace, $resource, $should_create) {
      $ds_def = $this->get_dataspace_definition($dataspace);
      if ($ds_def === null) {
        return false;
      }
      $name = $resource->get(MW_RESOURCE_KEY_NAME);
      if ($this->is_forbidden_name($name)) {
        return false;
      }
      $path = $this->get_path_for_resource_name($dataspace, $name);
      $content = $resource->get(MW_RESOURCE_KEY_CONTENT);
      $this->mkdirs_for_path($path);
      $client =& $this->get_current_webdav_client();
      return $this->is_success($client->put($path, $content));
    }

    /** @private */
    function mkdirs_for_path($path) {
      $client =& $this->get_current_webdav_client();
      $dirs = explode('/', dirname($path));
      $dir = '';
      foreach ($dirs as $part) {
        $dir .= $part.'/';
        # must start at root path
		if ((strpos($dir, $this->root_path) === 0) && ($dir !== $this->root_path)
		&& !$client->is_dir($dir) && !$this->is_success($client->mkcol($dir))) {
		  return false;
		}
      }
      return true;
    }
    
    /** ordered by revision from last to first */
    function get_resource_history($dataspace, $name, $with_data) {
      $ds_def = $this->get_dataspace_definition($dataspace);
      if ($ds_def === null) {
        return false;
      }
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
      if ($dataspace_def->get_content_type() === MW_RESOURCE_CONTENT_TYPE_NONE) {
        trigger_error("Dataspace without content is not allowed: " . $ds_name, E_USER_ERROR);
      }
      $this->dataspace_defs[$ds_name] = $dataspace_def;
    }

    function get_dataspace_names() {
      return array_keys($this->dataspace_defs);
    }

    function get_dataspace_definition($dataspace) {
      return isset($this->dataspace_defs[$dataspace]) ? $this->dataspace_defs[$dataspace] : null;
    }
    
    function requires_login() {
      $client = $this->get_webdav_client(null, null);
      # should work under most WebDAV security configurations (covers OPTIONS and PROPFIND)
      return !($client->check_webdav($this->root_path) && is_array($client->ls($this->root_path)));
    }

  }
  
  class MW_WebDAVUsersManager extends MW_UsersManager {
    
    function get_all_usernames() {
      return array();
    }

    function allows_maintenance() {
      return false;
    }
    
    function is_password_valid($user, $pass) {
      $storage =& get_storage();
      $client =& $storage->get_webdav_client($user, $pass);
      return $client->check_webdav($storage->root_path);
    }
    
  }
  
?>
