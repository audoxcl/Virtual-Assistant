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

1. Copy files to your server so you can access them via a url like:  
https://yourdomain.com/virtual-assistant/index.php
2. Set the parameters in the file config.php
    1. **url_connectors:** the url where connectors are installed.
    2. **pipedrive_connector_token:** token for Pipedrive connector
    3. **hubspot_connector_token:** token for HubSpot connector
    4. **jira_connector_token:** token for Jira connector
    5. **transactional_email_service:** select the email transactional service to use: sendgrid or mailchimp
    6. **sendgrid_api_key:** sendgrid api key
    7. **mailchimp_api_key:** mailchimp api key
    8. **from_email:** email address from which emails are sent (i.e. info@example.com)
    9. **from_name:** email name from which emails are sent (i.e. Virtual Assistant)
    10. **bcc_address:** email address to which a blind carbon copy is sent
    11. **quote_of_the_day:** service to get phrases to motivate salespeople (i.e https://zenquotes.io/api/today)
    12. **services:** the array where you set all services for your instances
    13. **translations:** translation to other languages. Here you can add any language you need
3. Set an http request to the Virtual Assistant url using a cron job in your own server or using an additional service like FastCron (https://www.fastcron.com/). 
You can set the cron according when you want to receive the emails.

## Contact Us:

- info@audox.com
- www.audox.com
- www.audox.mx
- www.audox.es
- www.audox.cl