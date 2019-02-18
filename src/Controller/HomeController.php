<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $active = "home";
    
    private function setActive($id){
        $this->active = $id;
    }
    
    private function TopNav(){
        $array = [];
        $array[] = ["id" => "home", "name" => "Homepage", "url" => "index"];
        $array[] = ["id" => "about", "name" => "About", "url" => "about"];


        
        for($i = 0; $i < count($array); $i++){
            if($array[$i]["id"] == $this->active){
                $array[$i]["active"] = true;
            }
        }
       
        return $array;
    }
    
    
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        
        return $this->render('home/index.html.twig', [
            'controller_name' => 'Qredit',
            'topnav' => $this->TopNav(),
        ]);
    }

    /**
     * @Route("/about", name="about")
     */
    public function aboutus()
    {
        $this->setActive("about" ); 
        return $this->render('home/about.html.twig', [
            'controller_name' => 'Qredit',
            'topnav' => $this->TopNav(),
        ]);
    }
 

}
