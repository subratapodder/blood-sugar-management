<?php
namespace Drupal\bs_manager\Form;
    
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AdminSettingsForm.
 *
 * @package Drupal\bs_manager\Form
 */
class AdminSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bs_manager_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'bs_manager.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('bs_manager.settings');
    $form['bs_form_refresh_time'] = [
        '#type' => 'textfield',
        '#title' => $this->t('User BS for refresh time: '),
        '#default_value' => $config->get('bs_form_refresh_time'),
        '#required' => TRUE,
    ];
    $form['min_refresh_time_of_bs_report_data'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Minimun refresh time of BS data: '),
        '#default_value' => $config->get('min_refresh_time_of_bs_report_data'),
        '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('bs_manager.settings');
    $config->set('min_refresh_time_of_bs_report_data', $form_state->getValue('min_refresh_time_of_bs_report_data'));
    $config->set('bs_form_refresh_time', $form_state->getValue('bs_form_refresh_time'));
    $config->save();
    parent::submitForm($form, $form_state);
    $form_state->setRedirect('bs_manager.admin_dashboard');
  }

}