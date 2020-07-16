<?php
/**
 * @file
 * Contains \Drupal\bs_manager\Form\BsPrescriptionSearchForm.
 */

namespace Drupal\bs_manager\Form; 

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class BsPrescriptionSearchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bs_prescription_search_form';
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
      $form['options']['search'] = array(
        '#type' => 'submit',
        '#value' => t('Filter'),
      );
      $form['options']['open_modal1'] = array(
        '#type' => 'link',
        '#title' => t('Add Prescription'),
        '#url' => Url::fromRoute('bs_manager.attach_prescription'),
        '#attributes' => array('class' => array('button', 'use-ajax'), 'data-dialog-type' => 'modal', 
        'data-dialog-options' => '{"width": 700}'),
      );
      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(true);
  }

}