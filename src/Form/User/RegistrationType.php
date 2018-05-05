<?php

namespace App\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class RegistrationType
 * @package App\Form\User
 */
class RegistrationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('displayName', 'Symfony\Component\Form\Extension\Core\Type\TextType', []);
    }

    /**
     * @return null|string
     */
    public function getParent(): ?string
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    /**
     * @return null|string
     */
    public function getBlockPrefix(): ?string
    {
        return 'app_user_registration';
    }

    /**
     * For Symfony 2.x
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->getBlockPrefix();
    }

}