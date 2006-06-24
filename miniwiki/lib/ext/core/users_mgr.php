<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core User Manager (bundled)
  */

  define("MW_DS_USERS", "users");
  define("MW_RESOURCE_KEY_PASSWORD", "password");
    
  class MW_CoreUsersManagerExtension extends MW_Extension {

    function get_name() {
      return "Core Users Manager";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "Users manager operating on top of storage.";
    }

    function initialize() {
      register_users_manager_class("MW_CoreUsersManager");
      $dataspace_def = new MW_DataSpaceDefinition(MW_DS_USERS, false, MW_RESOURCE_CONTENT_TYPE_NONE);
      $dataspace_def->add_custom_key(MW_RESOURCE_KEY_PASSWORD, MW_RESOURCE_CUSTOM_KEY_TYPE_TEXT . "32");
      register_dataspace($dataspace_def);
      return true;
    }

  }

  register_extension(new MW_CoreUsersManagerExtension());

  class MW_CoreUsersManager extends MW_UsersManager {

    function get_all_usernames() {
      global $storage;
      return $storage->get_resource_names(MW_DS_USERS);
    }

    function create_user($user) {
      global $storage;
      $res = new MW_Resource();
      $res->set(MW_RESOURCE_KEY_NAME, $user);
      $storage->create_resource(MW_DS_USERS, $res);
    }

    function delete_user($user) {
      global $storage;
      $storage->delete_resource(MW_DS_USERS, $user);
    }

    function change_password($user, $pass) {
      global $storage;
      $res = new MW_Resource();
      $res->set(MW_RESOURCE_KEY_NAME, $user);
      $md5_pass = md5($pass);
      $res->set(MW_RESOURCE_KEY_PASSWORD, $md5_pass);
      $storage->update_resource(MW_DS_USERS, $res);
    }
    
    function is_password_valid($user, $pass) {
      global $storage;
      $md5_pass = md5($pass);
      $res = $storage->get_resource(MW_DS_USERS, $user, MW_REVISION_HEAD, false);
      return ($md5_pass == $res->get(MW_RESOURCE_KEY_PASSWORD));
    }
    
  }

?>
