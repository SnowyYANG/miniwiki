<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for showing messages to the user
  */

  require_once('registry.php');
  
  define("MW_COMPONENT_ROLE_INFO_TEXT", "_info_text");
  
  /**
  * add new information to be shown to the user
  * @param text text to show
  */
  function add_info_text($text) {
    global $registry;
    $registry->register($text, MW_COMPONENT_ROLE_INFO_TEXT);
  }
  /**
  * returns array with all information texts
  */
  function get_info_text() {
    global $registry;
    return $registry->lookup(MW_COMPONENT_ROLE_INFO_TEXT);
  }

  define("MW_COMPONENT_ROLE_MESSAGES", "MW_Messages");
  $registry->add_registry(new MW_SingletonComponentRegistry(), MW_COMPONENT_ROLE_MESSAGES);
  $registry->register(new MW_Messages(), MW_COMPONENT_ROLE_MESSAGES);

  class MW_Messages {

    var $messages = array(
      # "translations" for action names
      MW_ACTION_VIEW => 'View',
      MW_ACTION_VIEW_SOURCE => 'View Source',
      MW_ACTION_EDIT => 'Edit',
      MW_ACTION_DELETE => 'Delete',
      MW_ACTION_HISTORY => 'History',
      MW_ACTION_UPDATE => 'Edit',
      MW_ACTION_LOGIN => 'Login',
      MW_ACTION_CHANGE_PASSWORD => 'Change Password',
      MW_ACTION_CREATE_USER => 'Create User',
      MW_ACTION_DELETE_USER => 'Delete User',
      MW_ACTION_UPLOAD => "Upload",
    );

    /** @private */
    function translate($message) {
      if (!isset($this->messages[$message])) {
        debug("Unknown message $message");
        return $message;
      }
      return $this->messages[$message];
    }

    /** @private */
    function replace($message, $data) {
      foreach (array_keys($data) as $key) {
        $message = str_replace("%$key%", $data[$key], $message);
      }
      return $message;
    }

    function format($message, $data = null) {
      $message = $this->translate($message);
      if ($data !== null) {
        $message = $this->replace($message, $data);
      }
      return $message;
    }
  
  }

  function _($message) {
    global $registry;
    $messages = $registry->lookup(MW_COMPONENT_ROLE_MESSAGES);
    $data = null;
    if (func_num_args() > 1) {
      $data = func_get_args();
      array_shift($data);
      if ((sizeof($data) == 1) && is_array($data[0])) {
        $data = $data[0];
      }
    }
    return $messages->format($message, $data);
  }

?>
