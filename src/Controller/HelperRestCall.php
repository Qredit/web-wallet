<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class HelperRestCall
{
    
    public function __construct()
    {
    }
    
    
    /* EXPLORER */
    
    public function getSupply(){
        $params = [];
        $base = $this->buildRequest("blocks/getSupply");
        
        //$req = $this->getRequest($base);
        //if($req){
        //    return $req->supply;
        //}
        
        return "0";
    }
    
    public function getFee(){
        $base = $this->buildRequest("transactions/fees");
        
        $req = $this->getRequest($base);
        if($req){
            return $req->data->transfer;
        }
        
        return "0";
    }
    
    public function getFees(){
        $base = $this->buildRequest("transactions/fees");
        
        $req = $this->getRequest($base);
        
        return $req->data;
    }
    
    public function getTransactions(){
        $params = [];
        $params["limit"] = 20;
        $base = $this->buildRequest("transactions/search", $params);
        
		$fields = [];
		$fields["orderBy"] = "timestamp:desc";
        $req = $this->postRequest($base, $fields);

        return $req->data;
    }
    
    public function getTransactionsById($address){
    
        $transactionsBySender = $this->getTransactionsBySender($address);
        $transactionsByRecipient = $this->getTransactionsByRecipient($address);       
        
        
        $transactionsArray = [];
        $totaltransactions = count($transactionsBySender) + count($transactionsByRecipient);
            
        if(count($transactionsBySender) > 0){
            for($i = 0; $i < count($transactionsBySender); $i++){
                $transactionsBySender[$i]->role = "sender";
                    
                $transactionsArray[] = $transactionsBySender[$i];
            }
        }
        if(count($transactionsByRecipient) > 0){
            for($i = 0; $i < count($transactionsByRecipient); $i++){
                $transactionsByRecipient[$i]->role = "recipient";
                    
                $transactionsArray[] = $transactionsByRecipient[$i];
            }
        }

        usort($transactionsArray, function($b, $a)
        {
            return strcmp($a->timestamp->epoch, $b->timestamp->epoch);
        });

        $transactions = array();
        $transactions['count'] = $totaltransactions;
        $transactions['transactions'] = $transactionsArray;


        /*
        echo "<pre>";
        print_r($transactionsArray);
        echo "</pre>";
        exit;
        */ 
            
        return (object)$transactions;
            

    }
    
    public function getTransactionsBySender($address){
        $params = [];
        $params["limit"] = 20;
        $params["page"] = 1;
        $base = $this->buildRequest("transactions/search", $params);
        
		$fields = [];
		$fields["senderId"] = $address;
		$fields["orderBy"] = "timestamp:desc";
        
        $req = $this->postRequest($base, $fields);
        
        if (isset($req->data))
        {
        
        	return $req->data;
        	
        }
        else
        {
        
        	return array();
        
        }
        
    }
    
    public function getTransactionsByRecipient($address){
        $params = [];
        $params["limit"] = 20;
        $params["page"] = 1;
        $base = $this->buildRequest("transactions/search", $params);
        
		$fields = [];
		$fields["recipientId"] = $address;
		$fields["orderBy"] = "timestamp:desc";
        $req = $this->postRequest($base, $fields);
        
        if (isset($req->data))
        {
        
        	return $req->data;
        	
        }
        else
        {
        
        	return array();
        
        }
        
    }
    
    public function getBlocks(){
        $params = [];
        $params["limit"] = 20;
        $params["page"] = 1;
        $base = $this->buildRequest("blocks/search", $params);

		$blockheight = $this->getBlockHeight();
		

		$fields = [];
		$fields["height"]["from"] = $blockheight - $params["limit"];
        $req = $this->postRequest($base, $fields);
        
        return $req->data;
    }

    public function getBlockHeight(){
        $params = [];
        $base = $this->buildRequest("node/configuration", $params);
        
        $req = $this->getRequest($base);
        return $req->data->constants->height;
    }
    
    public function getAccount($id){
        //$params = [];
        //$params["address"] = $id;
        $base = $this->buildRequest("wallets/" . $id);
        $req = $this->getRequest($base);
        
		if (isset($req->data))
		{
        	return $req->data;
        }
        else
        {
        	return $req;
        }
        
    }
    
    public function getTopAccounts(){
        $params = [];
        $params["limit"] = 50;
        $base = $this->buildRequest("wallets/top", $params);
        $req = $this->getRequest($base);
        return $req->data;
    }
    
    public function getDelegates(){
        $base = $this->buildRequest("delegates");
        $req = $this->getRequest($base);
        return $req->data;
    }
    
    public function getLowestHeightPeer(){
        $peers = $this->getPeers()->peers;
        $activePeer = "";
        $initHeight = 1000000000;
        for($i = 0; $i < count($peers); $i++){
            if($peers[$i]->height < $initHeight){
                $initHeight = $peers[$i]->height;
                $activePeer = $peers[$i];
            }
        }
        return $activePeer;
    }
    
    public function getHighestHeightPeer(){
        $peers = $this->getPeers()->data;
        $activePeer = "";
        $initHeight = 0;
        for($i = 0; $i < count($peers); $i++){
            if($peers[$i]->height > $initHeight){
                $initHeight = $peers[$i]->height;
                $activePeer = $peers[$i];
            }
        }
        return $activePeer;
    }
    
    public function getLeastDelayPeer(){
        $peers = $this->getPeers()->data;
        $activePeer = "";
        $initDelay = 1000;
        for($i = 0; $i < count($peers); $i++){
            if($peers[$i]->delay < $initDelay){
                $initDelay = $peers[$i]->height;
                $activePeer = $peers[$i];
            }
        }
        return $activePeer;
    }
    
    public function getBestPeer(){
        $peers = $this->getPeers();
        
        usort($peers, function($a, $b)
        {
            return strcmp($a->delay, $b->delay);
        });
        
        print_r($peers);
        die();
        
        return $peers;
        
    }
    
    public function getPeers(){
        $base = $this->buildRequest("peers");
        $req = $this->getRequest($base);
        return $req->data;
    }
    
    public function createTransaction($transaction, $peer=""){        
        $curl = curl_init();
        
        $url = [];        
        //$url = [];
        $url["transactions"] = [];
        if(is_array($transaction)){
            for($i = 0; $i < count($transaction); $i++){
                $url["transactions"][] = $transaction[$i];
            }
        } else {
            $url["transactions"][] = $transaction;
        }
        
        $params = json_encode($url);
        
        if($peer != ""){
            $peer_address = $peer;
        } else {
            $peer_address = "qredit.cloud/api/v2";
        }
        
        // http://".$ip->ip.":".$ip->port."/peer/transactions
        // CURLOPT_URL => "http://api.qredit.cloud/peer/transactions",
        
        //    CURLOPT_HTTPHEADER => [
        //        'Content-Type: application/json',
        //        'nethash: 5e67037fd290ba7ab378e84a591d251c46eb9770eb134983771fd602233bf193',
        //        'version: 0.0.1',
        //        'os: qae-dashboard',
        //        'port: 1'],
        
        curl_setopt_array($curl, array(
        	CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => "https://".$peer_address."/transactions",
            CURLOPT_USERAGENT => 'HodlerCompany',
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'],
        ));
        
        $resp = curl_exec($curl);
        curl_close($curl);

        return json_decode($resp);
    }
    
    
    /* MAKE THE GET REQUEST */
    
    
    public function buildRequest($base, $params=[]){       


        if(!empty($params)){
            foreach($params as $key => $value){
                reset($params);
                if ($key === key($params)){
                    $base.= "?".$key."=".$value;
                } else {
                    $base.= "&".$key."=".$value;
                }
            }
        }
        
        return $base;
    }
    
    public function getRequest($request, $peer=""){
        $curl = curl_init();
        
        if($peer == ""){
            $peer = "https://qredit.cloud/api/v2/";
            $base = $peer.$request;
            //$port = 4100;
        } else { // Connect other peer
            $base = $peer.$request;
            //$port = 4101;
        }
        
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $base,
            CURLOPT_USERAGENT => 'HodlerCompany',
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false
        ));
        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);
        
        if($resp != null){
            return json_decode($resp);
        } else {
            return $this->getRequest($request, "http://136.144.215.24:4101/");
        }
    }

    public function postRequest($request, $postData, $peer=""){
        $curl = curl_init();
        
        if($peer == ""){
            $peer = "https://qredit.cloud/api/v2/";
            $base = $peer.$request;
            //$port = 4100;
        } else { // Connect other peer
            $base = $peer.$request;
            //$port = 4101;
        }
        
        curl_setopt_array($curl, array(
        	CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $base,
            CURLOPT_USERAGENT => 'HodlerCompany',
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            //CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => json_encode($postData)
        ));
        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);

        if($resp != null){
            return json_decode($resp);
        } else {
            return $this->postRequest($request, $postData, "http://136.144.215.24:4101/");
        }
    }
    
    
}