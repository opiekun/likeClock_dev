The following changes have been made to the default magmi tool.

(1) categoryimport.php
Modified to specify a single semicolon, instead of double semicolong as a seperator between multiple categories
*The reason for this is to keep the data format consistent with uRapidflow

(2) magmi_auth.php
Changed default login credentials from username/password: magmi/magmi to cpamgento/cpmagento

(3) magmi_productimportengine.php
Modified the category import process that specifies product positions within a category.

By default magmi overwrites the product category positions within Magento. So if a user tries to control the
sort order of products directly in the Magento Admin the product import will overwrite the saved data.

The modification changes the default behavior turning OFF the ability to import the product category position.
This allows the product category position to be maintained directly in the Magento Admin.

(4) head.php, header.php, magmi.css, magmi_config_setup.php
Changes to these files are all design changes and simple text changes for branding