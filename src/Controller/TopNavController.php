<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class TopNavController
{
    
    private $array = [];
    private $active = "";
    
    public function __construct()
    {
    }
    
    public function setActive($id){
        $this->active = $id;
    }
    
    public function getActiveItem(){
        $id = $this->active;
        $array = $this->array;
        
        for($i = 0; $i < count($array); $i++){
            if($array[$i]["id"] === $id){
                return $array[$i];
            }
        }
    }
    
    public function getActiveTitle(){
        $active = $this->getActiveItem();
        return $active["name"];
    }


    public function addItem($id, $name, $url, $params=[]){
        
        $array = [];
        $array["id"] = $id;
        $array["name"] = $name;
        $array["url"] = $url;
        if(count($params) > 0){
            $array["pathParams"] = $params;
        }
        array_push($this->array, $array);
    }

    public function collect(){
        $array = $this->array;
        
        for($i = 0; $i < count($array); $i++){
            if($array[$i]["id"] == $this->active){
                $array[$i]["active"] = true;
            }
        }
        
        return $array;
    }
    
}