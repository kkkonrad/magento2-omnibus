# Kkkonrad Omnibus

Magento 2 module that records indexed catalog prices and displays the lowest
price from the configured period preceding a price reduction. The default
period is 30 days.

## Features

- interval-based price history per product, website, and customer group;
- automatic capture after Magento catalog price indexing;
- scheduled reconciliation for changes made outside standard save flows;
- separate, query-optimized current-price index;
- product and category/listing messages with tax and currency formatting;
- configurable-product variant updates in Luma and CSP-compatible Hyva themes;
- product-level visibility override and customer-group exclusions;
- history grid, product-form history, mass actions, and rebuild action in Admin;
- standard REST product extension attribute and GraphQL `omnibus_price` field;
- cleanup, reconciliation, rebuild, and diagnostics CLI commands.

## Installation

```bash
bin/magento module:enable Kkkonrad_Omnibus
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:clean
bin/magento indexer:reindex catalog_product_price kkkonrad_omnibus_price
```

Configuration is available under **Stores > Configuration > Catalog >
Omnibus Price**. The history retention setting must be at least as long as the
calculation period.

## Operations

```bash
bin/magento omnibus:diagnose
bin/magento omnibus:reconcile
bin/magento omnibus:history:cleanup
bin/magento omnibus:rebuild --force
```

`omnibus:rebuild --force` removes the module's history and recreates its
baseline from Magento's current catalog price index. Use it only when losing
the existing audit trail is intentional.

Cron runs reconciliation and retention cleanup automatically. Keep Magento
cron enabled in production.

## APIs

REST product responses expose `extension_attributes.omnibus_price` through
Magento's standard product repository endpoints.

GraphQL example:

```graphql
{
  products(filter: {sku: {eq: "24-MB01"}}) {
    items {
      sku
      omnibus_price {
        current_price
        reference_price
        lowest_price
        currency_code
        period_days
        has_active_discount
        message
      }
    }
  }
}
```

## Price model

The source of truth is `catalog_product_index_price`, so the module respects
Magento website scope, customer groups, catalog price rules, special prices,
and indexed product-type pricing. Stored values use the website base currency;
frontend output is converted and formatted in the active store currency.

On a detected reduction, the module freezes the lowest price from the period
immediately preceding that reduction. Subsequent price changes update the
active interval and retain the historical audit trail.

## Operational boundaries

- History starts when the module is enabled; it cannot reconstruct prices that
  Magento did not previously store.
- Coupon/cart price rules are not part of Magento's catalog price index and are
  therefore outside this module's calculation.
- For bundles and other dynamically configured products, the captured value is
  Magento's indexed product price, not every possible option combination.
- Direct database changes appear after catalog reindexing and reconciliation.
- Rebuild is synchronous and should be run during a maintenance window on very
  large catalogs.
