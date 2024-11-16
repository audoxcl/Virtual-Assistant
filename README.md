# Virtual-Assistant

## Description

The purpose of this application is to facilitate the adoption of HubSpot, Pipedrive, and Jira.

This goal is achieved through an email summary that includes all opportunities or deals (or issues in Jira) with relevant information and hyperlinks, allowing direct access to each opportunity/deal/issue from the email.

Each user receives their own records, while the manager receives all records.

HubSpot and Pipedrive are two well-known CRM software solutions, and Jira is one of the leading project management software tools.

This application uses our connectors for HubSpot, Pipedrive and Jira available here:

* HubSpot Connector  
https://github.com/audoxcl/Power-BI-HubSpot-Connector
* Pipedrive Connector  
https://github.com/audoxcl/Power-BI-Pipedrive-Connector
* Jira Connector  
https://github.com/audoxcl/Power-BI-Jira-Connector

## Instructions

1. Copy the files to your server so you can access them via a URL such as:  
https://yourdomain.com/virtual-assistant/index.php
2. Set the parameters in the config.php file
    1. **url_connectors:** The url where connectors are installed.
    2. **pipedrive_connector_token:** Token for the Pipedrive connector.
    3. **hubspot_connector_token:** Token for the HubSpot connector.
    4. **jira_connector_token:** Token for the Jira connector.
    5. **transactional_email_service:** Select the transactional email service to use (e.g., SendGrid or Mailchimp).
    6. **sendgrid_api_key:** API key for SendGrid.
    7. **mailchimp_api_key:** API key for Mailchimp.
    8. **from_email:** The email address from which emails are sent (e.g., info@example.com).
    9. **from_name:** The name displayed as the sender (e.g., Virtual Assistant).
    10. **bcc_address:** Email address to receive a blind carbon copy (BCC).
    11. **quote_of_the_day:** Service for retrieving motivational quotes for salespeople (e.g., https://zenquotes.io/api/today).
    12. **services:** The array where you configure all services for your instances.
    13. **translations:** Translations for other languages. You can add any additional languages as needed.
3. Set an http request to the Virtual Assistant url using a cron job in your own server or using an additional service like FastCron (https://www.fastcron.com/). 
You can set the cron according when you want to receive the emails.

## Contact Us:

- info@audox.com
- www.audox.com
- www.audox.mx
- www.audox.es
- www.audox.cl