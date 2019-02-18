<?php
namespace App\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\Response;

use Endroid\Qrcode\QrCode;
use Endroid\QrCode\Factory\QrCodeFactory;
use Endroid\QrCode\Response\QrCodeResponse;


class ApiController extends FOSRestController
{
    
    /**
     * GET Route annotation.
     * @Get("/api/accounts/top", name="api_accounts_top")
     */
    public function aApiTopAccounts(){
        $helper = new HelperRestCall();
        
        return $this->render('api/accounts/top.html.twig', [
            'module_title' => "Accounts",
            'response' => $helper->getTopAccounts(),
            'supply' => $helper->getSupply(),
        ]);
    }
    
    /**
     * GET Route annotation.
     * @Get("/api/transaction/builder/{recipient}/{amount}/{smartbridge}/{passphrase}", name="api_transaction_builder");
     */
    public function aTransactionBuilder($recipient="", $amount = "", $smartbridge="", $passphrase=""){
        $helper = new HelperRestCall();
        $crypto = new HelperCrypto();
        
        $transaction = $crypto->sendTransaction($recipient, $amount, $smartbridge, $passphrase);
        
        
        $send = $helper->createTransaction($transaction->transaction);
        
        $json = json_encode($send);
        return new Response($json, "200", array(
            'Content-Type' => 'application/json'
        ));
    }
    
    /**
     * GET Route annotation.
     * @Get("/api/delegates", name="api_delegates_index")
     */
    public function aApiDelegates(){
        $helper = new HelperRestCall();
        
        return $this->render('api/delegates/index.html.twig', [
            'module_title' => "Delegates",
            'response' => $helper->getDelegates(),
            'supply' => $helper->getSupply(),
        ]);
    }
    
    /**
     * GET Route annotation.
     * @Get("/api/test", name="api_test")
     */
    public function aTest(){
        $helper = new HelperRestCall();
        $json = json_encode($helper->getTopAccounts());
        return new Response($json, "200", array(
            'Content-Type' => 'application/json'
        ));
    }
    
    /**
     * GET Route annotation.
     * @Get("/api/getidfrompassphrase/{slug}", name="api_get_id_from_passphrase")
     */
    public function aGetIdFromPassPhrase($slug=""){
        $crypto = new HelperCrypto();
        
        $array = ["address" => $crypto->getAddressFromPassPhrase($slug)];
        
        $json = json_encode($array);
        
        return new Response($json, "200", array(
            'Content-Type' => 'application/json'
        ));
    }
    
    /**
     * GET Route annotation.
     * @Get("/api/wallet/qr/id/{id}", name="api_wallet_qr")
     */
    public function aMakeQrCodeFromID($id){
        $qrCodeFactory = new QrCodeFactory();
        $qr = $qrCodeFactory->create($id, ['size' => 200]);
        $data = $qr->writeDataUri();
        

        return new Response($data, "200", array(
            'Content-Type' => 'text/html',
        ));
        
    }
    
    /**
     * GET Route annotation.
     * @Get("/api/wallet/new", name="api_make_wallet")
     */
    public function aMakePassPhrase(){
        $crypto = new HelperCrypto();
        
        $passphrase = $crypto->generate_passphrase();
        $address = $crypto->getAddressFromPassPhrase($passphrase);
        $privatekey = $crypto->getPrivateKeyFromPassPhrase($passphrase);
        
        $array = ["address" => $address, "privatekey" => $privatekey, "passphrase" => $passphrase];
        
        $json = json_encode($array);
        
        return new Response($json, "200", array(
            'Content-Type' => 'application/json'
        ));
    }

}