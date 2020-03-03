<?php

namespace App\Form;

use App\Entity\Stagiaire;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class StagiaireFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom*'
            ])
            ->add('prenom', TextType::class ,[
                  'label'=> 'Prénom*',
            ]
            )
            ->add('dateNaissance',DateType::class, [
                'widget' => "single_text",
                'label' => 'Date de naissance*',
                'years' => range(date('Y'), date('Y')-102),
                
            ])

            ->add('codepostal', TextType::class, [
                'label' => 'Code postal*',
                'attr' => ['placeholder' => 'Code postal']
            ])
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                [$this, 'onPreSubmit']
            )
            ->add('ville', ChoiceType::class, [
                'placeholder' => 'Choisissez une ville'
            ])

            
            ->add('mail', EmailType::class, [
                'label' => 'Email*',
            ])
            ->add('telephone')

            ->add('sexe',ChoiceType::class, array('choices' => array(
                'Homme' => 'Homme',
                'Femme' => 'Femme',
                'Autre' => 'Autre',
               ),
            ))


                
            ->add('Valider', SubmitType::class,[
                'attr' => ['class' => 'submit'],

            ])

      
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Stagiaire::class,
        ]);
    }
    public function onPreSubmit(FormEvent $event)
    {
        $input = $event->getData()['ville'];
        $event->getForm()->add('ville', ChoiceType::class,
            ['choices' => [$input]]
    );
    }
}
