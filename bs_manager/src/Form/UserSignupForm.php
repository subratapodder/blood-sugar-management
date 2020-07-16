<?php
/**
 * @file
 * Contains \Drupal\bs_manager\Form\UserSignupForm.
 */

namespace Drupal\bs_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

class UserSignupForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bs_user_signup_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) { 
    $form['email'] = array(
      '#type' => 'email',
      '#title' => t('Email: '),
      '#required' => TRUE,
      '#attributes' => array('placeholder' => t('Enter your email.')),
    );
    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Full Name: '),
      '#required' => TRUE,
      '#attributes' => array('placeholder' => t('Enter your full name.')),
    );
    // Submit.
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Register'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $name = trim($form_state->getValue('name'));
    $name = isset($name) ? $name : '';
    $email = trim($form_state->getValue('email'));
    $email = isset($email) ? $email : '';
    // Name validation
    $full_name_regex = "/^[a-zA-Z\s\.]+$/";
    $name_match = preg_match($full_name_regex, $name);
    if (!empty($name) && $name_match == 0) {
      $form_state->setErrorByName('name', $this->t('Name only contain alphabets.'));
    }
    // Email validation
    $result_email = $this->checkEmailExists($email);
    if (!empty($result_email)) {
      $form_state->setErrorByName('email', $this->t('Email address is already in use.'));
    }
    elseif ($email != '' && !\Drupal::service('email.validator')->isValid($email)) {
      $form_state->setErrorByName('email', t('The email address %mail is not valid.', array('%mail' => $email)));
    }
  }
  public function checkEmailExists($email) {
    $ids = \Drupal::entityQuery('user')
    ->condition('mail', $email)
    ->execute();
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {	  
    $user_email = trim($form_state->getValue('email'));
    $full_name = trim($form_state->getValue('name'));
    $user_name = str_replace(' ', '_', strtolower($full_name));
    $user_name = $user_name . "_" . rand(10,100);
    // create new user
    $user = User::create();
    $user->setPassword(user_password());
    $user->enforceIsNew();
    $user->setEmail($user_email);
    $user->setUsername($user_name); 
    $user->set("init", $user_email);
    $user->set("field_bsuser_fullname", $full_name);
    $user->addRole('auxesis_user');
    //Create the account
    $user->save();
    $uid = $user->id();
    if (isset($uid)) {
      $account = User::load($uid);
      $get_pass_reset_url = $this->generateUserPasswordSetUrl($account);
      $prefer_language = $account->getPreferredLangcode();
      // Sending mail
      $mailManager = \Drupal::service('plugin.manager.mail');
      $module = 'bs_manager';
      $key = 'user_register';
      $to = $user_email;
      $params['message'] = "Your registration completed successfully. Please set your password to click this link. " . $get_pass_reset_url;
      $params['user_name'] = $user_name;
      $langcode = $prefer_language;
      $send = true;
      $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
      if ($result['result'] !== true) {
        drupal_set_message(t('There was a problem sending your message and it was not sent.'), 'error');
      }
      else {
        drupal_set_message(t('Further instructions has been sent to your email %string', array('%string' => $user_email)));
      }
      $form_state->setRedirect('bs_manager.register');
    }
    else {
      drupal_set_message(t('Unable to create user, please check your submitted details.'), 'error');
      $form_state->setRedirect('bs_manager.register');
    } 
  }

  function generateUserPasswordSetUrl($account) {
    $timestamp = REQUEST_TIME;    
    return \Drupal::url('bs_manager.reset', array(
      'uid' => $account->id(),
      'timestamp' => $timestamp,
      'hash' => user_pass_rehash($account, $timestamp),
    ), array(
      'absolute' => TRUE,
    ));
  }

}