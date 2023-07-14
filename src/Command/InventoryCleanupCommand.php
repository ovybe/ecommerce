<?php

namespace App\Command;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:inventory:cleanup',
    description: 'Cleans up inventory logs and returns the reserved amount back to availability.',
)]
class InventoryCleanupCommand extends Command
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager=$entityManager;
    }

    protected function configure(): void
    {
//        $this
//            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
//            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
//        ;
    }

    protected function execute(InputInterface $input = null, OutputInterface $output = null): int
    {
        Stripe::setApiKey($_ENV["STRIPE_SECRET"]);
        $orders=$this->entityManager->getRepository(Order::class)->findByExistingSession();
        foreach($orders as $o){
            $session=Session::retrieve($o->getStripeSession());
            if($session->expires_at<time()){
                $o->returnInventory($this->entityManager);
                $o->setStripeSession();
                $o->setUpdatedAt(new \DateTimeImmutable());
            }
        }
        return Command::SUCCESS;
    }
}
