<?php

namespace Lthrt\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(
        FormBuilderInterface $builder,
        array                $options
    ) {
        $builder->add('username', null, ['label_attr' => ['class' => 'text-right'], 'required' => false])
            ->add('email', null, ['label_attr' => ['class' => 'text-right']])
            ->add('active', null, ['label_attr' => ['class' => 'text-right'], 'required' => false])
            ->add('role', null, ['label_attr' => ['class' => 'text-right']])
            ->add('group', null, ['label_attr' => ['class' => 'text-right']])
            ->add('submit', SubmitType::class, ['label' => 'Submit'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Lthrt\UserBundle\Entity\User',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'lthrt_userbundle_user';
    }
}
