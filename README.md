Magento Full Catalog Translate
==============================

Magento module to translate all your products automatically using Google Translate API.

<table><tr><td align=center>
<strong>If you find my work valuable, please consider sponsoring</strong><br />
<a href="https://github.com/sponsors/fballiano" target=_blank title="Sponsor me on GitHub"><img src="https://img.shields.io/badge/sponsor-30363D?style=for-the-badge&logo=GitHub-Sponsors&logoColor=#white" alt="Sponsor me on GitHub" /></a>
<a href="https://www.buymeacoffee.com/fballiano" target=_blank title="Buy me a coffee"><img src="https://img.shields.io/badge/Buy_Me_A_Coffee-FFDD00?style=for-the-badge&logo=buy-me-a-coffee&logoColor=black" alt="Buy me a coffee" /></a>
<a href="https://www.paypal.com/paypalme/fabrizioballiano" target=_blank title="Donate via PayPal"><img src="https://img.shields.io/badge/PayPal-00457C?style=for-the-badge&logo=paypal&logoColor=white" alt="Donate via PayPal" /></a>
</td></tr></table>

How does it work
----------------
When you install the module it will create a new product attribute called "Translate automatically?" (you'll find it in the "General" tab for all of your products).

Let's say you have your main store in English, then you create a storeview in Italian, now all of your products in the Italian store view have the English texts. What you want is to quickly have them in Italian. Be sure the locales are correctly configured for all the stores/storeviews you're going to use in the process (this module will automatically read the language settings from the storeviews).

So go to your "Catalog -> Manage products" mask in the Magento backend, select the Italian store view (or anyway the one you want to translate), then with the mass action tools selects all the products and "update attributes", now set the "Translate automatically?" attribute to yes. This means that these products for this store view need to be translated (we'll see later how and from what language to what language).

Now we have products in English and products marked as "to be translated" in our Italian storeview.

Let's step back a little bit: configuration.
Before actually translating our contents you need to review some configurations of the module, go to the Magento backend mask "System -> Configuration -> Services -> Fballiano Full Catalog Translate".
Here can decide which product attributes have to be translated (defaults: name, short_description, description, meta_title, meta_keyword, meta_description) but most importantly you'll have to fill your Google Translate API key (without it nothing will actually work).

Open the console and navigate to the "shell" directory.
Now we'll start the actual translate process.

```shell
php fballiano_full_catalog_translate.php sourcestorecode targetstorecode
```

in out case it could be:
```shell
php fballiano_full_catalog_translate.php default storeviewita
```

The process will gather all the products that need to be translated (from the target storeview), gather the untranslated text (from the source storeview), call Google Translate API for every attribute, import the translated text into the target store view, set the record to "not to be translated" (again, into the target storeview).

Google Translate characters limit!
----------------------------------
Google Translate APIs only supports a maximum of 5,000 characters in a single request. If you setup this module to use Google Translate, be sure that your products' descriptions are less than 5,000 characters long!

Backup!!!
---------
Backup your database before launching the translation process!!!
This module is provided "as is" and I'll not be responsible for any data damage.

Installation
------------

Simply download the whole repository and copy everything to your Magento document root.
Otherwise with modman:
```shell
modman clone https://github.com/fballiano/magento-full-catalog-translate
```

Compatibility
-------------
This module was developed on Magento 1.9.
If you have a different version of Magento and the module is working please drop me a line so I can update this compatibility list.

Some technical detail
---------------------
To speed up a bit the import process, a patched version of [Magmi](http://sourceforge.net/projects/magmi/) it's included in the extension (lib/Magmi).

TODO
----

Remove magmi and migrate to https://github.com/avstudnitz/AvS_FastSimpleImport

Support
-------
If you have any issues with this extension, open an issue on GitHub.

Contribution
------------
Any contributions are highly appreciated. The best way to contribute code is to open a
[pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Fabrizio Balliano  
[http://fabrizioballiano.com](http://fabrizioballiano.com)  
[@fballiano](https://twitter.com/fballiano)

Licence
-------
[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)

Copyright
---------
(c) Fabrizio Balliano
