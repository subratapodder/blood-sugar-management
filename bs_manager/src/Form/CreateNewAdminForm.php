<?php
/**
 * @file
 * Contains \Drupal\bs_manager\Form\CreateNewAdminForm.
 */

namespace Drupal\bs_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

class CreateNewAdminForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'create_new_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {     
    $form['user_name'] = array(
      '#type' => 'textfield',
      '#title' => t('User name: '),
      '#required' => TRUE,
    );
    $form['user_email'] = array(
      '#type' => 'textfield',
      '#title' => t('Email: '),
      '#required' => TRUE,
    );
    $form['password'] = array(
      '#type' => 'password',
      '#title' => t('Password: '),
      '#required' => TRUE,
    );
    $form['confirm_password'] = array(
      '#type' => 'password',
      '#title' => t('Confirm Password: '),
      '#required' => TRUE,
    );
    // Submit.
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Create New Admin'),
    );
    return $form;
  }  
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $user_name = $form_state->getValue('user_name');
    $user_email = $form_state->getValue('user_email');
    $password = $form_state->getValue('password');
    $confirm_password = $form_state->getValue('confirm_password');
    // password validation
    $result = $this->checkPassword($password, $confirm_password);
    if ($result != true) {
      $form_state->setErrorByName('password', $this->t('Password mismatch, please enter carefully.'));
    }
    // Email check
    $result_email = $this->checkEmailExists($user_email);
    if (!empty($result_email)) {
      $form_state->setErrorByName('user_email', $this->t('Email address is already in use.'));
    }
    elseif ($user_email != '' && !\Drupal::service('email.validator')->isValid($user_email)) {
      $form_state->setErrorByName('email', t('The email address %mail is not valid.', array('%mail' => $user_email)));
    }
    // Username check
    $result_name = $this->checkUserExists($user_name);
    if (!empty($result_name)) {
      $form_state->setErrorByName('user_name', $this->t('User name is already in use.'));
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
  public function checkEmailExists($email) {
    $ids = \Drupal::entityQuery('user')
    ->condition('mail', $email)
    ->execute();
    return $ids;
  }
  public function checkUserExists($user_name) {
    $ids = \Drupal::entityQuery('user')
    ->condition('name', $user_name)
    ->execute();
    return $ids;
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user_name = $form_state->getValue('user_name');
    $admin_email = $form_state->getValue('user_email');
    $password = $form_state->getValue('password');
    $user = User::create();
    $user->setPassword($password);
    $user->enforceIsNew();
    $user->setEmail($admin_email);
    $user->set("init", $admin_email);
    $user->setUsername($user_name); 
    $user->addRole('auxesis_admin'); 
    $user->activate();
    //Save user
    $user->save();
    $uid = $user->id();
    if (isset($uid)) {
      drupal_set_message(t('New admin create successfully.'));
    }
    else {
      drupal_set_message(t('Unable to create admin. Something went wrong.'), 'error');
    }
    $form_state->setRedirect('bs_manager.admin_dashboard');
  }
}