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

## Known Issues
- Cancelling an MSP payment does not restore the cart properly (for a fix see [this commit](https://github.com/ho-nl/magento2-ReachDigital_GuestToShadowCustomer/compare/master...msp-restore-fix))
  - A similar fix is already applied for Mollie, see [this commit](https://github.com/ho-nl/magento2-ReachDigital_GuestToShadowCustomer/commit/99e987bea62e81c2bbc391e8a72b9fe89fe240f1)

## TODO

- Config setting for enabling or disabling the blocking of password reset function if customer is shadow
- Correctly set is_shadow if resetting password is enabled for shadow customers

### Implement more tests:

- Test that is_shadow flag is 0 when user registers
- Test that is_shadow flag value is maintained when customer is updated through webapi
- Test that is_shadow flag is 1 when shadow customer is automatically created
- Test that password reset is not allowed if not enabled and customer is shadow
- Test that password reset is allowed if enabled and customer is shadow
- Test correct updating of customer is_shadow flag in customer_grid_flat table (see
  `\Magento\Customer\Model\ResourceModel\Grid\CollectionTest::testGetItemByIdForUpdateOnSchedule` and
  `\Magento\Customer\Model\Indexer\AttributeProvider`
  )
