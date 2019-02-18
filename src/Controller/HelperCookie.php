<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class HelperCookie
{
    private $tenyears;
    
    public function __construct()
    {
        $this->tenyears = time() + (10 * 365 * 24 * 60 * 60);
    }

    public function insertWallet($id){
        
        if(!isset($_COOKIE["wallets"])){
            setcookie("wallets[0]", $id, $this->tenyears, "/");
        } else {
            setcookie("wallets[".count($_COOKIE["wallets"])."]", $id, $this->tenyears, "/");
        }
        
    }
    
    public function getName($address){
        $index = $this->getWalletIndexByAddress($address);
        if(isset($_COOKIE["name"][$index])){
            return $_COOKIE["name"][$index];
        } else {
            return "";
        }
    }
    
    public function setName($address, $name){
        $index = $this->getWalletIndexByAddress($address);
        setcookie("name[".$index."]", $name, $this->tenyears, "/");
    }
    
    public function isWalletNameTaken($name){
        $return = false;
        foreach($_COOKIE as $key => $value){
            if($key == "name" && is_array($value)){
                foreach($_COOKIE[$key] as $index => $value2) {
                    if($value2 == $name){
                        $return = true;
                    }
                }
            }
        }
        return $return;
    }
    
    public function getWalletIndexByAddress($address){
        foreach($_COOKIE as $key => $value){
            if($key == "wallets" && is_array($value)){
                foreach($_COOKIE[$key] as $name => $value2) {
                    if($value2 == $address){
                        return $name;
                    }
                }
            }
        }
    }
    
    public function deleteAll($clearSession=false){
        
        foreach($_COOKIE as $key => $value){
            if(is_array($value)){
                foreach($_COOKIE[$key] as $name => $value2) {
                    setcookie("".$key."[".$name."]", "", time() - 3600, "/");
                }
            }    
        }
    }

}