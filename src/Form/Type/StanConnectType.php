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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Validator\Constraints\NotBlank;

final class StanConnectType extends AbstractType
{
    /** @var string */
    private $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'clientId',
                TextType::class,
                [
                    'label' => 'brightweb.stan_connect.ui.form.client_id',
                    'attr' => [
                        'autocomplete' => 'off'
                    ],
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'brightweb.stan_connect.validator.client_id.not_blank',
                                'groups' => ['sylius'],
                            ],
                        ),
                    ]
                ],
            )
            ->add(
                'clientSecret',
                TextType::class,
                [
                    'label' => 'brightweb.stan_connect.ui.form.client_secret',
                    'attr' => [
                        'autocomplete' => 'off'
                    ],
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'brightweb.stan_connect.validator.client_secret.not_blank',
                                'groups' => ['sylius']
                            ]
                        )
                    ]
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
