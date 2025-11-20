<?php
// src/Command/CreateUserCommand.php (optional CLI helper)
use App\Entity\Employee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

$employee = new Employee();
$employee->setEmail('ahlem@gmail.com');
$employee->setFirstname('Ahlem');
$employee->setLastname('Ghanmi');
$employee->setRoles(['ROLE_ADMIN']);
$employee->setPassword(
    $passwordHasher->hashPassword($employee, '12345678')
);

$em->persist($employee);
$em->flush();
