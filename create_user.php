<?php

require __DIR__ . '/vendor/autoload.php';

use App\Entity\User;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

// Boot Symfony kernel
$kernel = new Kernel($_ENV['APP_ENV'], true);
$kernel->boot();

// Get Entity Manager
/** @var EntityManagerInterface $em */
$em = $kernel->getContainer()->get('doctrine')->getManager();

/*
|--------------------------------------------------------------------------
| Create Symfony Password Hasher (NO container dependency)
|--------------------------------------------------------------------------
*/

$factory = new PasswordHasherFactory([
    User::class => ['algorithm' => 'auto']
]);

$passwordHasher = $factory->getPasswordHasher(User::class);

/* =========================
   CREATE ADMIN USER
   ========================= */
$admin = new User();
$admin->setName('Admin User');
$admin->setEmail('admin@example.com');
$admin->setPassword(
    $passwordHasher->hash('admin123')
);
$admin->setRoles(['ROLE_ADMIN']);
$admin->setVerified(true);
$admin->setConfirmed(true);
$admin->setToken(bin2hex(random_bytes(16)));

$em->persist($admin);

/* =========================
   CREATE NORMAL USER
   ========================= */
$user = new User();
$user->setName('Regular User');
$user->setEmail('user@example.com');
$user->setPassword(
    $passwordHasher->hash('user123')
);
$user->setRoles(['ROLE_USER']);
$user->setVerified(true);
$user->setConfirmed(true);
$user->setToken(bin2hex(random_bytes(16)));

$em->persist($user);

// Save to database
$em->flush();

echo "Admin and Regular user created successfully!\n";
