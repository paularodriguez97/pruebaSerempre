<?php
/**
* @file
* Contains \Drupal\my_module\Controller\MyUsers.
*/
namespace Drupal\my_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
* Controlador para devolver el contenido de las páginas definidas
*/
class MyUsers extends ControllerBase {

    /**
     * Conexión de la base de datos.
     *
     * @var Connection
     */
    private $db;
  

    /**
     * {@inheritdoc}
     */
    public function __construct(Connection $db) {
        $this->db = $db;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('database')
        );
    }


    public function prueba() {
        $data = [];

        $data = [
            '#markup' => '<p>' . $this->t('Hello, this is my
            first module in Drupal 8!') . '</p>',
        ];
        
        return $data;

        // $query = $this->db->select('my_users', 'myu');
        // $query->fields('myu', ['name']);

        // $table = $query->execute()->fetchAll();
        // return $table;
        // ksm($table);

    }
}