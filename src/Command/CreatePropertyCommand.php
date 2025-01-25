<?php

namespace App\Command;

use App\Entity\Property;
use App\Entity\User;
use App\Form\DataTransformer\SlugTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-property',
    description: 'Creates a new property in the system.'
)]
class CreatePropertyCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private SlugTransformer $slugTransformer;

    public function __construct(EntityManagerInterface $entityManager, SlugTransformer $slugTransformer)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->slugTransformer = $slugTransformer;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('title', InputArgument::REQUIRED, 'The title of the property')
            ->addArgument('price', InputArgument::REQUIRED, 'The price of the property')
            ->addArgument('owner-email', InputArgument::REQUIRED, 'The email of the property owner')
            ->addArgument('description', InputArgument::OPTIONAL, 'The description of the property', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $title = $input->getArgument('title');
        $price = $input->getArgument('price');
        $ownerEmail = $input->getArgument('owner-email');
        $description = $input->getArgument('description');

        if (!is_numeric($price) || $price <= 0) {
            $io->error('The price must be a positive number.');
            return Command::INVALID;
        }

        $owner = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $ownerEmail]);
        if (!$owner) {
            $io->error(sprintf('User with email "%s" does not exist.', $ownerEmail));
            return Command::FAILURE;
        }

        $slug = $this->slugTransformer->reverseTransform($title);

        $property = new Property();
        $property->setTitle($title)
            ->setDescription($description)
            ->setPrice($price)
            ->setSlug($slug)
            ->setOwner($owner);

        $this->entityManager->persist($property);
        $this->entityManager->flush();

        $io->success('Property successfully created!');
        $io->writeln(sprintf('Title: %s', $title));
        $io->writeln(sprintf('Slug: %s', $slug));
        $io->writeln(sprintf('Price: %s', $price));
        $io->writeln(sprintf('Owner: %s', $ownerEmail));

        return Command::SUCCESS;
    }
}
