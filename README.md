# Visidea Magento (Adobe Commerce) Plugin – Installation Guide 

## 1. Purchase & Download

1. Go to the [Adobe Commerce Marketplace](https://marketplace.magento.com/).
2. Search for **Visidea** or **inferendo/module-visidea**.
3. Purchase (if required) and add the extension to your account.
4. In your Marketplace account, go to **My Purchases** and copy your access keys.

## 2. Install via Composer

1. SSH into your Magento server.
2. Add your Marketplace authentication keys to your Magento instance (if not already done):

    ```sh
    composer config --global http-basic.repo.magento.com <public_key> <private_key>
    ```

3. Require the Visidea module:

    ```sh
    composer require inferendo/module-visidea
    ```

4. Install the module:

    ```sh
    php bin/magento setup:upgrade
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy -f
    php bin/magento cache:clean
    php bin/magento cache:flush
    ```

## 3. Install via GitHub

Alternatively, you can install the plugin directly from the source code on GitHub:

1. SSH into your Magento server.
2. Navigate to the `app/code` directory of your Magento installation:

    ```sh
    cd <your-magento-root>/app/code
    ```

3. Clone the repository:

    ```sh
    git clone https://github.com/visidea/visidea-magento Inferendo/Visidea
    ```

    > If the `Inferendo` directory does not exist, create it first:
    > ```sh
    > mkdir -p Inferendo
    > ```

4. Install the module:

    ```sh
    php bin/magento setup:upgrade
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy -f
    php bin/magento cache:clean
    php bin/magento cache:flush
    ```

## 4. Enable & Configure

1. Log in to the Magento Admin Panel.
2. Go to **Stores > Configuration > Visidea > Settings**.
3. Enter your **Website**, **Public Token**, and **Private Token** (or use the integration link to generate them).
4. Adjust other settings as needed (e.g., Cron Hour Interval).
5. Save the configuration.

## 5. Verify Installation

- Visit your storefront and check for Visidea widgets or features.
- In the admin panel, you should see export links and integration instructions under **Stores > Configuration > Visidea**.

## 6. Troubleshooting

- If you do not see the plugin, clear cache and re-deploy static content.
- For support, contact [support@visidea.ai](mailto:support@visidea.ai) or visit the [documentation](https://docs.visidea.ai/docs/plugins/magento).

---

For more details, see the [official documentation](https://docs.visidea.ai/docs/plugins/magento).
