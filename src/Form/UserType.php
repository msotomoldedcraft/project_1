<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Group;
use App\Form\WishlistType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Full Name',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
            ])
            ->add('group', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose a group',
                'required' => true,
            ])
            ->add('wishlist', CollectionType::class, [
                'entry_type' => WishlistType::class, // Each wishlist item uses WishlistType
                'allow_add' => true,                 // Can add more items dynamically
                'allow_delete' => true,              // Can remove items
                'by_reference' => false,             // Needed for Doctrine collections
                'prototype' => true,                 // For dynamic JS forms (optional)
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
