<?php

namespace App\Form;

use App\Entity\Consultation;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ConsultationType extends AbstractType
{
    private AuthorizationCheckerInterface $authorizationChecker;
    private UserRepository $userRepository;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, UserRepository $userRepository)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'CABINET' => 'CABINET',
                    'EN_LIGNE' => 'EN_LIGNE',
                ],
                'label' => 'Type',
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'label' => 'Notes',
            ]);

        if ($this->authorizationChecker->isGranted('ROLE_PATIENT')) {
            $builder
                ->add('dateDebut', DateTimeType::class, [
                    'widget' => 'single_text',
                    'label' => 'Date de début',
                ])
                ->add('medecin', EntityType::class, [
                    'class' => User::class,
                    'choice_label' => 'username',
                    'label' => 'Médecin',
                    'query_builder' => function (UserRepository $repo) {
                        return $repo->createQueryBuilder('u')
                            ->leftJoin('u.roles', 'r')
                            ->leftJoin('u.doctor', 'd')
                            ->where('r.id = :roleId')
                            ->andWhere('u.isActive = 1')
                            ->setParameter('roleId', 5)
                            ->orderBy('u.username', 'ASC');
                    },
                ]);
        }

        if ($this->authorizationChecker->isGranted('ROLE_PHYSICIAN')) {
            $builder
                ->add('diagnostic', TextareaType::class, [
                    'required' => false,
                    'label' => 'Diagnostic',
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Consultation::class,
        ]);
    }
}
