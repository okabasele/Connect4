<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\Player;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('board')
            ->add('status')
            ->add('result')
            ->add('player_one', EntityType::class, [  // j'indique que le champ category est une entity
                'class' => Player::class
                ])
            ->add('player_two', EntityType::class, [  // j'indique que le champ category est une entity
                'class' => Player::class
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'novalidate' => 'novalidate' // désactive la validation html
            ],
            'data_class' => Game::class,
            'csrf_protection' => false,
        ]);
    }
}
