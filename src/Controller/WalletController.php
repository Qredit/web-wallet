<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Endroid\QrCode\Factory\QrCodeFactory;


    
class WalletController extends Controller
{
    private $page_title = "Wallet";   
    
    
    /**
     * @Route("/wallet/sendmultiple/{id}", name="qredit_wallet_send_multiple")
     */
    public function sendmultiple(Request $request, $id)
    {
        $topnav = new TopNavController();
        $rest = new HelperRestCall();

        $topnav->setActive("qredit_wallet_send_multiple");
        $topnav->addItem("qredit_wallet", "Wallet information", "qredit_wallet", ["id" => $id]);
        $topnav->addItem("qredit_wallet_send", "Send Transaction", "qredit_wallet_send", ["id" => $id]);
        $topnav->addItem("qredit_wallet_send_multiple", "Send Multiple Transactions", "qredit_wallet_send_multiple", ["id" => $id]);

        
        $form = $this->createFormBuilder();
        
        $form->setMethod("POST")
        ->setAction($this->generateUrl('qredit_wallet_send_multiple', ["id" => $id]));
        
        if($request->isMethod('POST')){
            $post_data = $request->request->all()["form"];
            if(isset($post_data["send"])){
                $amounttx = $post_data["amountoftx"];
                $crypto = new HelperCrypto();
                $keep_amount = false;
                $keep_vendor = false;
                $transactions = [];
                
                if(isset($post_data["amount"])){
                    $keep_amount = true;
                }
                if(isset($post_data["vendor"])){
                    $keep_vendor = true;
                }
                
                for($i = 0; $i < $amounttx; $i++){
                    $id = $i + 1;
                    if($keep_vendor == true && $keep_amount == true){
                        $transactions[] = $crypto->sendTransaction($post_data["recipient_".$id], $post_data["amount"], $post_data["vendor"], $post_data["passphrase"])->transaction;
                    } else if($keep_amount == true){
                        $transactions[] = $crypto->sendTransaction($post_data["recipient_".$id], $post_data["amount"], $post_data["vendor_".$id], $post_data["passphrase"])->transaction;
                    } else if($keep_vendor == true) {
                        $transactions[] = $crypto->sendTransaction($post_data["recipient_".$id], $post_data["amount_".$id], $post_data["vendor"], $post_data["passphrase"])->transaction;
                    } else {
                        $transactions[] = $crypto->sendTransaction($post_data["recipient_".$id], $post_data["amount_".$id], $post_data["vendor_".$id], $post_data["passphrase"])->transaction;
                    }
                }
                
                $response = $rest->createTransaction($transactions);

				if ($response->data->accept && $response->data->accept[0] && $response->data->accept[0] != '')
				{
				
					$trxids = '';
					foreach ($response->data->accept as $acctrans)
					{
					
						$trxids .= 'TrxID: ' . $acctrans . ', ';
					
					
					}
                
					$this->addFlash(
						'warning', 'Transactions Sent.. TrxID: ' . $trxids
					);
         
				}
				else
				{

					$this->addFlash(
						'warning', 'An Error Occurred'
					);
				
				}


                if($response){
                    return $this->render('qredit/wallet/wallet_send_multipletransactions.html.twig', [
                        'page_title' => $this->page_title,
                        'module_title' => 'Send Multiple Transactions',
                        'topnav' => $topnav->collect(),
                        'id' => $id,
                        'response' => $response,
                    ]);
                }
                
            } elseif(isset($post_data["amounttx"])) {

            $amounttx = $post_data["amounttx"];
            
            $keep_amount = false;
            $keep_vendor = false;
            
            if(isset($post_data["keep"])){
                $keep = $post_data["keep"];
                
                foreach($keep as $key => $value){
                    if($value == "vendorfield"){
                        $keep_vendor = true;
                    }
                    if($value == "amountfield"){
                        $keep_amount = true;
                    }
                }
                
                // Keep same vendorfield
            }
            
            
            for($i = 1; $i < $amounttx+1; $i++){
                $form->add('recipient_'.$i, TextType::class, ['label' => "Recipient: #".$i,]);
                if($keep_amount == false){
                    $form->add('amount_'.$i, TextType::class, ['label' => "Amount for Recipient: #".$i,]);
                }
                if($keep_vendor == false){
                    $form->add('vendor_'.$i, TextType::class, ['label' => "Smartbridge for Recipient: #".$i ,'required' => false]);
                }
            }
            
            if($keep_amount == true){
                $form->add('amount', TextType::class, ['label' => "Amount for all Recipients"]);
            }
            if($keep_vendor == true){
                $form->add('vendor', TextType::class, ['label' => "Smartbridge for all Recipients", 'required' => false]);
            }
            
            $form->add('passphrase', TextType::class, ['label' => "Passphrase"]);
            $form->add("amountoftx", HiddenType::class, ['data' => $amounttx]);
            $form->add('send', SubmitType::class, ['attr' => ['class' => 'btn btn-danger'],]);
            }
            
        } else { // Check how many TX first
            
            $form->add("amounttx", ChoiceType::class, [
                'label' => 'Amount of transactions',
                'choices' => array(
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                    '7' => '7',
                    '8' => '8',
                    '9' => '9',
                    '10' => '10',
                )
            ]);
            $form->add("keep", ChoiceType::class, [
                'expanded' => true,
                'multiple' => true,
                'choices' => array(
                    'Keep same smartbridge' => 'vendorfield',
                    'Keep same amount' => 'amountfield',
                )
            ]);
            $form->add('Create', SubmitType::class, ['attr' => ['class' => 'btn btn-danger'],]);
        }
        
        
        return $this->render('qredit/wallet/wallet_send_multipletransactions.html.twig', [
            'page_title' => $this->page_title,
            'module_title' => 'Send Multiple Transactions',
            'topnav' => $topnav->collect(),
            'id' => $id,
            'form' => $form->getForm()->createView(),
        ]);
        
    }
    
    
    /**
     * @Route("/wallet/send/{id}", name="qredit_wallet_send")
     */
    public function send(Request $request, $id)
    {
        $topnav = new TopNavController();
        $helper = new HelperRestCall();
        $crypto = new HelperCrypto();
        
        $topnav->setActive("qredit_wallet_send");
        $topnav->addItem("qredit_wallet", "Wallet information", "qredit_wallet", ["id" => $id]);
        $topnav->addItem("qredit_wallet_send", "Send Transaction", "qredit_wallet_send", ["id" => $id]);
        $topnav->addItem("qredit_wallet_send_multiple", "Send Multiple Transactions", "qredit_wallet_send_multiple", ["id" => $id]);
        
        $wallet = $helper->getAccount($id);
        $fee = $helper->getFee();
        
        $fee = $crypto->amountToXQR($fee);
        
        if($request->isMethod('POST')){
            
            $post_data = $request->request->all()["form"];
            
            if(is_numeric($post_data["amount"])){
                $send = $crypto->sendTransaction($post_data["recipient"], $post_data["amount"], $post_data["smartbridge"], $post_data["passphrase"]);

                $test = $helper->createTransaction($send->transaction);
                
				if ($test->data->accept && $test->data->accept[0] && $test->data->accept[0] != '')
				{
                
					$this->addFlash(
						'warning', 'Transaction Sent.. TrxID: ' . $test->data->accept[0]
					);
         
				}
				else
				{

					$this->addFlash(
						'warning', 'An Error Occurred'
					);
				
				}
                
            }
            
            
        }
        
        $form = $this->createFormBuilder()
            ->add('recipient', TextType::class, ['label' => "Recipient",])
            ->add('amount', TextType::class, ['label' => "Amount", "help" => "Fee: ".$fee." XQR"])
            ->add('smartbridge', TextType::class, ['label' => "SmartBridge", 'required' => false])
            ->add('passphrase', TextType::class, ['label' => "Passphrase"])
            ->add('Send', SubmitType::class, ['attr' => ['class' => 'btn btn-danger'],])
            ->setMethod('POST')
            ->setAction($this->generateUrl('qredit_wallet_send', ["id" => $id]))
            ->getForm();    
        
        return $this->render('qredit/wallet/wallet_sendtransactions.html.twig', [
            'page_title' => $this->page_title,
            'module_title' => 'Send Transaction',
            'id' => $id,
            'topnav' => $topnav->collect(),
            'wallet' => $wallet,
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/wallet/id/{id}", name="qredit_wallet")
     */
    public function show(Request $request, $id)
    {
        $topnav = new TopNavController();
        $topnav->setActive("qredit_wallet");
        
        $topnav->addItem("qredit_wallet", "Wallet information", "qredit_wallet", ["id" => $id]);
        $topnav->addItem("qredit_wallet_send", "Send Transaction", "qredit_wallet_send", ["id" => $id]);
        $topnav->addItem("qredit_wallet_send_multiple", "Send Multiple Transactions", "qredit_wallet_send_multiple", ["id" => $id]);
        
        $cookie = new HelperCookie();
        $helper = new HelperRestCall();
        $qrCodeFactory = new QrCodeFactory();
        $wallet = $helper->getAccount($id);
        
		if (isset($wallet->statusCode) && $wallet->statusCode == 404)
		{
		
			// Wallet not found - Should redirect
			//return $this->redirectToRoute("qredit_wallet_import");
		
		}
 
 		$transactions = $helper->getTransactionsById($id);
        
        if($request->isMethod('POST')){
            $post_data = $request->request->all()["form"];
            $name = $post_data["name"];
            
            if($cookie->isWalletNameTaken($name) == true){
                $this->addFlash(
                    'warning',
                    "You've labeled a wallet '".$name."' already!"
                    );
            } else {
                $cookie->setName($id, $name);
            }
            return $this->redirectToRoute("qredit_wallet", ["id" => $id]);
        }
        
        $form = $this->createFormBuilder()
        ->add('name', TextType::class, ['data' => $cookie->getName($id), 'label' => " ",])
        ->add('Change', SubmitType::class, ['attr' => ['class' => 'btn btn-danger'],])
        ->setMethod('POST')
        ->setAction($this->generateUrl('qredit_wallet', ["id" => $id]))
        ->getForm();   

        return $this->render('qredit/wallet/wallet_info.html.twig', [
            'page_title' => $this->page_title,
            'module_title' => 'Wallet information',
            'id' => $id,
            'topnav' => $topnav->collect(),
            'wallet' => $wallet,
            'transactions' => $transactions,
            'form' => $form->createView(),
            "qr" => $qrCodeFactory->create("qredit:".$id, ['size' => 100])->writeDataUri(),
        ]);
    }
    
    
    /**
     * @Route("/wallet/verify", name="qredit_wallet_verify")
     */
    public function aWallet_verify(Request $request)
    {
        //$this->setActive("qredit_wallet_verify");
        
        
        /*
         var_dump(Address::fromPassphrase($mnemonic));
         var_dump(PublicKey::fromPassphrase($mnemonic)->getHex());
         var_dump(PrivateKey::fromPassphrase($mnemonic)->getHex());
         */
        
        //$data = json_encode($parameters, true);
        
        if($request->isMethod('POST')){
            
            $post_data = $request->request->all()["form"];
            
            $grace = $post_data["grace"];
            
            $graceinarray = explode(" ", $grace);
            $status = true;
            
            foreach($post_data as $key => $value){
                if(strpos($key, 'passphraseword_') !== false){
                    $wordnumber = explode("_", $key);
                    $wordnumber = $wordnumber[1];
                    if($post_data["passphraseword_".$wordnumber] == $graceinarray[$wordnumber-1]){
                        $status = true;
                    } else {
                        $status = false;
                    }
                }
            }
            
            if($status == true){
                $status = true;
                $crypto = new HelperCrypto();
                $address = $crypto->getAddressFromPassPhrase($grace);
                
                $cookie = new HelperCookie();
                $cookie->insertWallet($address);
                return $this->redirectToRoute("qredit_wallet", ["id" => $address]);
            }
            
            return $this->render('qredit/wallet/verify_wallet.html.twig', [
                'page_title' => $this->page_title,
                'module_title' => 'Make Wallet',
                'status' => $status
            ]);
        }
        
        

    }
    
    /**
     * @Route("/wallet/make", name="qredit_wallet_make")
     */
    public function aWallet_make(Request $request)
    {
        $crypto = new HelperCrypto();
        $verse = $crypto->generate_passphrase();
        

        /*
        var_dump(Address::fromPassphrase($mnemonic));
        var_dump(PublicKey::fromPassphrase($mnemonic)->getHex());
        var_dump(PrivateKey::fromPassphrase($mnemonic)->getHex());
        */

        $form = $this->createFormBuilder()
        ->add('Make new wallet', SubmitType::class, [
            'attr' => ['class' => 'btn btn-danger'],
        ])->add('passphrase', HiddenType::class, array(
            'data' => $verse,
        ))
        ->getForm();
        

        
        $form->handleRequest($request);

      
        
        if ($form->isSubmitted()) {

            $formData = $form->getData();
            $grace =  $formData["passphrase"];
            
            $numbers = range(1, 12);
            shuffle($numbers);
            $numbersArray = array_slice($numbers, 0, 3);
            
            $verifyform = $this->createFormBuilder();
            
            for($i = 0; $i < count($numbersArray); $i++){
                $verifyform->add('passphraseword_'.$numbersArray[$i], TextType::class, [
                    "label" => "Insert word number: ".$numbersArray[$i]

                ]);
               
            }
            
            $verifyform->add('Verify', SubmitType::class, [
                'attr' => ['class' => 'btn btn-danger'],
            ])->add("grace", HiddenType::class, [
                'data' => $grace
            ])->setMethod('POST')->setAction($this->generateUrl('qredit_wallet_verify'))->getForm();           
            
            $test = $verifyform->getForm()->createView();
                
            return $this->render('qredit/wallet/verify_wallet.html.twig', [
                'page_title' => $this->page_title,
                'module_title' => 'Make Wallet',
                'form' => $test,
            ]);
            
        } 
        
        return $this->render('qredit/wallet/make.html.twig', [
            'page_title' => $this->page_title,
            'module_title' => 'Make Wallet',
            'form' => $form->createView(),
            'verse' => $verse
        ]);
    }
   
    
    /**
     * @Route("/wallet/import", name="qredit_wallet_import")
     */
    public function awallet_import(Request $request)
    {
        $crypto = new HelperCrypto();
        
        if($request->isMethod('POST')){
            
            $post_data = $request->request->all()["form"];
            $input = $post_data["input"];
            $words = explode(" ", $input);
            
            if(count($words) > 0){
                $address = $crypto->getAddressFromPassPhrase($input);
                $cookie = new HelperCookie();
                $cookie->insertWallet($address);
            }
            
            return $this->redirectToRoute('qredit_wallet', ["id" => $address]);
            
        } else {
        
            $form = $this->createFormBuilder()->add('input', TextType::class, [
                'label' => "Insert pass phrase, wif or private key",
            ])->add('Import', SubmitType::class, [
                'attr' => ['class' => 'btn btn-danger'],
            ])->setMethod('POST')->setAction($this->generateUrl('qredit_wallet_import'))->getForm();
            
            return $this->render('qredit/wallet/import.html.twig', [
                'page_title' => $this->page_title,
                'module_title' => 'Import Wallet',
                'form' => $form->createView(),
            ]);
        }
    }
    
    
    /**
     * @Route("/logout", name="qredit_logout")
     */
    public function aLogout(Request $request)
    {
        $status = false;
        if($request->isMethod('POST')){
            $cookie = new HelperCookie();
            $cookie->deleteAll();
            $status = true;
            return $this->redirectToRoute('index');
        } 
        
        $form = $this->createFormBuilder()
        ->add('Yes', SubmitType::class, ['attr' => ['class' => 'btn btn-danger'],])
        ->setMethod('POST')
        ->setAction($this->generateUrl('qredit_logout'))
        ->getForm();
        
        return $this->render('qredit/wallet/logout.html.twig', [
            'page_title' => $this->page_title,
            'module_title' => 'Logout',
            'form' => $form->createView(),
            'status' => $status,
        ]);
    }
}
