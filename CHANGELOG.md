0.7.3
-----
* code reformat
* Google Translate API URL was changed (now is "https://translation.googleapis.com/language/translate/v2")

0.7.2
-----
* mbstring extension is required (check added) if you use Google Translate API.
* if you translate an attribute that has a text longer than 5000 chars with
  Google Translate API, the script will output a warning and skip that specific
  attribute for that specific product.

0.7.1
-----
* solved issue #12

0.7.0
-----
* categories translation was added

0.6.1
-----
* fixed modman file

0.6.0
-----
* support for debug and dry run was added to the fballiano_full_catalog_translate.php shell script

0.5.0
-----
* support for custom translation command was introduced

0.4.0
-----
* a bug with Magmi and table prefixes was solved

0.3.0
-----
* "Attribute to translate" config il now a multiselect

0.2.0
-----
* Table names read dinamically

0.1.0
-----
* Initial release
