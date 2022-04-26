<?php

namespace Drupal\my_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;



/**
* Implements the Import form controller.
*
* @see \Drupal\Core\Form\FormBase
*/

class Import extends FormBase {
    
    /**
     * @var \Drupal\Core\Messenger\MessengerInterface
     */
    protected $messenger;

    protected $database;
    protected $currentUser;

     /**
     * @var FileSystem
     */
    protected $fileSystem;

    /**
     * @var EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    public function __construct(Connection $database, AccountInterface $current_user, MessengerInterface $messenger, FileSystem $fileSystem, EntityTypeManagerInterface $entityTypeManager) {
        $this->database = $database;
        $this->currentUser = $current_user;
        $this->messenger = $messenger;
        $this->fileSystem = $fileSystem;
        $this->entityTypeManager = $entityTypeManager;
    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('database'),
            $container->get('current_user'),
            $container->get('messenger'),
            $container->get('file_system'),
            $container->get('entity_type.manager')
        );
    }

    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['file'] = [
            '#type' => 'managed_file',
            '#title' => 'File:',
            '#description' => $this->t('Please upload the file with the names of the users.'),
            '#upload_location' => 'public://'. $this->dirName,
            '#name' => 'my_file',
            '#size' => 29,
            '#upload_validators' => [
                'file_validate_extensions' => ['csv'],
            ],
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => 'Submit',
            '#attributes' => [
              'class' => ['button--submit'],
            ],
        ];

        $form['#attributes']['class'][] = 'form_general';
        $form['#id'] = 'my_form_import';
        $form['#attached']['library'][] = 'my_module/js_validate';

        return $form;
    }
    public function getFormId() {
        return 'my_module_forms_import';
    }


    public function submitForm(array &$form, FormStateInterface $form_state) {
        $csv = $form_state->getValue('file');
        $file_csv = $this->entityTypeManager->getStorage('file')->load($csv[0]);
        $uri_csv = $file_csv->getFileUri();
        $lineNumber = 0;
        if (($handle = fopen($uri_csv, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($lineNumber != 0) {
                // $row[]['name'] = $data[0];
                // $process[] = ['::operations', [$data[0]]];
                $operations[] = ['\Drupal\my_module\Form\Import::insertName', [$data[0]]];
            }
            $lineNumber++;
          }
        }
        
        $batch = [
            'title' => 'Update imports in my module ...',
            'operations' => $operations,
            'finished' => $this->messenger->addMessage("Termino de ejecutar"),
          ];
        batch_set($batch);
    }

    public function insertName($data){
        \Drupal::database()->insert('my_users')
        ->fields([
            'name',
        ])
        ->values([
            'name' => $data,
        ])
        ->execute();
    }

}

