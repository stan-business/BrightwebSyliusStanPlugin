<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
*/

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Form\Type;

use Stan\ApiException;
use Stan\Model\ApiSettingsRequestBody;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Validator\Constraints\NotBlank;

final class StanConnectType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'enable_stan_connect',
                CheckboxType::class,
                [
                    'label' => 'brightweb.stan_plugin.ui.form.enable_stan_connect'
                ],
            )
            ->add(
                'client_id',
                TextType::class,
                [
                    'label' => 'brightweb.stan_plugin.ui.form.client_id',
                    'required' => false,
                    'attr' => [
                        'autocomplete' => 'off'
                    ],
                ],
            )
            ->add(
                'client_secret',
                TextType::class,
                [
                    'label' => 'brightweb.stan_plugin.ui.form.client_secret',
                    'required' => false,
                    'attr' => [
                        'autocomplete' => 'off'
                    ],
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'brigthweb_stan_connect';
    }
}
