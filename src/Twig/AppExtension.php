<?php 
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('xqr', array($this, 'priceFilter')),
            new TwigFilter('tooltip', array($this, 'tooltipFilter')),
            new TwigFilter('blockdate', array($this, 'blockdateFilter')),
        );
    }

    public function priceFilter($number, $decimals = 2, $decPoint = ',', $thousandsSep = '.')
    {
        
        $price = $number/100000000;
        $price = number_format($price, $decimals, $decPoint, $thousandsSep);
        $price = $price;

        return $price;
    }
    
    public function blockdateFilter($unix){
        $date = strtotime("21 March 2017 13:00");
        $blockdate = $date + $unix;
        return date('d-m-Y H:i', $blockdate);
    }
    
    public function tooltipFilter($string){
        $newstring = substr($string, 0, 5);
        $newstring .= "...";
        $newstring .= substr($string, -5, 5);
        
        $html = 'data-toggle="tooltip" data-placement="bottom" title="'.$string.'"';
        
        return "<span class=\"mouseover\" onmouseup=\"copyToClipFromId('".$string."')\" ".$html." >".$newstring."</span>";
    }
    
}