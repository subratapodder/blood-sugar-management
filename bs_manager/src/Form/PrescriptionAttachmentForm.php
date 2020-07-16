<?php
/**
 * @file
 * Contains \Drupal\bs_manager\Form\PrescriptionAttachmentForm.
 */

namespace Drupal\bs_manager\Form; 

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

class PrescriptionAttachmentForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'prescription_attachment_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['file_upload'] = array(
      '#type' => 'managed_file',
      '#title' => t('Prescription Upload'),  
      '#upload_location' => 'public://bs-user-prescriptions',
      '#required' => TRUE,
    );
    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => t('File Description: '),
      '#attributes' => array('placeholder' => "Enter file description here"),
      '#required' => TRUE,
    );
    $form['options']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Upload File'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_description = trim($form_state->getValue('description'));
    $post_file_name = $form_state->getValue('file_upload');
    $current_user_id = \Drupal::currentUser()->id();    
    $post_file_name = isset($post_file_name[0]) ? $post_file_name[0] : '';
    $file = File::load($post_file_name);
    $file->uid = $current_user_id;
    $file->setPermanent();
    $file->save();
    $fid = $file->id();
    if (isset($fid)) {
      try{
        $timestamp = time();
        $db = \Drupal::database();
        $db->insert('bs_file_details')  // bs_file_details
            ->fields(array(
                'fid' => $fid,
                'description' => $file_description,
            ))
            ->execute();
        drupal_set_message(t("Attachment upload successfully."));
        $form_state->setRedirect('bs_manager.my_space');
      }
      catch (\Exception $e) {
        $error_msg = $e->getMessage();            
        drupal_set_message(t('Error in insert in Data. error : %string', array('%string' => $error_msg)), 'error');
      }
    }


    
  }

}