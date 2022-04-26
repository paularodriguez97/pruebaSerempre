<?php

namespace Drupal\my_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;

/**
* Implements the Register form controller.
*
* @see \Drupal\Core\Form\FormBase
*/

class Register extends FormBase {
    
    protected $database;
    protected $currentUser;

    public function __construct(Connection $database, AccountInterface $current_user) {
        $this->database = $database;
        $this->currentUser = $current_user;
    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('database'),
            $container->get('current_user')
        );
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['name'] = [
            '#type' => 'textfield',
            '#title' => 'Username:',
            '#description' => $this->t('Fill in the full name of the user here.'),
            '#size' => 19,
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => 'Submit',
            // '#submit' => ['::submitModal'],
            '#attributes' => [
              'class' => ['button--submit'],
            ],
        ];

        $form['#attributes']['class'][] = 'form_general';
        $form['#id'] = 'my_form';
        $form['#attached']['library'][] = 'my_module/js_validate';

        return $form;
    }
    public function getFormId() {
        return 'my_module_forms_register';
    }
    public function validateForm(array &$form, FormStateInterface $form_state) {
        $name = $form_state->getValue('name');
        if (strlen($name) < 5) {
          $form_state->setErrorByName('name', $this->t('The name field must have a minimum of 5 characters.'));
        }

        
        if ($name) {
            $query = $this->database->select('my_users', 'my');
            $query->addField('my', 'name', 'name');
            $result = $query->execute()->fetchAll();
            $names = [];
            if($result){
                foreach($result as $item){
                    $names[] = $item->name;
                }
            }

            if (in_array($name, $names)) {
                $form_state->setErrorByName('name', $this->t('The name field has already been registered.'));
            }
        }
    }
    public function submitForm(array &$form, FormStateInterface $form_state) {
        
        $name = $form_state->getValue('name');

        $this->database->insert('my_users')
        ->fields([
            // 'id' => $this->currentUser->id(),
            'name',
        ])
        ->values([
            'name' => $form_state->getValue('name'),
        ])
        ->execute();

        $form_state->cleanValues();

    }
    public function submitModal(array &$form, FormStateInterface $form_state) {
        $form_state->setRebuild();
    }
}