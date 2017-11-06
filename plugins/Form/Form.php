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
  private $formrow_template = '
    <div class="formRow">
      <div class="formLabel">
        {{LABEL}}
      </div>
      <div class="formField">
        {{FIELD}}
      </div>
    </div>
  ';
  private $form_template = '
    <div class="formContainer">
      <form method="post" action="{{FORMACTION}}">
        {{FORMROWS}}
        <button type="submit">submit</button>
      </form>
    </div>
  ';
    
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
  }
    
  /**
   * Add a form, given a name and some options.
   *
   * An example
   * <code>
   * $opts = [
   *     "action" => "/register/",    // the slug for the action.
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
      $html_rows .= $this->machine->populateTemplate(
        $this->formrow_template, [
          "LABEL" => $this->getFormLabel($formField),
          "FIELD" => $this->getFormField($formField)
        ]
      );
    }
    
    $html = $this->machine->populateTemplate(
      $this->form_template, [
        "FORMACTION" => $opts["action"],
        "FORMROWS" => $html_rows
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
        default:
          return $formField[0];
      }
    }        
  }
    
  private function getFormField($formField) 
  {
    $type = gettype($formField);
    if ($type  == "string") {
      return '<input type="text" name="' . $formField . '" />';
    }
    if ($type == "array") {
      $field_type = $formField[1];
      switch ($field_type) {
        case "password":
          return '<input type="password" name="' . $formField[0] . '" />';
          break;
        case "hidden":
          $v = htmlentities($formField[2]);
          return '<input type="hidden" name="' . $formField[0] . '" value="'. $v .'" />';
          break;
      }
    }
  }
}
