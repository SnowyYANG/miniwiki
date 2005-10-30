<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  # extension User Info (bundled)

  class EXT_UserInfo extends MW_Extension {

    function get_name() {
      return "User Info";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "Wiki function user_info.";
    }

    function initialize() {
      register_wiki_function('user_info', array($this, 'wiki_fn_user_info'));
      return true;
    }

    # returns user information
    function wiki_fn_user_info() {
      global $auth;
      if ($auth->is_logged) {
        $f = get_user_info_file($auth->user);
        if (!file_exists($f)) {
          return null;
        }
        $user_info = file_get_contents($f);
        if ($user_info === false) {
          return null;
        }
        return $user_info;
      }
      return null;
    }
  }

  register_extension(new EXT_UserInfo());

?>
