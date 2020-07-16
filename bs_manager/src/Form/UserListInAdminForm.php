<?php
/**
 * @file
 * Contains \Drupal\bs_manager\Form\UserListInAdminForm.
 */

namespace Drupal\bs_manager\Form; 

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class UserListInAdminForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_list_admin';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {      
      $form['options'] = array(
        '#type' => 'fieldset', 
        '#attributes' => array('class' => array('container-inline')),
      );
      $form['options']['email_search'] = array(
        '#type' => 'textfield',
        '#attributes' => array('placeholder' => "Filter by Email"),
        '#required' => TRUE,
      );
      $form['options']['search_email'] = array(
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