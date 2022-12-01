# Stan Installation

1. Run

    ```bash
    $ composer require stan-business/sylius-stan-plugin
    ```

2. Import Routes

    ```yaml
    # config/routes/sylius_shop.yaml

    brightweb_sylius_stan_connect_shop:
        resource: "@BrightwebSyliusStanPlugin/config/shop_routing.yml"
    ```
    
3. Import configuration

    ```yaml
   # config/packages/_sylius.yaml

   imports:
       # ...
       - { resource: "@BrightwebSyliusStanPlugin/config/config.yaml" }
   ```

4. Add dependencies

Add plugin dependencies to your config/bundles.php file:

    ```php
    // config/bundles.php
    return [
        ...
        Brightweb\SyliusStanPlugin::class => ['all' => true],
        ...
    ]
    ```

---

Next: [Onboarding](onboarding.md)
