<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
*/

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Form\Type;

use Brightweb\SyliusStanPlugin\Client\StanPayClient;
use Payum\Core\Bridge\Spl\ArrayObject;
use Stan\ApiException;
use Stan\Model\ApiSettingsRequestBody;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

final class StanPayGatewayConfigurationType extends AbstractType
{
    private string $baseUrl;

    private string $baseApiUrl;

    private TranslatorInterface $translator;

    public function __construct(string $baseUrl, string $baseApiUrl, TranslatorInterface $translator)
    {
        $this->baseUrl = $baseUrl;
        $this->baseApiUrl = $baseApiUrl;
        $this->translator = $translator;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'environment',
                ChoiceType::class,
                [
                    'label' => 'brightweb.stan_plugin.ui.form.gateway_configuration.environment',
                    'choices' => [
                        'brightweb.stan_plugin.ui.form.gateway_configuration.live' => StanPayClient::STAN_MODE_LIVE,
                        'brightweb.stan_plugin.ui.form.gateway_configuration.test' => StanPayClient::STAN_MODE_TEST,
                    ],
                ],
            )
            ->add(
                'live_api_client_id',
                TextType::class,
                [
                    'label' => 'brightweb.stan_plugin.ui.form.gateway_configuration.live_api_client_id',
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'brightweb.stan_plugin.ui.form.validator.api_client_id.not_blank',
                                'groups' => ['sylius'],
                            ],
                        ),
                    ],
                ],
            )
            ->add(
                'test_api_client_id',
                TextType::class,
                [
                    'label' => 'brightweb.stan_plugin.ui.form.gateway_configuration.test_api_client_id',
                ],
            )
            ->add(
                'live_api_secret',
                TextType::class,
                [
                    'label' => 'brightweb.stan_plugin.ui.form.gateway_configuration.live_api_client_secret',
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'brightweb.stan_plugin.ui.form.validator.api_client_secret.not_blank',
                                'groups' => ['sylius'],
                            ],
                        ),
                    ],
                ],
            )
            ->add(
                'test_api_secret',
                TextType::class,
                [
                    'label' => 'brightweb.stan_plugin.ui.form.gateway_configuration.test_api_client_secret',
                ],
            )
            ->add(
                'only_for_stanner',
                CheckboxType::class,
                [
                    'label' => 'brightweb.stan_plugin.ui.form.gateway_configuration.only_for_stanner',
                    'required' => false,
                    'help' => 'brightweb.stan_plugin.ui.form.gateway_configuration.only_for_stanner_tip',
                ],
            )
            ->add(
                'stan_connect',
                StanConnectType::class,
                [
                    'block_prefix' => 'brightweb_sylius_stan_stan_connect',
                    'label' => 'brightweb.stan_plugin.ui.form.edit_stan_connect_label',
                    'required' => false,
                ],
            )
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
                /** @var ArrayObject $gatewayOptions */
                $gatewayOptions = $event->getData();

                // TODO check if redirect URI already set

                $api = new StanPayClient([
                    'environment' => StanPayClient::STAN_MODE_LIVE,
                    'client_id' => $gatewayOptions['live_api_client_id'],
                    'client_secret' => $gatewayOptions['live_api_secret'],
                ], $this->baseApiUrl);

                $apiSettings = new ApiSettingsRequestBody();
                $apiSettings->setPaymentWebhookUrl("{$this->baseUrl}/payment/notify/unsafe/stan_pay");
                $apiSettings->setOauthRedirectUrl("{$this->baseUrl}/stan-connect");

                try {
                    $api->updateApiSettings($apiSettings);
                } catch (ApiException|\InvalidArgumentException $e) {
                    $translatedText = $this->translator->trans('brightweb.stan_plugin.ui.form.api_error');
                    $event->getForm()->addError(new FormError($translatedText));
                }
            })
        ;
    }
}
