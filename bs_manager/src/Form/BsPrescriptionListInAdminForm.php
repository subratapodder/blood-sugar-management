<?php
/**
 * @file
 * Contains \Drupal\bs_manager\Form\BsPrescriptionListInAdminForm.
 */

namespace Drupal\bs_manager\Form; 

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class BsPrescriptionListInAdminForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bs_prescription_list_in_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {      
      $form['options'] = array(
        '#type' => 'fieldset', 
        '#attributes' => array('class' => array('container-inline')),
      );
      $form['options']['file_name'] = array(
        '#type' => 'textfield',
        '#size' => 30,
        '#attributes' => array('placeholder' => "Filter by File Name"),
        '#required' => TRUE,
      );
      $form['options']['search_file_name'] = array(
        '#type' => 'submit',
        '#value' => t('Filter'),
      );
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(true);
  }

}