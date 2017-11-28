<?php

namespace Lthrt\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('role', null, ['label_attr' => ['class' => 'text-right']])
             ->add('active', null, ['label_attr' => ['class' => 'text-right']])
             ->add('description', null, ['label_attr' => ['class' => 'text-right']])
                             ->add('submit', SubmitType::class, ['label' => 'Submit'])
                ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Lthrt\UserBundle\Entity\Role'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'lthrt_userbundle_role';
    }


}
