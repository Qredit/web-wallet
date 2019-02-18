<?php
namespace App\Controller;

use ArkEcosystem\Crypto\Networks\Mainnet;
use ArkEcosystem\Crypto\Configuration\Network;
use ArkEcosystem\Crypto\Identities\Address;
use ArkEcosystem\Crypto\Identities\PrivateKey;
use ArkEcosystem\Crypto\Identities\PublicKey;
use ArkEcosystem\Crypto\Transactions\Builder\Transfer;
use BitWasp\Bitcoin\Mnemonic\MnemonicFactory;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use Symfony\Component\HttpFoundation\Response;

class HelperCrypto
{
    
    public function __construct()
    {
    	Network::set(Mainnet::new());
    }
    
    public function generate_passphrase(){
        $random = new Random();
        $entropy = $random->bytes(16);
        
        $bip39 = MnemonicFactory::bip39();
        $words = $bip39->entropyToWords($entropy);
        
        return implode(" ", $words);
    }
    
    public function amountToXQR($amount){
        $amount = $amount / 10 ** 8;
        return $amount;
    }
    
    public function xqrToAmount($xqr){
        $xqr = $xqr * 10 ** 8;
        return $xqr;
    }
    
    public function sendTransaction($recipient, $amount, $smartbridge, $passphrase){
        $transaction = Transfer::new()
        ->recipient($recipient)
        ->amount($amount * 10 ** 8)
        ->vendorField($smartbridge)
        ->sign($passphrase);
        
        return $transaction;
    }
    
    public function getAddressFromPassPhrase($input){
        $address = Address::fromPassphrase($input);
        return $address;
    }

    public function getPrivateKeyFromPassPhrase($input){
        $privatekey = PrivateKey::fromPassphrase($input);
        return $privatekey->getHex();
    
    }
    
}