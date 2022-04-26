<?php

namespace Drupal\my_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Response;

class Consult extends ControllerBase{

        /**
     * Variable para formatear conexion con la base de datos.
     *
     * @var \Drupal\Core\Database\Connection
     */
    protected $database;
    

    /**
     * {@inheritdoc}
     */
    public function __construct(Connection $connection) {
        $this->database = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
        $container->get('database')
        );
    }


    public function view(){
        $query = $this->database->select('my_users', 'my');
        $query->addField('my', 'id', 'id');
        $query->addField('my', 'name', 'name');
        $result = $query->execute()->fetchAll();
        
        $rows = [];
        if($result){
            foreach($result as $item){
                $rows[] = [$item->id, $item->name];
            }
        }
        return [
            [
                "#type" => "table",
                "#header" => [
                    "id" => "ID",
                    "name" => "NAME",
                ],
                "#rows" => $rows,
            ],
            [
                '#type' => 'pager',
                '#quantity' => 3
            ],
            [
                "#type" => "link",
                '#url' => Url::fromRoute("my_module.consult_csv"),
                "#title" => "CSV",
            ]
            
        ];
    }

    public function csv(){
        $query = $this->database->select('my_users', 'my');
        $query->addField('my', 'id', 'id');
        $query->addField('my', 'name', 'name');
        $result = $query->execute()->fetchAll();

        $output = "";
        $header = [
            "id" => "ID",
            "name" => "NAME",
        ];

        foreach ($header as $key => $value) {
            if ($key == 'name') {
                $output .= $value . "\r\n";
            }
            else {
                $output .= $value . ",";
            }
        }
        
        foreach ($result as $item) {
            foreach ($item as $key => $value) {
                if($key == "name"){    
                    $output .= '"'.$value.'"' . "\r\n";
                }
                else{
                    $output .= '"'.$value.'",';
                }
            }
        }

        // return[];
        $response = new Response();
        $response->headers->set('Content-Type', 'application/excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=My-users.csv');
        $response->headers->set('Expires', '0');
        $response->headers->set('Cache-Control', 'must-revalidate');
        $response->headers->set('Pragma', 'public');
        $response->setContent($output);

        return $response;
    }
}