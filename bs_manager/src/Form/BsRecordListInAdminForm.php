<?php
/**
 * @file
 * Contains \Drupal\bs_manager\Form\BsRecordListInAdminForm.
 */

namespace Drupal\bs_manager\Form; 

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class BsRecordListInAdminForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bs_record_list_admin';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {      
      $form['options'] = array(
        '#type' => 'fieldset', 
        '#attributes' => array('class' => array('container-inline')),
      );
      $form['options']['mail_search'] = array(
        '#type' => 'textfield',
        '#attributes' => array('placeholder' => "Filter by Email"),
        '#required' => TRUE,
      );
      $form['options']['search_mail'] = array(
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