# magento2-ReachDigital_GuestToShadowCustomer

## Installation
`composer require reach-digital/magento2-guesttoshadowcustomer`

## Cron Usage
`n98-magerun2 sys:cron:run <number> reachdigital_guesttoshadowcustomer_convert`

## Introduction
- Create more insight into the total amount of orders per unique visitors
- In the backend there should be a possibility to create reorders for guests
- Possibility to save Guests payment information

## Functionalities
- Create Shadow Customer if new guest order is placed.
- Loop through all historical guest orders and create shadow customers.
- Loop through all historical guest orders and link Guest to existing "Customer/Shadow Customer".
- Added "Shadow Customer" column in Admin > Customer/Order Grid.
- Added "Is Shadow Customer" information on Admin > Customer/Order Edit page.

## TODO

### Implement more tests:

- Test that is_shadow flag is 0 when user registers
- Test that is_shadow flag value is maintained when customer is updated through webapi
- Test that is_shadow flag is 1 when shadow customer is automatically created
