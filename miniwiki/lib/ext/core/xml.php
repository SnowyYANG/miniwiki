<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core XML Import/Export (bundled)
  */

  class EXT_Core_XML_Import_Export extends MW_Extension {

    function get_name() {
      return "Core XML Import/Export";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "XML import/export.";
    }

    function initialize() {
      register_importer(new MW_XML_Importer());
      register_exporter(new MW_XML_Exporter());
      return true;
    }

  }

  register_extension(new EXT_Core_XML_Import_Export());

  define('MW_XML_FORMAT', 'XML');
  define('MW_XML_TYPE_TEXT', 'text');
  define('MW_XML_TYPE_BINARY', 'binary');
  define('MW_XML_TYPE_DATETIME', 'datetime');

  /*
    XML format (if miniWiki's encoding is not UTF-8 and iconv() is not available, internal encoding is used):

    <?xml version="1.0" encoding="utf-8"?>
    <resources>
      <resource dataspace="DATASPACE_NAME" name="RESOURCE_NAME">
        <key name="RESOURCE_KEY_NAME" type="RESOURCE_KEY_TYPE">RESOURCE_KEY_VALUE</key>
        ...
      </resource>
      ...
    </resources>

    RESOURCE_KEY_TYPE is one of:
      text - RESOURCE_KEY_VALUE is plain text
      binary - RESOURCE_KEY_VALUE is encoded with base64
      datetime - RESOURCE_KEY_VALUE is YYYY-MM-DDTHH:MM:SSZ (corresponds to XSD dateTime with UTC timezone)
  */

  class MW_XML_Importer extends MW_Importer {

    function get_format() {
      return MW_XML_FORMAT;
    }
    
  }

  class MW_XML_Exporter extends MW_Exporter {

    function export($file, $with_history = true, $dataspaces = array()) {
      global $storage, $mw_encoding;
      $enc = $mw_encoding;
      $conv_enc_from = null;
      # convert to UTF-8 if possible
      if (strcasecmp($enc, "utf-8") != 0) {
        if (function_exists("iconv")) {
          $conv_enc_from = $enc;
          $enc = "utf-8";
        }
      }
      $out = fopen($file, "wb");
      fwrite($out, '<?xml version="1.0" encoding="'.$enc.'"?>'."\n");
      fwrite($out, '<resources>'."\n");
      if (sizeof($dataspaces) == 0) {
        $dataspaces = $storage->get_dataspace_names();
      }
      foreach ($dataspaces as $ds) {
        $ds_def = $storage->get_dataspace_definition($ds);
        $types_map = array();
        /** @todo custom keys may have (currently) only text type */
        if ($ds_def->get_content_type() == MW_RESOURCE_CONTENT_TYPE_BINARY) {
          $types_map[MW_RESOURCE_KEY_CONTENT] = MW_XML_TYPE_BINARY;
        }
        $types_map[MW_RESOURCE_KEY_LAST_MODIFIED] = MW_XML_TYPE_DATETIME;
        $res_names = $storage->get_resource_names($ds);
        foreach ($res_names as $res_name) {
          if ($with_history) {
            $resources = $storage->get_resource_history($ds, $res_name, true);
          } else {
            $resources = array( $storage->get_resource($ds, $res_name, null, true) );
          }
          foreach ($resources as $res) {
            fwrite($out, '<resource dataspace="'.$ds.'" name="'.$this->convert_for_xml($conv_enc_from, $enc, $res_name).'">'."\n");
            foreach ($res->data as $key => $value) {
              $type = MW_XML_TYPE_TEXT;
              if (isset($types_map[$key])) {
                $type = $types_map[$key];
              }
              fwrite($out, '<key name="'.$this->convert_for_xml($conv_enc_from, $enc, $key).'">');
              switch ($type) {
                case MW_XML_TYPE_BINARY:
                  $value = base64_encode($value);
                  break;
                case MW_XML_TYPE_DATETIME:
                  $ts = last_modified_as_timestamp($value);
                  $value = strftime("%Y%m%dT%H%M%SZ", $ts);
                  break;
              }
              fwrite($out, $this->convert_for_xml($conv_enc_from, $enc, $value, false));
              fwrite($out, '</key>'."\n");
            }
            fwrite($out, '</resource>'."\n");
          }
        }
      }
      fwrite($out, '</resources>'."\n");
      fclose($out);
      return true;
    }

    function convert_for_xml($from, $to, $str, $as_attr = true) {
      if ($from !== null) {
        $str = iconv($from, $to, $str);
      }
      return htmlspecialchars($str, ($as_attr ? ENT_QUOTES : ENT_NOQUOTES));
    }
    
    function get_format() {
      return MW_XML_FORMAT;
    }
    
  }

?>
