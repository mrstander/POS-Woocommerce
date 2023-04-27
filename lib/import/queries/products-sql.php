SELECT
PRODUCT_ALIAS.PA_ALIASCODE AS SKU,
PRODUCTS.PM_DESCRIPTION as NAME,
PRODUCTS.PM_STDSELLINGPRICE as PRICE_1,
PRODUCTS.PM_STATUS_ID AS STATUS,
CAT_1.CT_NAME AS MAINCATEGORY,
CAT_2.CT_NAME as CATEGORY,
PRODUCT_BRANDS.PB_NAME as BRAND,
FLOOR(SUM(COALESCE(PRODUCT_LEDGER.PL_QTYONHAND,0))/2) as SOH
FROM PRODUCT_LEDGER
LEFT OUTER JOIN PRODUCTS ON PRODUCT_LEDGER.PL_PRODUCTCODE = PRODUCTS.PM_PRODUCTCODE AND PRODUCTS.PM_IS_WEB = 1
LEFT OUTER JOIN CATEGORY CAT_1 ON PRODUCTS.PM_CATEGORY_ID1 = CAT_1.CT_CODE AND CAT_1.CT_TYPE='0'
LEFT OUTER JOIN CATEGORY CAT_2 ON PRODUCTS.PM_CATEGORY_ID2 = CAT_2.CT_CODE AND CAT_2.CT_TYPE='1'
LEFT OUTER JOIN PRODUCT_BRANDS ON PRODUCT_BRANDS.PB_ID = PRODUCTS.PM_BRAND_ID
INNER JOIN PRODUCT_ALIAS ON PRODUCT_ALIAS.PA_PRODUCTCODE = PRODUCTS.PM_PRODUCTCODE
WHERE
PRODUCT_LEDGER.PL_STORE_ID IN (50)
AND PRODUCT_LEDGER.PL_LOCATION_ID=0
AND PRODUCTS.PM_STOCKTYPE = 0
AND PRODUCT_ALIAS.PA_ALIAS_ID = 14
GROUP BY
PRODUCT_ALIAS.PA_ALIASCODE,
PRODUCTS.PM_DESCRIPTION,
PRODUCTS.PM_STDSELLINGPRICE,
PRODUCTS.PM_STATUS_ID,
CAT_1.CT_NAME,
CAT_2.CT_NAME,
PRODUCT_BRANDS.PB_NAME;