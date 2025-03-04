<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('query', SearchType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Suche...',
                    'class' => 'searchInput',
                ],
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Suchen',
                'attr' => [
                    'class' => 'searchButton',
                ],
            ])
            ->add('crafting', CheckboxType::class, [
                'label' => "Crafting",
                'required' => false,
            ])
            ->add('mysticForge', CheckboxType::class, [
                'label' => 'Mystic Forge',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
