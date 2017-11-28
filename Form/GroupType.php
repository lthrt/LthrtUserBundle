<?php

namespace Lthrt\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('active', null, ['label_attr' => ['class' => 'text-right']])
             ->add('description', null, ['label_attr' => ['class' => 'text-right']])
             ->add('name', null, ['label_attr' => ['class' => 'text-right']])
             ->add('subGroup', null, ['label_attr' => ['class' => 'text-right']])
             ->add('superGroup', null, ['label_attr' => ['class' => 'text-right']])
                             ->add('submit', SubmitType::class, ['label' => 'Submit'])
                ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Lthrt\UserBundle\Entity\Group'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'lthrt_userbundle_group';
    }


}
