# Virtual-Assistant

## Description

The purpose of this application is to facilitate the adoption of HubSpot, Pipedrive, and Jira.

This goal is achieved through a summary sent by email, which includes all opportunities or deals (or issues in Jira) with relevant information and hyperlinks that allow direct access to each opportunity/deal/issue from the email.

Each user receives their own records, while the manager receives all records.

HubSpot and Pipedrive are two well-known CRM software solutions, and Jira is one of the leading project management software tools.

This application usses our connectors to HubSpot, Pipedrive and Jira available here:

* HubSpot Connector  
https://github.com/audoxcl/Power-BI-HubSpot-Connector
* Pipedrive Connector  
https://github.com/audoxcl/Power-BI-Pipedrive-Connector
* Jira Connector  
https://github.com/audoxcl/Power-BI-Jira-Connector

## Instructions

1. Copy files to your server so you can access it via url like:  
https://yourdomain.com/Virtual-Assistant/index.php
2. Set the parameters in the file config.php
    1. **url_connectors:** the url where connectors are installed.
    2. **pipedrive_connector_token**
    3. **hubspot_connector_token**
    4. **transactional_email_service**
    5. **sendgrid_api_key**
    6. **mailchimp_api_key**
    7. **from_email**
    8. **from_name**
    9. **bcc_address**
    10. **quote_of_the_day**
    11. **services**
    12. **translations**
3. Set the cron in your own server or using an additional service like FastCron (https://www.fastcron.com/).

## Contact Us:

- info@audox.com
- www.audox.com
- www.audox.mx
- www.audox.es
- www.audox.cl