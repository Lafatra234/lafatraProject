<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Type;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('eventName', TextType::class)
            ->add('description')
            ->add('place', TextType::class)
            ->add('beginDate')
            ->add('endDate')
            //->add('statutEvent', TextType::class)
            //->add('organizer', EntityType::class, [
                //'class' => User::class,
                //'choice_label' => 'email',
            //])
            ->add('type_event', EntityType::class, [
                'class' => Type::class,
                'choice_label' => 'type_event',
            ])
            ->add('image', FileType::class, [
                'multiple' => false,
                'mapped' => false,
                'required' => true, 
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
