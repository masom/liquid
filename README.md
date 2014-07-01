[![Build Status](https://travis-ci.org/masom/liquid.svg?branch=master)](https://travis-ci.org/masom/liquid)

# Liquid template engine

This is the PHP port of [Shopify/liquid](https://github.com/Shopify/liquid)

* [Liquid documentation from Shopify](http://docs.shopify.com/themes/liquid-basics)
* [Liquid Wiki at GitHub](https://github.com/Shopify/liquid/wiki)
* [Website](http://liquidmarkup.org/)
 
## Introduction

Liquid is a template engine which was written with very specific requirements:

* It has to have beautiful and simple markup. Template engines which don't produce good looking markup are no fun to use.
* It needs to be non evaling and secure. Liquid templates are made so that users can edit them. You don't want to run code on your server which your users wrote.
* It has to be stateless. Compile and render steps have to be separate so that the expensive parsing and compiling can be done once and later on you can just render it passing in a hash with local variables and objects.

## Why you should use Liquid

* You want to allow your users to edit the appearance of your application but don't want them to run **insecure code on your server**.
* You want to render templates directly from the database.
* You like smarty (PHP) style template engines.
* You need a template engine which does HTML just as well as emails.
* You don't like the markup of your current templating engine.
