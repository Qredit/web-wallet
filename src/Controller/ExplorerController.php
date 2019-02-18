<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;



class ExplorerController extends AbstractController
{
    private $page_title = "Explorer";
    private $topnav;
    
    public function __construct(UrlGeneratorInterface $router){
        $this->topnav = new TopNavController();
        $this->topnav->addItem("qredit_explorer", "Latest Transactions", "qredit_explorer");
        $this->topnav->addItem("qredit_explorer_blocks", "Latest Blocks", "qredit_explorer_blocks");
        $this->topnav->addItem("qredit_explorer_top_accounts", "Top Accounts", "qredit_explorer_top_accounts");
        $this->topnav->addItem("qredit_explorer_delegates", "Delegates", "qredit_explorer_delegates");
    }
    
    
    
    /*
export enum TransactionType {
  SendArk = 0,
  SecondSignature = 1,
  CreateDelegate = 2,
  Vote = 3,
  MultiSignature = 4,
}
     */
    
    public function getSupply(){
        $helper = new HelperRestCall();
        $supply = $helper->getSupply();
        return $supply;
    }
    
    /**
     * @Route("/explorer", name="qredit_explorer")
     */
    public function transactions()
    {
        $this->topnav->setActive("qredit_explorer");

        
        $helper = new HelperRestCall();
        
        //print_r($helper->getTransactions());
        //exit;
        
        return $this->render('qredit/explorer/transactions.html.twig', [
            'page_title' => $this->page_title,
            'module_title' => $this->topnav->getActiveTitle(),
            'response' => $helper->getTransactions(),
            'topnav' => $this->topnav->collect(),
        ]);
    }
    
    /**
     * @Route("/explorer/blocks", name="qredit_explorer_blocks")
     */
    public function blocks()
    {
        $this->topnav->setActive("qredit_explorer_blocks");
        
        $helper = new HelperRestCall();
        
        return $this->render('qredit/explorer/blocks.html.twig', [
            'page_title' => $this->page_title,
            'module_title' => $this->topnav->getActiveTitle(),
            'response' => $helper->getBlocks(),
            'topnav' => $this->topnav->collect(),
        ]);
    }
    
    /**
     * @Route("/explorer/top/accounts", name="qredit_explorer_top_accounts")
     */
    public function accounts()
    {
        $this->topnav->setActive("qredit_explorer_top_accounts");
        
        $helper = new HelperRestCall();
        
        return $this->render('qredit/explorer/top_accounts.html.twig', [
            'page_title' => $this->page_title,
            'module_title' => $this->topnav->getActiveTitle(),
            'response' => $helper->getTopAccounts(),
            'topnav' => $this->topnav->collect(),
        ]);
        
        /*
        $autoload = [];
        $accounts = [];
        $accounts["targetdiv"] = "accounts";
        $accounts["targetapi"] = $this->generateUrl('api_accounts_top');
        $autoload[] = $accounts;
        
        return $this->render('api/api_base.html.twig', [
            'page_title' => $this->page_title,
            'module_title' => $this->topnav->getActiveTitle(),
            'topnav' => $this->topnav->collect(),
            'autoload' => $autoload,
        ]);
        */
    }
    
    /**
     * @Route("/explorer/delegates", name="qredit_explorer_delegates")
     */
    public function delegates()
    {
        $this->topnav->setActive("qredit_explorer_delegates");
        
        $helper = new HelperRestCall();
        
        return $this->render('qredit/explorer/delegates.html.twig', [
            'page_title' => $this->page_title,
            'module_title' => $this->topnav->getActiveTitle(),
            'response' => $helper->getDelegates(),
            'topnav' => $this->topnav->collect(),
        ]);
        
        /*
        $autoload = [];
        $delegates = [];
        $delegates["targetdiv"] = "delegates";
        $delegates["targetapi"] = $this->generateUrl('api_delegates_index');
        $autoload[] = $delegates;
      
        return $this->render('api/api_base.html.twig', [
            'page_title' => $this->page_title,
            'module_title' => $this->topnav->getActiveTitle(),
            'autoload' => $autoload,
            'topnav' => $this->topnav->collect(),
        ]);
        */
    }
}
