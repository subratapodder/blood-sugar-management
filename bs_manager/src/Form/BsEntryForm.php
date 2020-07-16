<?php
/**
 * @file
 * Contains \Drupal\bs_manager\Form\BsEntryForm.
 */

namespace Drupal\bs_manager\Form; 

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class BsEntryForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bs_entry_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {      
      $form['options'] = array(
        '#type' => 'fieldset', 
        '#attributes' => array('class' => array('container-inline')),
      );
      $form['options']['bs_value'] = array(
        '#type' => 'textfield',
        '#attributes' => array('placeholder' => "Enter Blood Sugar value"),
        '#required' => TRUE,
      );
      $form['options']['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Save BS value'),
      );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {    
    $bs_value = trim($form_state->getValue('bs_value'));
    if (isset($bs_value)) {
      $return_msg = $this->checkBsEntry($bs_value);
      if ($return_msg != '') {
        $form_state->setErrorByName('bs_value', $this->t('%msg', array('%msg' => $return_msg)));
      }
    }
  }
  /*
  *  Custom function for validating BS input value
  */
  public function checkBsEntry($bs_value) {
    $message = "";
    if (!is_numeric($bs_value)) {
      $message = "Please enter a numeric value between 0 to 10";
    }
    elseif ($bs_value < 0)
    {
      $message = "Your entered value is less than zero.";
    }
    elseif ($bs_value > 10 ) {
      $message = "Entered value not more than 10.";
    }
    return $message;
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $bs_value = trim($form_state->getValue('bs_value'));
    $current_user_id = \Drupal::currentUser()->id();
    try{
      $timestamp = time();
      $db = \Drupal::database();
      $db->insert('bs_records')  // bs_file_details
          ->fields(array(
              'uid' => $current_user_id,
              'bs_value' => $bs_value,
              'created' => $timestamp,
          ))
          ->execute();
      drupal_set_message(t("BS value added successfully."));
    }
    catch (\Exception $e) {
      $error_msg = $e->getMessage();            
      drupal_set_message(t('Error in insert in Data. error : %string', array('%string' => $error_msg)), 'error');
    }
  }

}