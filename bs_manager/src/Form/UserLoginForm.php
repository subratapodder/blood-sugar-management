<?php
/**
 * @file
 * Contains \Drupal\bs_manager\Form\UserLoginForm.
 */

namespace Drupal\bs_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

class UserLoginForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bs_user_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {     
    $form['user_name'] = array(
      '#type' => 'textfield',
      '#title' => t('User name: '),
      '#required' => TRUE,
      '#attributes' => array('placeholder' => t('Enter your user name.')),
    );
    $form['password'] = array(
      '#type' => 'password',
      '#title' => t('Password: '),
      '#required' => TRUE,
      '#attributes' => array('placeholder' => t('Enter your password.')),
    );
    // Submit.
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Login'),
    );
    return $form;
  }  

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {	  
    $user_name = $form_state->getValue('user_name');
    $password = $form_state->getValue('password');
    
    if ($uid = \Drupal::service('user.auth')->authenticate($user_name, $password)) {
      $user_account = User::load($uid);
      if ($user_account) {
        user_login_finalize($user_account);
        $roles = $user_account->getRoles();
        if (in_array("auxesis_admin", $roles)) {
          drupal_set_message(t('Successfully logged in as admin.'));
          $form_state->setRedirect('bs_manager.admin_dashboard');
        }
        elseif (in_array("auxesis_user", $roles)) {
          drupal_set_message(t('Successfully logged in as user.'));
          $form_state->setRedirect('bs_manager.my_space');
        }    
        else {
          drupal_set_message(t('Permission Denied.'), 'error');
          $form_state->setRedirect('<front>');
        }
      }
      else {
        drupal_set_message(t('Wrong credentilas.'), 'error');
      }
    }
    else {
      drupal_set_message(t('Unable to authenticate.'), 'error');
    } 
  }


}