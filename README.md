# Visidea magento plugin

To test the plugin upload the code in the /app/code directory and from the command line execute these commands:

php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:clean
php bin/magento cache:flush


To publish the plugin on the Magento module store follow this procedure:

1. zip the "Visidea" folder content and give "inferendo_visidea-1.3.0.zip" name

    `
    cd Inferendo/Visidea
    zip -r ../../inferendo_visidea-1.3.0.zip .
    `

2. remove mac file from zip:

    `
    zip -d inferendo_visidea-1.3.0.zip "__MACOSX*"
    zip -d inferendo_visidea-1.3.0.zip "*.DS_Store"
    `

3. upload the zip file to Magento module backend: https://developer.magento.com/
