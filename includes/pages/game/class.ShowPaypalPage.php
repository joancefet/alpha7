<?php

require_once(ROOT_PATH . 'includes/classes/class.paypal.php');

class ShowPaypalPage extends AbstractPage
{
        const MAIL        = 'ncn71008@toiea.com';

        public $pattern        = array(
                
                120000        => '5.00',
                300000        => '10.00',
                850000        => '25.00',
                1750000        => '50.00',
                4000000        => '80.00',
               
        );
        public $cost        = '0';
        public $amount        = '0';

        function __construct() {
                parent::__construct();
				
				
				$action = HTTP::_GP('action', '');
                switch ($action)
                {
                        case 'success':
                                $this->IPN();
                        break;
                        case 'cancel':
                                $this->Cancel();
                        break;
                        case 'ipn':
                                $this->IPN();
                        break;
                        default:
                                $this->CallOrder();
                        break;
                }
				
        }

        function Success()
        {
 
                message(pretty_number(HTTP::_GP('amount', 0)).' Anti matter where is on your account. <br><br><a href="?page=overview">Continue</a>');
        }

        function Cancel()
        {
                message('Your Order where Cancelled.</h3><br><a href="?page=overview">Continue</a><br>');
        }

        /*
                It's important to remember that paypal calling this script.  There
                is no output here.  This is where you validate the IPN data and if it's
                valid, update your database to signify that the user has payed.  If
                you try and use an echo or printf function here it's not going to do you
                a bit of good.  This is on the "backend".  That is why, by default, the
                class logs all IPN data to a text file.
        */


        function CallOrder()
        {
                global $USER, $CONF, $UNI;

                $this->amount                = str_replace(".","",HTTP::_GP('ik_payment_amount',0));
                $this->amountbis                = HTTP::_GP('ik_payment_amount',0);
				
				if($this->amount < 10000){
				$this->printMessage('You have to buy minimum 10.000 AM!',  true, array('game.php?page=donate', 3)); 
				}
				
				
				if($this->amount >=  10000 && $this->amount <  20000)		$this->amount *= 1.05;
				elseif($this->amount >=  20000 && $this->amount <  50000) $this->amount *= 1.10;
				elseif($this->amount >=  50000 && $this->amount < 100000) $this->amount *= 1.20;
				elseif($this->amount >= 100000 && $this->amount < 150000) $this->amount *= 1.30;
				elseif($this->amount >= 150000 && $this->amount < 200000) $this->amount *= 1.40;
				elseif($this->amount >= 200000)                    $this->amount *= 1.50;
				
	
				
				$INFO1        = $GLOBALS['DATABASE']->uniquequery("SELECT * FROM `uni1_users` WHERE `id` = ".$GLOBALS['DATABASE']->sql_escape($USER['id']).";");
				
				if($INFO1['lp_points'] == 0)
				$text = 0;
				if($INFO1['lp_points'] > 0)
                $text = 0;
				if($INFO1['lp_points'] >= 125)
                $text = 5;
				if($INFO1['lp_points'] >= 625)
                $text = 10;
				if($INFO1['lp_points'] >= 2500)
                $text = 15;
				if($INFO1['lp_points'] >= 7000)
                $text = 20;
				
				$bonuses = 0;
				if($CONF['purchase_bonus_timer'] > TIMESTAMP){
				$bonuses = $CONF['purchase_bonus'];
				}
				$this->amount = round($this->amount + ($this->amount / 100 * $text));
				$this->amount = $this->amount + ($this->amount / 100 * $bonuses);
				$this->cost                = round($this->amountbis * 0.00013131);
               

                
				
				$this_p = new paypal_class;
				
               
                $this_p->add_field('business', $this::MAIL);
               
				
			  if($UNI==1){
			   $this_p->add_field('return', 'http://dark-space.org/game.php?page=overview');
			   $this_p->add_field('cancel_return', 'http://dark-space.org/game.php?page=overview');
                $this_p->add_field('notify_url', 'http://dark-space.org/ipns.php');
				}else{
				 $this_p->add_field('return', 'http://dark-space.org/game.php?page=overview');
				 $this_p->add_field('cancel_return', 'http://dark-space.org/game.php?page=overview');
                $this_p->add_field('notify_url', 'http://dark-space.org/ipns.php');
				}
				
                $this_p->add_field('item_name', $this->amount.' AM-User('.$USER['username'].').');
                $this_p->add_field('item_number', $this->amount);
                $this_p->add_field('amount', $this->cost);
                //$this_p->add_field('action', $action); ?
                $this_p->add_field('currency_code', 'EUR');
                 $this_p->add_field('custom', $USER['id']);
                 $this_p->add_field('rm', '2');
                //$this_p->dump_fields(); 
				 foreach ($this_p->fields as $name => $value) {
					$field[] = array('text'=>'<input type="hidden" name="'.$name.'" value="'.$value.'">');
				}
			 $this->tplObj->assign_vars(array(
				'fields' => $field,
			 ));
			$this->display('paypal_class.tpl');
        }

}
?>