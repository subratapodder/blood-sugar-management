<?php

/*
* BS Manager controller
*/

namespace Drupal\bs_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Utility\Crypt;
use Drupal\user\Entity\User;
use Drupal\file\Entity\File;
use Drupal\Core\Link;
use Drupal\Core\Url;

class BsManagerController extends ControllerBase{
    public function adminDashBoard() {
        $url_create_admin = Link::fromTextAndUrl(t('Create More Admins'), Url::fromRoute('bs_manager.admin_create_admin'))->toString();
        $url_config = Link::fromTextAndUrl(t('Configure Settings'), Url::fromRoute('bs_manager.admin_create_config'))->toString();
        $build = array(
          '#markup' => "<div>$url_config</div><div>$url_create_admin</div>",
        );
        $build['form1'] = $this->userlistInAdminDashBoard(); 
        $build['form2'] = $this->userBsListInAdminDashBoard(); 
        $build['form3'] = $this->userPrescriptionListInAdminDashboard(); 
      return $build;
    }
    protected function userlistInAdminDashBoard() {
      // Get form post data        
      $email_search_for = \Drupal::request()->request->get('email_search');
      $form_class = '\Drupal\bs_manager\Form\UserListInAdminForm'; 
      $build['form'] = \Drupal::formBuilder()->getForm($form_class);
      $header = [
        'email' => [
          'data' => $this->t('Email'),
          'specifier' => 'email',
        ],
        'full_name' => [
          'data' => $this->t('Full Name'),
          'specifier' => 'full_name',
        ],
      ];      
      $query = \Drupal::entityQuery('user');      
      if ($email_search_for && isset($email_search_for)) {
        $query->condition('mail', '%' . db_like($email_search_for) . '%', 'LIKE');
      }
      $query->condition('roles', 'auxesis_user');
      $all_user_ids = $query->execute();      
      $rows = [];
      if (!empty($all_user_ids)) {
        foreach ($all_user_ids as $user_id) {
          $user_account = User::load($user_id);
          $email = $user_account->getEmail();
          $full_name = $user_account->field_bsuser_fullname->value;
          $row = [];
          $row[] = $email;
          $row[] = $full_name;
          $rows[] = $row;
        }
      }
      $build['table'] = [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $this->t('No record found.'),
      ];
      return $build;      
    }
    protected function userBsListInAdminDashBoard() {
      $email_search_for = \Drupal::request()->request->get('mail_search');
      $form_class = '\Drupal\bs_manager\Form\BsRecordListInAdminForm'; 
      $build['form'] = \Drupal::formBuilder()->getForm($form_class);
      $header = [
          'email' => [
            'data' => $this->t('Email'),
            'specifier' => 'email',
          ],
          'sl_no' => [
            'data' => $this->t('Sl No'),
            'specifier' => 'sl',
          ],
          'bs_level' => [
            'data' => $this->t('BS Level'),
            'specifier' => 'bs_level',
          ],
          'created' => [
            'data' => $this->t('Date & Time'),
            'specifier' => 'created',
          ],
        ];
        if ($email_search_for && isset($email_search_for)) {
          $query = \Drupal::entityQuery('user');
          $query->condition('mail', '%' . db_like($email_search_for) . '%', 'LIKE');
          $query->condition('roles', 'auxesis_user');
          $all_user_ids = $query->execute(); 
        }
        if ($email_search_for && isset($email_search_for) && empty($all_user_ids)) {
          // do nothing
        }
        else {
          $db = \Drupal::database();          
          $query = $db->select('bs_records', 'bs')            
                  ->fields('bs');
          if (!empty($all_user_ids)) {
            $query->condition('bs.uid', $all_user_ids, 'IN');
          }
          $bs_reports = $query->execute();
        }        
        // Build table rows         
        $rows = [];
        if (!empty($bs_reports)) {
          $sl_no = 1;
          foreach ($bs_reports as $single_record) {
            $uid = $single_record->uid;
            $user = User::load($uid);
            $email = $user->getEmail();
            $row = [];
            $row[] = $email;
            $row[] = $sl_no;
            $row[] = $single_record->bs_value;
            $row[] = format_date($single_record->created, 'short');
            $sl_no++;
            $rows[] = $row;  
          }
        }              
        $build['table'] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
          '#empty' => $this->t('No record forund.'),
        ];
      return $build; 
    }
    protected function userPrescriptionListInAdminDashboard() {
      $file_search_for = \Drupal::request()->request->get('file_name');      
      $form_class = '\Drupal\bs_manager\Form\BsPrescriptionListInAdminForm'; 
      $build['form'] = \Drupal::formBuilder()->getForm($form_class);
      $header = [
        'email' => [
          'data' => $this->t('Email'),
          'specifier' => 'email',
        ],
        'sl_no' => [
          'data' => $this->t('Sl No'),
          'specifier' => 'sl',
        ],
        'file_name' => [
          'data' => $this->t('File Name'),
          'specifier' => 'file_name',
        ],        
        'description' => [
          'data' => $this->t('Description'),
          'specifier' => 'description',
        ],
        'file_size' => [
          'data' => $this->t('File Size'),
          'specifier' => 'file_size',
        ],
        'created_date' => [
          'data' => $this->t('Date'),
          'specifier' => 'created_date',
        ],
      ]; 
      $uids = \Drupal::entityQuery('user')
        ->condition('roles', 'auxesis_user')
        ->execute();
      if (!empty($uids)) {
        $query = \Drupal::entityQuery('file');
        $query->condition('status', 1);
        $query->condition('uid', $uids, 'IN');
        if ($file_search_for != '') {
          $query->condition('filename', '%' . db_like($file_search_for) . '%', 'LIKE');
        }
        $file_ids = $query->execute();
      }
      // Intialize table rows
      $rows = [];
      if (!empty($file_ids)) {
        // Get file description
        $db = \Drupal::database();
        $query1 = $db->select('bs_file_details', 'bfs')
          ->fields('bfs')
          ->condition('bfs.fid', $file_ids, 'IN');
        $get_file_description = $query1->execute()->fetchAll();
        $sl_no = 1;
        foreach ($file_ids as $single_file_id) {
          $array_key = array_search($single_file_id, array_column($get_file_description, 'fid')); 
          $file_description = $get_file_description[$array_key]->description;
          $single_file = File::load($single_file_id);
          $get_user_entity = $single_file->getOwner();          
          $row = [];
          $row[] = $get_user_entity->getEmail();
          $row[] = $sl_no;
          $row[] = $single_file->getFilename();
          $row[] = $file_description;
          $row[] = format_size($single_file->getSize());
          $row[] = format_date($single_file->getCreatedTime(), 'custom', 'm/d/Y');
          $sl_no++;
          $rows[] = $row;
        }
      }            
      $build['table'] = [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $this->t('No records found.'),
      ];      
      return $build;
    }
    // User password set function
    public function userPasswordSet($uid, $timestamp, $hash) {
        if (isset($uid) && isset($hash) && isset($timestamp)) {
            $account = User::load($uid);
            if ($account && Crypt::hashEquals($hash, user_pass_rehash($account, $timestamp))) {
                $form_class = '\Drupal\bs_manager\Form\UserPasswordSetForm';
                $build['form'] = \Drupal::formBuilder()->getForm($form_class, $uid);
                return $build;
            }
            else {
                drupal_set_message(t('Your link is invalid.'), 'error');
                return $this->redirect("bs_manager.register");
            }
        }
        else {
            drupal_set_message(t('Your link is invalid.'), 'error');
            return $this->redirect("bs_manager.register");
        } 
    }
    // User my space function
    public function mySpace() {
        $current_user_id = \Drupal::currentUser()->id();
        if (isset($current_user_id)) {
            $form_class = '\Drupal\bs_manager\Form\BsEntryForm';
            $build['form'] = \Drupal::formBuilder()->getForm($form_class);
            $header = [
                'sl_no' => [
                  'data' => $this->t('Sl No'),
                  'specifier' => 'sl',
                ],
                'bs_level' => [
                  'data' => $this->t('BS Level'),
                  'specifier' => 'bs_level',
                ],
                'created' => [
                  'data' => $this->t('Date & Time'),
                  'specifier' => 'created',
                ],
              ];     
            
              $db = \Drupal::database();          
              $query = $db->select('bs_records', 'bs')            
                      ->fields('bs')
                      ->condition('bs.uid', $current_user_id);
              $bs_reports = $query->execute();       
              // Build table rows         
              $rows = [];
              if (!empty($bs_reports)) {
                $sl_no = 1;
                foreach ($bs_reports as $single_record) {
                  $row = [];
                  $row[] = $sl_no;
                  $row[] = $single_record->bs_value;
                  $row[] = format_date($single_record->created, 'short');
                  $sl_no++;
                  $rows[] = $row;
                }
              }                            
              $build['table'] = [
                '#type' => 'table',
                '#header' => $header,
                '#rows' => $rows,
                '#empty' => $this->t('No record forund.'),
              ];
            $build['form1'] = $this->bsReportDetails($current_user_id);                      
        }
        return $build;
    }
    // Function to get all BS entry details
    protected function bsReportDetails($current_user_id) {
      // Get form post data        
      $file_search_for = \Drupal::request()->request->get('file_name');
      $submit_value = \Drupal::request()->request->get('op');
      $form_class = '\Drupal\bs_manager\Form\BsPrescriptionSearchForm'; 
      $build['form'] = \Drupal::formBuilder()->getForm($form_class);
      $header = [
        'sl_no' => [
          'data' => $this->t('Sl No'),
          'specifier' => 'sl',
        ],
        'file_name' => [
          'data' => $this->t('File Name'),
          'specifier' => 'file_name',
        ],        
        'description' => [
          'data' => $this->t('Description'),
          'specifier' => 'description',
        ],
        'file_size' => [
          'data' => $this->t('File Size'),
          'specifier' => 'file_size',
        ],
        'created_date' => [
          'data' => $this->t('Date'),
          'specifier' => 'created_date',
        ],
      ]; 
      $query = \Drupal::entityQuery('file');
      $query->condition('status', 1);
      $query->condition('uid', $current_user_id);
      if ($submit_value == "Filter" && $file_search_for != '') {
        $query->condition('filename', '%' . db_like($file_search_for) . '%', 'LIKE');
      }
      $file_ids = $query->execute();     
      // Intialize table rows
      $rows = [];
      if (!empty($file_ids)) {
        // Get file description
        $db = \Drupal::database();
        $query1 = $db->select('bs_file_details', 'bfs')
          ->fields('bfs')
          ->condition('bfs.fid', $file_ids, 'IN');
        $get_file_description = $query1->execute()->fetchAll();
        $sl_no = 1;
        foreach ($file_ids as $single_file_id) {
          $array_key = array_search($single_file_id, array_column($get_file_description, 'fid')); 
          $file_description = $get_file_description[$array_key]->description;
          $single_file = File::load($single_file_id);
          $row = [];
          $row[] = $sl_no;
          $row[] = $single_file->getFilename();
          $row[] = $file_description;
          $row[] = format_size($single_file->getSize());
          $row[] = format_date($single_file->getCreatedTime(), 'custom', 'm/d/Y');
          $sl_no++;
          $rows[] = $row;
        }
      }            
      $build['table'] = [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $this->t('No records found.'),
      ];      
      return $build;
    }

}