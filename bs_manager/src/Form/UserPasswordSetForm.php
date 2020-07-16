<?php
/**
 * @file
 * Contains \Drupal\bs_manager\Form\UserPasswordSetForm.
 */

namespace Drupal\bs_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

class UserPasswordSetForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bs_user_pw_set_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $uid = NULL) {
    $form['password'] = array(
      '#type' => 'password',
      '#title' => t('Password: '),
      '#required' => TRUE,
      '#attributes' => array('placeholder' => t('Set password.')),
    );    
    $form['confirm_password'] = array(
      '#type' => 'password',
      '#title' => t('Confirm Password: '),
      '#required' => TRUE,
      '#attributes' => array('placeholder' => t('Reenter password.')),
    );
    $form['user_id'] = array(
      '#type' => 'hidden',
      '#default_value' => $uid,
    );
    // Submit.
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Set Password'),
    );
    return $form;
  }  

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $password = $form_state->getValue('password');
    $confirm_password = $form_state->getValue('confirm_password');
    // password validation
    $result = $this->checkPassword($password, $confirm_password);
    if ($result != true) {
      $form_state->setErrorByName('password', $this->t('Password mismatch, please enter carefully.'));
    }
  }
  public function checkPassword($password, $confirm_password) {
    if ($password === $confirm_password) {
      return true;
    }
    else {
      return false;
    }
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $password = $form_state->getValue('password');
    $confirm_password = $form_state->getValue('confirm_password');
    $user_id = $form_state->getValue('user_id');
    if (isset($user_id)) {
      $user = User::load($user_id);
      $user->setPassword($password);
      $user->activate();
      //Save user
      $user->save();
      user_login_finalize($user);
      drupal_set_message(t('Successfully logged in as user.'));
      $form_state->setRedirect('bs_manager.my_space');
    }
    else {
      drupal_set_message(t('You are a invalid user.'), 'error');
      $form_state->setRedirect('bs_manager.register');
    }
  }


}