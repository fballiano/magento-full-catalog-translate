Magento Full Catalog Translate
==============================

Magento module to translate all your products automatically using Google Translate API.

How does it work
----------------
When you install the module it will create a new product attribute called "Translate automatically?" (you'll find it in the "General" tab for all of your products).

Let's say you have your main store in English, then you create a storeview in Italian, now all of your products in the Italian store view have the English texts. What you want is to quickly have them in Italian. Be sure the locales are correctly configured for all the stores/storeviews you're going to use in the process (this module will automatically read the language settings from the storeviews).

So go in your "Catalog -> Manage products" mask in the Magento backend, select the Italian store view (or anyway the one you want to translate), then with the mass action tools selects all the products and "update attributes", now set the "Translate automatically?" attribute to yes. This means that these products for this store view neeed to be translated (we'll see later how and from what language to what language).

Now we have products in English and products marked as "to be translated" in our Italian storeview.

Let's step back a little bit: configuration.
Before actually translating our contents you need to review come configurations of the module, go to the Magento backend mask "System -> Configuration -> Services -> Fballiano Full Catalog Translate".
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

Some technical detail
---------------------
To speed up a bit the import process, a patched version of [Magmi](http://sourceforge.net/projects/magmi/) it's included in the extension (lib/Magmi).

Support
-------
If you have any issues with this extension, open an issue on GitHub (see URL above).

Contribution
------------
Any contributions are highly appreciated. The best way to contribute code is to open a
[pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Fabrizio Balliano
[http://fabrizioballiano.it](http://fabrizioballiano.it)  
[@fballiano](https://twitter.com/fballiano)

Licence
-------
[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)

Copyright
---------
(c) 2014 Fabrizio Balliano
