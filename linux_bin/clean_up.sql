TRUNCATE core_cache;
TRUNCATE core_cache_tag;
TRUNCATE core_session;
TRUNCATE log_customer;
TRUNCATE log_visitor;
TRUNCATE log_visitor_info;
TRUNCATE log_url;
TRUNCATE log_url_info;
TRUNCATE log_quote;
TRUNCATE log_summary;
TRUNCATE log_summary_type;
TRUNCATE report_viewed_product_index;
TRUNCATE report_compared_product_index;
TRUNCATE report_event;
TRUNCATE dataflow_profile;


DELETE FROM sales_flat_quote WHERE updated_at < DATE_SUB(Now(),INTERVAL 7 DAY);
DELETE FROM sales_flat_quote_address WHERE updated_at < DATE_SUB(Now(),INTERVAL 7 DAY);
DELETE FROM sales_flat_quote_item WHERE updated_at < DATE_SUB(Now(),INTERVAL 7 DAY);

DROP TABLE IF EXISTS sales_t;
CREATE TABLE sales_t
  SELECT sales_flat_quote_item_option.option_id FROM sales_flat_quote_item_option
  LEFT JOIN sales_flat_order_item ON sales_flat_order_item.item_id = sales_flat_quote_item_option.item_id
  WHERE sales_flat_order_item.item_id IS NULL;
DELETE sales_flat_quote_item_option FROM sales_flat_quote_item_option INNER JOIN sales_t ON (sales_flat_quote_item_option.option_id = sales_t.option_id);
DROP TABLE sales_t;

CREATE TABLE core_url_rewrite_new LIKE core_url_rewrite;
INSERT core_url_rewrite_new SELECT * FROM core_url_rewrite;
DROP TABLE core_url_rewrite;
RENAME TABLE core_url_rewrite_new TO core_url_rewrite;

CREATE TABLE sales_flat_quote_new LIKE sales_flat_quote;
INSERT sales_flat_quote_new SELECT * FROM sales_flat_quote;
DROP TABLE sales_flat_quote;
RENAME TABLE sales_flat_quote_new TO sales_flat_quote;

CREATE TABLE sales_flat_quote_address_new LIKE sales_flat_quote_address;
INSERT sales_flat_quote_address_new SELECT * FROM sales_flat_quote_address;
DROP TABLE sales_flat_quote_address;
RENAME TABLE sales_flat_quote_address_new TO sales_flat_quote_address;

CREATE TABLE sales_flat_quote_item_option_new LIKE sales_flat_quote_item_option;
INSERT sales_flat_quote_item_option_new SELECT * FROM sales_flat_quote_item_option;
DROP TABLE sales_flat_quote_item_option;
RENAME TABLE sales_flat_quote_item_option_new TO sales_flat_quote_item_option;

CREATE TABLE sales_flat_quote_item_new LIKE sales_flat_quote_item;
INSERT sales_flat_quote_item_new SELECT * FROM sales_flat_quote_item;
DROP TABLE sales_flat_quote_item;
RENAME TABLE sales_flat_quote_item_new TO sales_flat_quote_item;
