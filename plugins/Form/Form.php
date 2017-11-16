<?php
/**
 * The Machine
 *
 * PHP version 5
 *
 * @category  Plugin
 * @package   Machine
 * @author    Paolo Savoldi <paooolino@gmail.com>
 * @copyright 2017 Paolo Savoldi
 * @license   https://github.com/paooolino/Machine/blob/master/LICENSE 
 *            (Apache License 2.0)
 * @link      https://github.com/paooolino/Machine
 */
namespace Machine\Plugin;

/**
 * Link class
 *
 * A Form manager for the Machine.
 *
 * @category Plugin
 * @package  Machine
 * @author   Paolo Savoldi <paooolino@gmail.com>
 * @license  https://github.com/paooolino/Machine/blob/master/LICENSE 
 *           (Apache License 2.0)
 * @link     https://github.com/paooolino/Machine
 */
class Form
{
  
  private $machine;
  private $forms;
  private $onceForms;
  private $formrow_template = '
    <div class="formRow type{{CLASS_TYPE}}">
      <div class="formLabel">
        {{LABEL}}
      </div>
      <div class="formField">
        {{FIELD}}
      </div>
      <div class="closing"></div>
    </div>
  ';
  private $form_template = '
    <div class="formContainer">
      <form method="post" action="{{FORMACTION}}">
        {{FORMROWS}}
        <button type="submit">{{SUBMITLABEL}}</button>
      </form>
    </div>
  ';
  private $_values;
  
  /**
   * Form plugin constructor.
   *
   * The user should not use it directly, as this is called by the Machine.
   *
   * @param Machine $machine the Machine instance.
   */
  function __construct($machine) 
  {
    $this->machine = $machine;
    $this->_values = [];
  }
  
  /**
   * Add a form, given a name and some options.
   *
   * An example
   * <code>
   * $opts = [
   *     "action" => "/register/",    // the slug for the action.
   *     "submitlabel" => "Send",     // the label for the submit button
   *     "fields" => [                // an array of field definitions.
   *         "email",                    // the name for a text field. 
   *         ["password", "password"] // the name and type of a field.
   *     ]
   * ]
   * </code>
   *
   * @param string $name
   * @param array  $opts
   *
   * @return void
   */
  public function addForm($name, $opts) 
  {
    $this->forms[$name] = $opts;
  }
  
  /**
   *  Add fields to a form just once
   */
  public function addFieldsOnce($name, $fields)
  {
    $original_form_fields = $this->forms[$name]["fields"];
    $new_fields = array_merge(
      $original_form_fields,
      $fields
    );
    $this->onceForms[$name] = $this->forms[$name];
    $this->onceForms[$name]["fields"] = $new_fields;
  }
  
  /**
   *  Set the values for a form.
   */
  public function setValues($formname, $values)
  {
    $this->_values[$formname] = $values;
  }
  
  /**
   * Renders the form, given the name.
   *
   * @param string $params
   *
   * @return string The html code to display the form.
   */
  public function Render($params) 
  {
    $formName = $params[0];
    
    $opts = isset($this->onceForms[$formName]) 
              ? $this->onceForms[$formName]
              : $this->forms[$formName];

    $html_rows = "";
    foreach ($opts["fields"] as $formField) {
      $value = "";
      if (isset($formField[2]) && isset($formField[2]["name"])) {
        $fieldname = $formField[2]["name"];
        if (isset($this->_values[$formName][$fieldname])) {
          $value = $this->_values[$formName][$fieldname];
        }
      }
      $html_rows .= $this->machine->populateTemplate(
        $this->formrow_template, [
          "LABEL" => $this->getFormLabel($formField),
          "FIELD" => $this->getFormField($formField, $value),
          "CLASS_TYPE" => $formField[1]
        ]
      );
    }
    
    $html = $this->machine->populateTemplate(
      $this->form_template, [
        "FORMACTION" => $opts["action"],
        "FORMROWS" => $html_rows,
        "SUBMITLABEL" => isset($opts["submitlabel"]) ? $opts["submitlabel"] : "submit"
      ]
    );
    
    unset($this->onceForms[$formName]);

    return $html;
  }
  
  private function getFormLabel($formField) 
  {
    $type = gettype($formField);
    if ($type == "string") {
      return $formField;
    }
    if ($type == "array") {
      $field_type = $formField[1];
      switch ($field_type) {
        case "hidden":
          return "";
          break;
        case "checkbox":
          return '<label for="' . $formField[2]["name"] . '">' . $formField[0] . '</label>';
          break;
        case "content":
          return $formField[0];
        default:
          return $formField[0];
      }
    }        
  }
  
  private function _getHtmlForOptions($opts) {
    $html = '';
    
    foreach ($opts as $opt) {
      if (gettype($opt) == "string") {
        $html .= '<option>' . $opt . '</option>';
      }
    }
    
    return $html;
  }
  
  private function getFormField($formField, $value) 
  {
    $field_type = $formField[1];
    switch ($field_type) {
      case "text":
        $attributes = $this->_buildFieldAttributesString($formField[2]);
        return '<input value="'. htmlentities($value) .'" type="text" ' . $attributes . ' />';
        break;
      case "image":
        $attributes = $this->_buildFieldAttributesString($formField[2]);
        return '<input data-value="' . htmlentities($value) . '" type="file" ' . $attributes . '>';
        break;
      case "content":
        return '';
      case "email":
        $attributes = $this->_buildFieldAttributesString($formField[2]);
        return '<input value="'. htmlentities($value) .'" type="email" ' . $attributes . ' />';
        break;
      case "select":
        $attributes = $this->_buildFieldAttributesString($formField[2]);
        $opts = $this->_getHtmlForOptions($formField[2]["options"]);
        return '<select value="'. htmlentities($value) .'" ' . $attributes . '>' . $opts . '</select>';
        break;
      case "password":
        $attributes = $this->_buildFieldAttributesString($formField[2]);
        return '<input type="password" ' . $attributes . ' />';
        break;
      case "checkbox":
        $attributes = $this->_buildFieldAttributesString($formField[2]);
        return '<input id="' . $formField[2]["name"] . '" type="checkbox" ' . $attributes . ' />';
        break;
      case "hidden":
        $v = htmlentities($formField[2]);
        return '<input type="hidden" value="'. $v .'" name="' . $formField[0] . '" />';
        break;
    }
  }
  
  private function _buildFieldAttributesString($arr_attributes)
  {
    $allowed_attributes = ["name", "disabled"];
    $atts = [];
    foreach ($arr_attributes as $k => $v) {
      if (in_array($k, $allowed_attributes)) {
        $atts[] = $k . '="' . htmlentities($v) . '"';
      }
    }
    return implode(" ", $atts);
  }
}
